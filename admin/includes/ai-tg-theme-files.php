<?php
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ai_tg_should_skip_url') ) {
    function ai_tg_should_skip_url($url) {
        return (bool) preg_match('~^(?:https?:)?//|data:|mailto:|tel:|#~i', trim((string)$url));
    }
}

if ( ! function_exists('ai_tg_normalize_rel_path') ) {
    function ai_tg_normalize_rel_path($path) {
        $path = trim((string)$path);

        $q = '';
        if (strpos($path, '?') !== false || strpos($path, '#') !== false) {
            $parts = preg_split('~(?=[?#])~', $path, 2);
            $path = $parts[0] ?? $path;
            $q = $parts[1] ?? '';
        }

        $path = preg_replace('~^\./+~', '', $path);
        $path = ltrim($path, '/');

        return $path . $q;
    }
}

if ( ! function_exists('ai_tg_extract_body_inner') ) {
    function ai_tg_extract_body_inner($html) {
        $html = (string)$html;
        if (preg_match('#<body[^>]*>(.*)</body>#is', $html, $m)) {
            return trim($m[1]);
        }
        return trim($html);
    }
}

if ( ! function_exists('ai_tg_fix_home_links') ) {
    function ai_tg_fix_home_links($html) {
        return preg_replace_callback(
            '/<a\b([^>]*?)\bhref=(["\'])([^"\']*)\2([^>]*)>/i',
            function ($m) {
                $before = $m[1];
                $href   = trim((string)$m[3]);
                $after  = $m[4];

                $h = strtolower($href);
                $h = preg_replace('~\?.*$~', '', $h);
                $h = preg_replace('~#.*$~', '', $h);
                $h = trim($h);

                $home_candidates = [
                    '', '/', './',
                    'index.html','index.php',
                    './index.html','./index.php',
                    '/index.html','/index.php',
                ];

                if (in_array($h, $home_candidates, true)) {
                    return '<a' . $before . 'href="<?php echo esc_url( home_url("/") ); ?>"' . $after . '>';
                }

                return $m[0];
            },
            (string)$html
        );
    }
}

if ( ! function_exists('ai_tg_fix_img_src') ) {
    function ai_tg_fix_img_src($html) {
        return preg_replace_callback(
            '/<img\b([^>]*?)\bsrc=(["\'])([^"\']+)\2([^>]*)>/i',
            function ($m) {
                $before = $m[1];
                $src    = $m[3];
                $after  = $m[4];

                if (ai_tg_should_skip_url($src)) return $m[0];

                $src = ai_tg_normalize_rel_path($src);
                return '<img' . $before . 'src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/' . esc_attr($src) . '"' . $after . '>';
            },
            (string)$html
        );
    }
}

if ( ! function_exists('ai_tg_fix_inline_style_urls') ) {
    function ai_tg_fix_inline_style_urls($html) {
        return preg_replace_callback(
            '/\bstyle=(["\'])(.*?)\1/i',
            function ($m) {
                $outer_quote = $m[1];
                $style = $m[2];

                $style = preg_replace_callback(
                    '~url\(\s*(["\']?)([^"\')]+)\1\s*\)~i',
                    function ($u) {
                        $url = $u[2];
                        if (ai_tg_should_skip_url($url)) return $u[0];

                        $url = ai_tg_normalize_rel_path($url);
                        return "url('<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/$url')";
                    },
                    $style
                );

                return 'style=' . $outer_quote . $style . $outer_quote;
            },
            (string)$html
        );
    }
}

if ( ! function_exists('ai_tg_strip_css_js_tags') ) {
    function ai_tg_strip_css_js_tags($html) {
        $html = (string)$html;
        $html = preg_replace('#<link\b[^>]*rel=["\']stylesheet["\'][^>]*>\s*#is', '', $html);
        $html = preg_replace('#<script\b[^>]*\bsrc=["\'][^"\']+["\'][^>]*>\s*</script>\s*#is', '', $html);
        return $html;
    }
}

