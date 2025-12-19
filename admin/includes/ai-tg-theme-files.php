<?php
if ( ! defined('ABSPATH') ) exit;

function ai_tg_fs_path_without_query($rel) {
    $rel = (string)$rel;
    $rel = preg_replace('~\?.*$~', '', $rel);
    $rel = preg_replace('~#.*$~', '', $rel);
    return $rel;
}

function ai_tg_theme_asset_exists($theme_dir, $rel_path) {
    $rel_path = ai_tg_fs_path_without_query($rel_path);
    $rel_path = ltrim($rel_path, '/\\');
    $abs = trailingslashit($theme_dir) . $rel_path;
    return file_exists($abs);
}


function ai_tg_fix_page_links_by_map($html, $map = []) {
    if (empty($map) || !is_array($map)) return (string)$html;

    return preg_replace_callback(
        '/<a\b([^>]*?)\bhref=(["\'])([^"\']*)\2([^>]*)>/i',
        function ($m) use ($map) {
            $before = $m[1];
            $href   = trim((string)$m[3]);
            $after  = $m[4];

            // skip external links, #, mailto, tel, etc.
            if ($href === '' || ai_tg_should_skip_url($href)) return $m[0];

            // normalize href for matching
            $h = strtolower($href);
            $h = preg_replace('~\?.*$~', '', $h);
            $h = preg_replace('~#.*$~', '', $h);
            $h = preg_replace('~^\./~', '', $h);
            $h = ltrim($h, '/');

            $h_no_ext = preg_replace('/\.[^.]+$/', '', $h);

            // try match
            $slug = '';
            if (isset($map[$h])) $slug = $map[$h];
            elseif (isset($map[$h_no_ext])) $slug = $map[$h_no_ext];

            if ($slug) {
                return '<a' . $before . 'href="<?php echo esc_url( home_url("/' . esc_attr($slug) . '/") ); ?>"' . $after . '>';
            }

            return $m[0];
        },
        (string)$html
    );
}


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
    function ai_tg_fix_img_src($html, $theme_dir = '') {
        return preg_replace_callback(
            '/<img\b([^>]*?)\bsrc=(["\'])([^"\']+)\2([^>]*)>/i',
            function ($m) use ($theme_dir) {
                $before = $m[1];
                $src    = $m[3];
                $after  = $m[4];

                // Skip if already PHP code (from featured image replacement)
                if (strpos($src, '<?php') !== false) {
                    return $m[0];
                }

                if (ai_tg_should_skip_url($src)) return $m[0];

                $normalized = ai_tg_normalize_rel_path($src);

                // Check if this is NOT a featured image (slug-based images should have been replaced already)
                // But if not replaced, still convert to theme path
                if ($theme_dir && ai_tg_theme_asset_exists($theme_dir, $normalized)) {
                    return '<img' . $before . 'src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/' . esc_attr($normalized) . '"' . $after . '>';
                }

                return $m[0];
            },
            (string)$html
        );
    }
}