if ( ! function_exists('ai_tg_correct_html_to_php') ) {
    function ai_tg_correct_html_to_php($html) {
        $html = (string)$html;
        $html = ai_tg_fix_home_links($html);
        $html = ai_tg_fix_img_src($html);
        $html = ai_tg_fix_inline_style_urls($html);
        $html = ai_tg_strip_css_js_tags($html);
        return $html;
    }
}

if ( ! function_exists('ai_tg_collect_assets_from_html') ) {
    function ai_tg_collect_assets_from_html($html) {
        $html = (string)$html;
        $css = [];
        $js  = [];

        // CSS
        if (preg_match_all('#<link\b[^>]*rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\']#is', $html, $m)) {
            foreach ($m[1] as $href) {
                $href = trim($href);
                if (!ai_tg_is_enqueueable_url($href)) continue;

                // normalize only if it's NOT external
                if (!preg_match('~^(?:https?:)?//~i', $href)) {
                    $href = ai_tg_normalize_rel_path($href);
                }

                $css[] = $href;
            }
        }

        // JS
        if (preg_match_all('#<script\b[^>]*\bsrc=["\']([^"\']+)["\']#is', $html, $m2)) {
            foreach ($m2[1] as $src) {
                $src = trim($src);
                if (!ai_tg_is_enqueueable_url($src)) continue;

                // normalize only if it's NOT external
                if (!preg_match('~^(?:https?:)?//~i', $src)) {
                    $src = ai_tg_normalize_rel_path($src);
                }

                $js[] = $src;
            }
        }

        return [
            'css' => array_values(array_unique($css)),
            'js'  => array_values(array_unique($js)),
        ];
    }

}

/**
 * Extract ZIP into theme root (copy folders/files)
 */
if ( ! function_exists('ai_tg_extract_zip_to_theme_root') ) {
    function ai_tg_extract_zip_to_theme_root($zip_path, $theme_dir, &$err = '') {
        $err = '';

        if (!file_exists($zip_path)) { $err = 'ZIP not found.'; return false; }
        if (!file_exists($theme_dir)) { $err = 'Theme dir not found.'; return false; }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();

        $upload = wp_upload_dir();
        $tmp = trailingslashit($upload['basedir']) . 'ai_theme_generator_extract/' . time() . '/';
        wp_mkdir_p($tmp);

        $unzipped = unzip_file($zip_path, $tmp);
        if (is_wp_error($unzipped)) {
            $err = 'Unzip failed: ' . $unzipped->get_error_message();
            return false;
        }

        // wrapper folder detection
        $items = array_values(array_diff(scandir($tmp), ['.','..']));
        $root_to_copy = $tmp;
        if (count($items) === 1) {
            $one = $tmp . $items[0];
            if (is_dir($one)) $root_to_copy = trailingslashit($one);
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root_to_copy, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $f) {
            $src = $f->getPathname();
            $rel = ltrim(str_replace($root_to_copy, '', $src), '/\\');
            $dst = trailingslashit($theme_dir) . $rel;

            if ($f->isDir()) {
                if (!file_exists($dst)) wp_mkdir_p($dst);
            } else {
                if (!file_exists(dirname($dst))) wp_mkdir_p(dirname($dst));
                @copy($src, $dst);
            }
        }

        return true;
    }
}

/**
 * Ensure header/footer exist + corrected + wp_head/wp_footer
 * IMPORTANT: Your header/footer can contain navbar/footer HTML (repeatable parts) — this keeps them.
 */
if ( ! function_exists('ai_tg_ensure_wp_header_footer') ) {
    function ai_tg_ensure_wp_header_footer($theme_dir, &$err = '') {
        $err = '';

        $header_path = trailingslashit($theme_dir) . 'header.php';
        $footer_path = trailingslashit($theme_dir) . 'footer.php';

        // HEADER
        if (file_exists($header_path)) {
            $h = file_get_contents($header_path);
            if ($h === false) { $err = 'Could not read header.php'; return false; }

            $h = ai_tg_correct_html_to_php($h);

            // ensure wp_head
            if (stripos($h, 'wp_head') === false) {
                if (stripos($h, '</head>') !== false) {
                    $h = preg_replace('#</head>#i', "<?php wp_head(); ?>\n</head>", $h, 1);
                } else {
                    // no </head>, just add wp_head at end (safe)
                    $h .= "\n<?php wp_head(); ?>\n";
                }
            }

            // ensure body_class on <body>
            if (preg_match('#<body\b[^>]*>#i', $h) && stripos($h, 'body_class') === false) {
                $h = preg_replace('#<body\b([^>]*)>#i', '<body$1 <?php body_class(); ?> >', $h, 1);
            }

            if (file_put_contents($header_path, $h) === false) {
                $err = 'Could not write header.php';
                return false;
            }
        } else {
            file_put_contents(
                $header_path,
                "<?php if ( ! defined('ABSPATH') ) exit; ?>\n<!doctype html>\n<html <?php language_attributes(); ?>>\n<head>\n<meta charset=\"<?php bloginfo('charset'); ?>\" />\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />\n<?php wp_head(); ?>\n</head>\n<body <?php body_class(); ?>>\n"
            );
        }

        // FOOTER
        if (file_exists($footer_path)) {
            $f = file_get_contents($footer_path);
            if ($f === false) { $err = 'Could not read footer.php'; return false; }

            $f = ai_tg_correct_html_to_php($f);

            if (stripos($f, 'wp_footer') === false) {
                $f = rtrim($f) . "\n<?php wp_footer(); ?>\n";
            }

            if (stripos($f, '</body>') === false) $f .= "\n</body>\n";
            if (stripos($f, '</html>') === false) $f .= "</html>\n";

            if (file_put_contents($footer_path, $f) === false) {
                $err = 'Could not write footer.php';
                return false;
            }
        } else {
            file_put_contents($footer_path, "<?php wp_footer(); ?>\n</body></html>");
        }

        return true;
    }
}

/**
 * Enqueue block in functions.php (REPLACEABLE, not messy append)
 */
if ( ! function_exists('ai_tg_write_enqueues_into_functions_php') ) {
    function ai_tg_write_enqueues_into_functions_php($theme_dir, $css_list, $js_list, &$err = '') {
        $err = '';

        $fn = trailingslashit($theme_dir) . 'functions.php';
        if (!file_exists($fn)) {
            if (file_put_contents($fn, "<?php\n") === false) {
                $err = 'Could not create functions.php';
                return false;
            }
        }

        $existing = file_get_contents($fn);
        if ($existing === false) { $err = 'Could not read functions.php'; return false; }

        $css_list = array_values(array_unique(array_filter((array)$css_list)));
        $js_list  = array_values(array_unique(array_filter((array)$js_list)));

        $block_start = "/* AI_TG_ENQUEUES_START */";
        $block_end   = "/* AI_TG_ENQUEUES_END */";

        $lines = [];
        $lines[] = $block_start;
        $lines[] = "add_action('wp_enqueue_scripts', function () {";

        $i = 1;
        foreach ($css_list as $href) {
            $href = trim((string)$href);
            if ($href === '') continue;

            $handle = "ai-tg-style-{$i}";
            if (preg_match('~^(?:https?:)?//~i', $href)) {
                $lines[] = "    wp_enqueue_style('{$handle}', '" . esc_url_raw($href) . "', [], null);";
            } else {
                $href = ltrim($href, '/');
                $lines[] = "    wp_enqueue_style('{$handle}', get_stylesheet_directory_uri() . '/" . esc_js($href) . "', [], null);";
            }
            $i++;
        }

        $j = 1;
        foreach ($js_list as $src) {
            $src = trim((string)$src);
            if ($src === '') continue;

            $handle = "ai-tg-script-{$j}";
            if (preg_match('~^(?:https?:)?//~i', $src)) {
                $lines[] = "    wp_enqueue_script('{$handle}', '" . esc_url_raw($src) . "', ['jquery'], null, true);";
            } else {
                $src = ltrim($src, '/');
                $lines[] = "    wp_enqueue_script('{$handle}', get_stylesheet_directory_uri() . '/" . esc_js($src) . "', ['jquery'], null, true);";
            }
            $j++;
        }


        $lines[] = "});";
        $lines[] = $block_end;

        $new_block = implode("\n", $lines) . "\n";

        if (strpos($existing, $block_start) !== false && strpos($existing, $block_end) !== false) {
            $pattern = '#/\* AI_TG_ENQUEUES_START \*/.*?/\* AI_TG_ENQUEUES_END \*/\s*#s';
            $updated = preg_replace($pattern, $new_block, $existing);
        } else {
            $updated = rtrim($existing) . "\n\n" . $new_block;
        }

        if (file_put_contents($fn, $updated) === false) {
            $err = 'Could not write functions.php';
            return false;
        }

        return true;
    }
}