if ( ! function_exists('ai_tg_fix_inline_style_urls') ) {
    function ai_tg_fix_inline_style_urls($html, $theme_dir = '') {
        return preg_replace_callback(
            '/\bstyle=(["\'])(.*?)\1/i',
            function ($m) use ($theme_dir) {
                $outer_quote = $m[1];
                $style = $m[2];

                // Skip if already contains PHP code
                if (strpos($style, '<?php') !== false) {
                    return 'style=' . $outer_quote . $style . $outer_quote;
                }

                $style = preg_replace_callback(
                    '~url\(\s*(["\']?)([^"\')]+)\1\s*\)~i',
                    function ($u) use ($theme_dir) {
                        $url = trim((string)$u[2]);
                        if ($url === '' || ai_tg_should_skip_url($url)) return $u[0];

                        $normalized = ai_tg_normalize_rel_path($url);

                        // ✅ ONLY rewrite if exists
                        if ($theme_dir && ai_tg_theme_asset_exists($theme_dir, $normalized)) {
                            return "url('<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/" . $normalized . "')";
                        }

                        return $u[0]; // keep original (no guessing)
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
    function ai_tg_correct_html_to_php($html, $theme_dir = '') {
        $html = (string)$html;

        // Step 1: Fix home links
        $html = ai_tg_fix_home_links($html);

        // Step 2: Fix image sources (but skip PHP code)
        $html = ai_tg_fix_img_src($html, $theme_dir);

        // Step 3: Fix inline style URLs (using smart version)
        $html = ai_tg_smart_fix_inline_style_urls($html, $theme_dir);

        // Step 4: Strip CSS/JS tags
        $html = ai_tg_strip_css_js_tags($html);

        return $html;
    }
}
if ( ! function_exists('ai_tg_collect_assets_from_html') ) {
    function ai_tg_collect_assets_from_html($html) {
        $html = (string)$html;
        $css = [];
        $js  = [];

        // Extract ALL <link> tags first
        preg_match_all('#<link[^>]*>#is', $html, $all_links);

        foreach ($all_links[0] as $link_tag) {
            // Check if it's a stylesheet
            if (stripos($link_tag, 'stylesheet') === false) continue;

            // Extract href
            if (!preg_match('#href=["\']([^"\']+)["\']#i', $link_tag, $href_match)) continue;

            $href = trim($href_match[1]);
            if ($href === '') continue;

            // Skip truly non-enqueueable
            if (preg_match('~^(?:data:|mailto:|tel:|javascript:|#)~i', $href)) continue;

            // External or internal?
            if (preg_match('~^(?:https?:)?//~i', $href)) {
                $css[] = $href; // External - keep as-is
            } else {
                $css[] = ai_tg_normalize_rel_path($href); // Internal - normalize
            }
        }

        // Extract ALL <script> tags with src
        preg_match_all('#<script[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>#is', $html, $all_scripts);

        foreach ($all_scripts[1] as $src) {
            $src = trim($src);
            if ($src === '') continue;

            // Skip truly non-enqueueable
            if (preg_match('~^(?:data:|mailto:|tel:|javascript:|#)~i', $src)) continue;

            // External or internal?
            if (preg_match('~^(?:https?:)?//~i', $src)) {
                $js[] = $src; // External - keep as-is
            } else {
                $js[] = ai_tg_normalize_rel_path($src); // Internal - normalize
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

        // Copy ONLY root-level folders into theme (merge), ignore root-level files
        $root_items = array_values(array_diff(scandir($root_to_copy), ['.','..']));

        foreach ($root_items as $item) {
            $src_path = trailingslashit($root_to_copy) . $item;

            if (!is_dir($src_path)) {
                continue; // ignore root-level files
            }

            $dst_path = trailingslashit($theme_dir) . $item;
            if (!file_exists($dst_path)) {
                wp_mkdir_p($dst_path);
            }

            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($src_path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($it as $f) {
                $src = $f->getPathname();
                $rel = ltrim(str_replace($src_path, '', $src), '/\\');
                $dst = trailingslashit($dst_path) . $rel;

                if ($f->isDir()) {
                    if (!file_exists($dst)) wp_mkdir_p($dst);
                } else {
                    if (!file_exists(dirname($dst))) wp_mkdir_p(dirname($dst));
                    @copy($src, $dst);
                }
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
    function ai_tg_write_enqueues_into_functions_php($theme_dir, $css_list, $js_list, $text_domain = '', &$err = '') {
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

        $block_start = "/* ENQUEUES_START */";
        $block_end   = "/* ENQUEUES_END */";

        $lines = [];
        $lines[] = $block_start;

        $lines[] = "add_action('wp_enqueue_scripts', function () {";
        $lines[] = "/* ENQUEUES_START for CSS */";
        $i = 1;
        foreach ($css_list as $href) {
            $href = trim((string)$href);
            if ($href === '') continue;

            $handle = "ai-tg-style-{$i}";

            // Check if it's an external URL (http://, https://, or //)
            if (preg_match('~^(?:https?:)?//~i', $href)) {
                // External URL - use directly with proper escaping
                $lines[] = "    wp_enqueue_style('{$handle}', '" . esc_url_raw($href) . "', [], null);";
            } else {
                // Internal path - use theme directory
                $href = ltrim($href, '/');
                $lines[] = "    wp_enqueue_style('{$handle}', get_stylesheet_directory_uri() . '/" . addslashes($href) . "', [], null);";
            }
            $i++;
        }

        $text_domain = sanitize_key($text_domain);
        if (!$text_domain) { $text_domain = sanitize_key(wp_get_theme()->get('TextDomain')); }
        if (!$text_domain) { $text_domain = 'theme-style'; }

        $lines[] = "    // Main theme style.css";
        $lines[] = "    wp_enqueue_style('{$text_domain}', get_stylesheet_uri(), [], filemtime(get_stylesheet_directory() . '/style.css'), 'all');";

        $lines[] = "";
        $lines[] = "/* ENQUEUES_START for JSS */";
        $j = 1;
        foreach ($js_list as $src) {
            $src = trim((string)$src);
            if ($src === '') continue;

            $handle = "ai-tg-script-{$j}";

            // Check if it's an external URL (http://, https://, or //)
            if (preg_match('~^(?:https?:)?//~i', $src)) {
                // External URL - use directly with proper escaping
                $lines[] = "    wp_enqueue_script('{$handle}', '" . esc_url_raw($src) . "', ['jquery'], null, true);";
            } else {
                // Internal path - use theme directory
                $src = ltrim($src, '/');
                $lines[] = "    wp_enqueue_script('{$handle}', get_stylesheet_directory_uri() . '/" . addslashes($src) . "', ['jquery'], null, true);";
            }
            $j++;
        }

        $lines[] = "});";

        $lines[] = "add_action('after_setup_theme', function () {";
        $lines[] = "    // Featured image";
        $lines[] = "    add_theme_support('post-thumbnails');";
        $lines[] = "    // Custom logo";
        $lines[] = "    add_theme_support('custom-logo', []);";
        $lines[] = "});";
        $lines[] = "";
        $lines[] = "// Remove <p> and <br/> from Contact Form 7";
        $lines[] = "add_filter('wpcf7_autop_or_not', '__return_false');";
        $lines[] = "";

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

        $raw_content = ai_tg_strip_php_header_footer_includes($raw_content);

        // IMPORTANT: Do featured image replacement BEFORE URL fixing
        $raw_content = ai_tg_replace_slug_image_with_featured($raw_content, $slug);

        $raw_content = ai_tg_fix_page_links_by_map($raw_content, $GLOBALS['ai_tg_page_link_map'] ?? []);
        $raw_content = ai_tg_correct_html_to_php($raw_content, $theme_dir);

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




function ai_tg_build_header_footer_from_zip($zip_path, $theme_dir, &$all_css = [], &$all_js = [], $page_link_map = [], &$err = '') {
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
        $header = ai_tg_fix_page_links_by_map($header, $page_link_map);

        // Force dynamic language attributes on <html>
        $header = preg_replace(
            '#<html\b[^>]*>#i',
            '<html <?php language_attributes(); ?>>',
            $header,
            1
        );


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

        // Correct URLs + strip assets (already handled internally)
        $footer = ai_tg_correct_html_to_php($zip_footer);
        $footer = ai_tg_fix_page_links_by_map($footer, $page_link_map);


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


if (!function_exists('ai_tg_upload_and_set_featured_image')) {
    function ai_tg_upload_and_set_featured_image($post_id, $abs_path, $title = '', &$err = '') {
        $err = '';
        $abs_path = (string) $abs_path;

        if (!$post_id || !file_exists($abs_path)) {
            $err = 'Image file missing: ' . $abs_path;
            return 0;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $filename = basename($abs_path);

        // Copy to a true temp file
        $tmp = wp_tempnam($filename);
        if (!$tmp) {
            $err = 'wp_tempnam() failed';
            return 0;
        }

        if (!@copy($abs_path, $tmp)) {
            @unlink($tmp);
            $err = 'Failed to copy to temp: ' . $abs_path;
            return 0;
        }

        $file_array = [
            'name'     => $filename,
            'tmp_name' => $tmp,
            'error'    => 0,
            'size'     => filesize($tmp),
        ];

        // Do the sideload
        $att_id = media_handle_sideload($file_array, $post_id, $title ?: '');

        // Always cleanup temp
        if (file_exists($tmp)) {
            @unlink($tmp);
        }

        if (is_wp_error($att_id)) {
            $err = 'media_handle_sideload failed: ' . $att_id->get_error_message();
            return 0;
        }

        set_post_thumbnail($post_id, (int)$att_id);
        return (int)$att_id;
    }
}

if ( ! function_exists('ai_tg_smart_fix_inline_style_urls') ) {
    function ai_tg_smart_fix_inline_style_urls($html, $theme_dir = '') {
        $html = (string)$html;

        // First, extract and process all style attributes
        $html = preg_replace_callback(
            '/\bstyle=(["\'])(.*?)\1/i',
            function ($m) use ($theme_dir) {
                $outer_quote = $m[1];
                $style = $m[2];

                // Skip if already contains PHP code
                if (strpos($style, '<?php') !== false) {
                    return 'style=' . $outer_quote . $style . $outer_quote;
                }

                // Process URLs in the style
                $style = preg_replace_callback(
                    '~url\(\s*(["\']?)([^"\')]+)\1\s*\)~i',
                    function ($u) use ($theme_dir) {
                        $url = trim((string)$u[2]);
                        if ($url === '' || ai_tg_should_skip_url($url)) return $u[0];

                        // Check if it's a relative path
                        if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
                            $normalized = ai_tg_normalize_rel_path($url);
                            if ($theme_dir && ai_tg_theme_asset_exists($theme_dir, $normalized)) {
                                return "url('<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/" . $normalized . "')";
                            }
                        }

                        return $u[0];
                    },
                    $style
                );

                return 'style=' . $outer_quote . $style . $outer_quote;
            },
            $html
        );

        return $html;
    }
}