/**
 * Write template file in theme root: page-{slug}.php
 */
if ( ! function_exists('ai_tg_write_page_template_in_theme') ) {
    function ai_tg_write_page_template_in_theme($theme_dir, $slug, $source_filename, $raw_content, $ext) {
        $slug = sanitize_title($slug);
        if (!$slug) return new WP_Error('bad_slug', 'Invalid slug.');

        $template_file = 'page-' . $slug . '.php';
        $template_path = trailingslashit($theme_dir) . $template_file;

        if ($ext === 'html') {
            $raw_content = ai_tg_extract_body_inner($raw_content);
        }

        $raw_content = ai_tg_correct_html_to_php($raw_content);

        $php  = "<?php\n";
        $php .= "/**\n";
        $php .= " * Template Name: AI - {$slug}\n";
        $php .= " * Source: {$source_filename}\n";
        $php .= " */\n\n";
        $php .= "if ( ! defined('ABSPATH') ) exit;\n";
        $php .= "get_header();\n\n";
        $php .= "?>\n";
        $php .= $raw_content . "\n";
        $php .= "<?php\n\n";
        $php .= "get_footer();\n";

        if (file_put_contents($template_path, $php) === false) {
            return new WP_Error('write_fail', 'Failed to write template file: ' . $template_file);
        }

        return $template_file;
    }
}

/**
 * Create WP pages + templates
 */
if ( ! function_exists('ai_tg_create_wp_pages_with_templates') ) {
    function ai_tg_create_wp_pages_with_templates($rows, $page_status, $zip_saved_path, $theme_dir, $target_mode, $new_theme_slug_for_activation = '', $activate_theme = false) {
        $created = [];
        $errors = [];

        if (!current_user_can('manage_options')) {
            return [[], ['Permission denied.']];
        }

        $page_status = ($page_status === 'publish') ? 'publish' : 'draft';

        if ($target_mode === 'new' && $new_theme_slug_for_activation && $activate_theme) {
            switch_theme($new_theme_slug_for_activation);
        }

        foreach ($rows as $row) {
            $create = !empty($row['create']) && (string)$row['create'] === '1';
            if (!$create) continue;

            $path  = isset($row['path']) ? sanitize_text_field($row['path']) : '';
            $title = isset($row['title']) ? sanitize_text_field($row['title']) : '';
            $slug  = isset($row['slug']) ? sanitize_title($row['slug']) : '';

            if (!$title) $title = 'Untitled';
            if (!$slug)  $slug  = sanitize_title($title);

            $existing = get_page_by_path($slug, OBJECT, 'page');
            if ($existing instanceof WP_Post) {
                $errors[] = "Skipped '{$title}' (slug '{$slug}') — already exists (ID {$existing->ID}).";
                continue;
            }

            $source_basename = basename($path);
            $ext = strtolower(pathinfo($source_basename, PATHINFO_EXTENSION));

            $zip_err = '';
            $content = ai_tg_get_zip_file_content($zip_saved_path, $source_basename, $zip_err);

            if ($zip_err || $content === '') {
                $errors[] = "Failed reading '{$source_basename}' from ZIP for '{$title}': " . ($zip_err ?: 'Empty content');
                continue;
            }

            $template_file = ai_tg_write_page_template_in_theme($theme_dir, $slug, $source_basename, $content, $ext);
            if (is_wp_error($template_file)) {
                $errors[] = "Failed writing template for '{$title}' ({$slug}): " . $template_file->get_error_message();
                continue;
            }

            $post_id = wp_insert_post([
                'post_type'   => 'page',
                'post_title'  => $title,
                'post_name'   => $slug,
                'post_status' => $page_status,
            ], true);

            if (is_wp_error($post_id)) {
                $errors[] = "Failed creating page '{$title}' ({$slug}): " . $post_id->get_error_message();
                continue;
            }

            update_post_meta($post_id, '_wp_page_template', $template_file);
            update_post_meta($post_id, '_ai_tg_source_file', $source_basename);

            $created[] = [
                'id'       => (int)$post_id,
                'title'    => $title,
                'slug'     => $slug,
                'template' => $template_file,
            ];
        }

        return [$created, $errors];
    }
}


function ai_tg_build_header_footer_from_zip($zip_path, $theme_dir, &$all_css = [], &$all_js = [], &$err = '') {
    $err = '';

    // 1) Read header/footer from ZIP by basename
    $zip_err = '';
    $zip_header = ai_tg_get_zip_file_content($zip_path, 'header.php', $zip_err);
    if ($zip_err || $zip_header === '') {
        // try html fallback
        $zip_err = '';
        $zip_header = ai_tg_get_zip_file_content($zip_path, 'header.html', $zip_err);
    }

    $zip_err2 = '';
    $zip_footer = ai_tg_get_zip_file_content($zip_path, 'footer.php', $zip_err2);
    if ($zip_err2 || $zip_footer === '') {
        $zip_err2 = '';
        $zip_footer = ai_tg_get_zip_file_content($zip_path, 'footer.html', $zip_err2);
    }

    // If ZIP doesn't have them, we don't overwrite theme files (keep whatever exists)
    if ($zip_header !== '') {
        // Collect assets BEFORE stripping
        $a = ai_tg_collect_assets_from_html($zip_header);
        $all_css = array_merge($all_css, $a['css']);
        $all_js  = array_merge($all_js,  $a['js']);

        // Correct URLs then strip tags
        $header = ai_tg_correct_html_to_php($zip_header);
        $header = ai_tg_strip_css_js_tags($header);

        // Ensure wp_head before </head>
        if (stripos($header, 'wp_head') === false) {
            if (stripos($header, '</head>') !== false) {
                $header = preg_replace('#</head>#i', "<?php wp_head(); ?>\n</head>", $header, 1);
            } else {
                $header .= "\n<?php wp_head(); ?>\n";
            }
        }

        // Ensure body_class in <body ...>
        if (preg_match('#<body\b[^>]*>#i', $header) && stripos($header, 'body_class') === false) {
            $header = preg_replace('#<body\b([^>]*)>#i', '<body$1 <?php body_class(); ?> >', $header, 1);
        }

        // IMPORTANT: your header may contain navbar etc — we KEEP everything after <body>
        // We only make sure wp_head/body_class exist.
        $header_path = trailingslashit($theme_dir) . 'header.php';
        if (file_put_contents($header_path, $header) === false) {
            $err = 'Failed writing header.php into theme.';
            return false;
        }
    }

    if ($zip_footer !== '') {
        // Collect assets BEFORE stripping
        $a2 = ai_tg_collect_assets_from_html($zip_footer);
        $all_css = array_merge($all_css, $a2['css']);
        $all_js  = array_merge($all_js,  $a2['js']);

        // Correct URLs then strip tags
        $footer = ai_tg_correct_html_to_php($zip_footer);
        $footer = ai_tg_strip_css_js_tags($footer);

        // Ensure wp_footer exists (prefer before </body>)
        if (stripos($footer, 'wp_footer') === false) {
            if (stripos($footer, '</body>') !== false) {
                $footer = preg_replace('#</body>#i', "<?php wp_footer(); ?>\n</body>", $footer, 1);
            } else {
                $footer .= "\n<?php wp_footer(); ?>\n";
            }
        }

        // If footer doesn’t close body/html, that’s fine for your use-case,
        // but we add them only if missing AND footer looks like a full doc.
        // (We won’t force-close if your footer is partial.)
        $footer_path = trailingslashit($theme_dir) . 'footer.php';
        if (file_put_contents($footer_path, $footer) === false) {
            $err = 'Failed writing footer.php into theme.';
            return false;
        }
    }

    return true;
}
