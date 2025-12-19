<?php
if ( ! defined('ABSPATH') ) exit;

if ( ! current_user_can('manage_options') ) {
    wp_die( esc_html__('Permission denied.', 'ai_assistant') );
}

$errors = [];
$success = [];
$detected_pages = [];

$is_submitted = (
    isset($_POST['ai_tg_nonce']) &&
    wp_verify_nonce($_POST['ai_tg_nonce'], 'ai_tg_generate_theme')
);

$step = '1';
if ($is_submitted) {
    $step = isset($_POST['ai_tg_step']) ? sanitize_text_field($_POST['ai_tg_step']) : '1';
    if (!in_array($step, ['1','2'], true)) $step = '1';
}

require_once plugin_dir_path(__FILE__) . '../includes/ai-tg-helpers.php';
require_once plugin_dir_path(__FILE__) . '../includes/ai-tg-theme-files.php';


/**
 * -----------------------------
 * Phase 4: ZIP → theme + correct html
 * -----------------------------
 */

// Add to create_theme.php after the includes
function ai_tg_debug_log($message, $data = null) {
    error_log('AI_TG_DEBUG: ' . $message);
    if ($data !== null) {
        error_log('AI_TG_DEBUG DATA: ' . print_r($data, true));
    }
}

function ai_tg_should_skip_url($url) {
    return (bool) preg_match('~^(?:https?:)?//|data:|mailto:|tel:|#~i', trim((string)$url));
}

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

function ai_tg_extract_body_inner($html) {
    $html = (string)$html;
    if (preg_match('#<body[^>]*>(.*)</body>#is', $html, $m)) {
        return trim($m[1]);
    }
    return trim($html);
}

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

if ( ! function_exists('ai_tg_fix_inline_style_urls') ) {
    function ai_tg_fix_inline_style_urls($html, $theme_dir = '') {
        return preg_replace_callback(
            '/\bstyle=(["\'])(.*?)\1/i',
            function ($m) use ($theme_dir) {
                $outer_quote = $m[1];
                $style = $m[2];

                $style = preg_replace_callback(
                    '~url\(\s*(["\']?)([^"\')]+)\1\s*\)~i',
                    function ($u) use ($theme_dir) {
                        $url = trim((string)$u[2]);
                        if ($url === '' || ai_tg_should_skip_url($url)) return $u[0];

                        $normalized = ai_tg_normalize_rel_path($url);

                        // ✅ ONLY rewrite if exists
                        if ($theme_dir && ai_tg_theme_asset_exists($theme_dir, $normalized)) {
                            return "url('<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/$normalized')";
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
        $html = (string) $html;

        // 1) Remove ANY <link ... href="*.css"> (stylesheet, preload-as-style, etc.)
        $html = preg_replace(
            '#<link\b[^>]*\bhref=["\'][^"\']+\.css(?:\?[^"\']*)?["\'][^>]*>\s*#is',
            '',
            $html
        );

        // 2) Remove ANY <script ... src="..."></script> (incl. module/defer/async)
        $html = preg_replace(
            '#<script\b[^>]*\bsrc=["\'][^"\']+["\'][^>]*>\s*</script>\s*#is',
            '',
            $html
        );

        // 3) Safety: remove rare self-closed script tags (just in case)
        $html = preg_replace(
            '#<script\b[^>]*\bsrc=["\'][^"\']+["\'][^>]*/>\s*#is',
            '',
            $html
        );

        return $html;
    }
}


if ( ! function_exists('ai_tg_correct_html_to_php') ) {
    function ai_tg_correct_html_to_php($html, $theme_dir = '') {
        $html = (string)$html;
        $html = ai_tg_fix_home_links($html);
        $html = ai_tg_fix_img_src($html, $theme_dir);
        $html = ai_tg_fix_inline_style_urls($html, $theme_dir);
        $html = ai_tg_strip_css_js_tags($html);
        return $html;
    }
}


if ( ! function_exists('ai_tg_collect_assets_from_html') ) {
    function ai_tg_collect_assets_from_html($html) {
        error_log('COLLECTING ASSETS - Function called');
        $html = (string)$html;
        $css = [];
        $js  = [];

        preg_match_all('#<link[^>]*>#is', $html, $all_links);
        error_log('Found ' . count($all_links[0]) . ' link tags total');

        foreach ($all_links[0] as $link_tag) {
            if (stripos($link_tag, 'stylesheet') === false) continue;
            if (!preg_match('#href=["\']([^"\']+)["\']#i', $link_tag, $href_match)) continue;

            $href = trim($href_match[1]);
            if ($href === '' || preg_match('~^(?:data:|mailto:|tel:|javascript:|#)~i', $href)) continue;

            if (preg_match('~^(?:https?:)?//~i', $href)) {
                $css[] = $href;
                error_log('EXTERNAL CSS: ' . $href);
            } else {
                $css[] = ai_tg_normalize_rel_path($href);
                error_log('INTERNAL CSS: ' . $href);
            }
        }

        preg_match_all('#<script[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>#is', $html, $all_scripts);

        foreach ($all_scripts[1] as $src) {
            $src = trim($src);
            if ($src === '' || preg_match('~^(?:data:|mailto:|tel:|javascript:|#)~i', $src)) continue;

            if (preg_match('~^(?:https?:)?//~i', $src)) {
                $js[] = $src;
            } else {
                $js[] = ai_tg_normalize_rel_path($src);
            }
        }

        return ['css' => array_values(array_unique($css)), 'js' => array_values(array_unique($js))];
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

        // wrapper folder detection (if zip has a single top folder)
        $items = array_values(array_diff(scandir($tmp), ['.','..']));
        $root_to_copy = $tmp;
        if (count($items) === 1) {
            $one = $tmp . $items[0];
            if (is_dir($one)) $root_to_copy = trailingslashit($one);
        }

        // Copy EVERYTHING (files + folders) from extracted root into theme
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root_to_copy, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($it as $f) {
            $src = $f->getPathname();
            $rel = ltrim(str_replace($root_to_copy, '', $src), '/\\');
            if ($rel === '') continue;

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

/**
 * Create WP pages + templates
 */
function ai_tg_create_wp_pages_with_templates($rows, $page_status, $zip_saved_path, $theme_dir, $target_mode, $new_theme_slug_for_activation = '', $activate_theme = false) {
    $created = [];
    $errors = [];

    if (!current_user_can('manage_options')) {
        return [[], ['Permission denied.']];
    }

    $page_status = ($page_status === 'publish') ? 'publish' : 'draft';

    // Optionally activate new theme
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

        // Featured image (optional per page)
        $do_featured = !empty($row['featured']) && (string)$row['featured'] === '1';

        if ($do_featured) {
            $img_path = ai_tg_find_featured_image_for_slug($theme_dir, $slug);

            if ($img_path) {
                $up_err = '';
                $att_id = ai_tg_upload_and_set_featured_image($post_id, $img_path, $title, $up_err);

                if (!$att_id) {
                    $errors[] = "Featured image FAILED for '{$title}' ({$slug}): {$up_err}";
                } else {
                    // Successfully set featured image
                    $success[] = "Featured image set for '{$title}' (attachment ID: {$att_id})";
                }
            } else {
                $errors[] = "Featured image NOT FOUND for '{$title}' ({$slug}). No matching image file.";
            }
        }

        $created[] = [
            'id'       => (int)$post_id,
            'title'    => $title,
            'slug'     => $slug,
            'template' => $template_file,
        ];

        // Inside the foreach loop in ai_tg_create_wp_pages_with_templates function
        $zip_err = '';
        $content = ai_tg_get_zip_file_content($zip_saved_path, $source_basename, $zip_err);

        // DEBUG: Log the original content
        error_log('AI_TG_DEBUG: Original content for ' . $source_basename);
        error_log('AI_TG_DEBUG: First 1000 chars: ' . substr($content, 0, 1000));

        if ($zip_err || $content === '') {
            $errors[] = "Failed reading '{$source_basename}' from ZIP for '{$title}': " . ($zip_err ?: 'Empty content');
            continue;
        }
    }

    return [$created, $errors];
}
/**
 * -----------------------------
 * Handle submit steps
 * -----------------------------
 */

$zip_saved_path = '';
$target_mode = isset($_POST['ai_tg_target_mode']) ? sanitize_text_field($_POST['ai_tg_target_mode']) : 'existing';
if (!in_array($target_mode, ['existing','new'], true)) $target_mode = 'existing';

$new_theme = [
    'name'        => isset($_POST['ai_tg_theme_name']) ? sanitize_text_field($_POST['ai_tg_theme_name']) : '',
    'slug'        => isset($_POST['ai_tg_theme_slug']) ? sanitize_title($_POST['ai_tg_theme_slug']) : '',
    'text_domain' => isset($_POST['ai_tg_text_domain']) ? sanitize_title($_POST['ai_tg_text_domain']) : '',
    'theme_uri'   => isset($_POST['ai_tg_theme_uri']) ? esc_url_raw($_POST['ai_tg_theme_uri']) : '',
    'author'      => isset($_POST['ai_tg_author']) ? sanitize_text_field($_POST['ai_tg_author']) : '',
    'author_uri'  => isset($_POST['ai_tg_author_uri']) ? esc_url_raw($_POST['ai_tg_author_uri']) : '',
];

$activate_theme = !empty($_POST['ai_tg_activate_theme']) && (string)$_POST['ai_tg_activate_theme'] === '1';

if ($is_submitted) {

    // STEP 1
    if ($step === '1') {

        if (empty($_FILES['ai_tg_zip']['name'])) {
            $errors[] = 'Please upload a ZIP file.';
        } else {
            $saved = ai_tg_save_uploaded_zip($_FILES['ai_tg_zip']);
            if (is_wp_error($saved)) {
                $errors[] = $saved->get_error_message();
            } else {
                $zip_saved_path = $saved;

                [$detected_pages, $zip_err] = ai_tg_detect_pages_from_zip($zip_saved_path);
                if ($zip_err) $errors[] = $zip_err;
            }
        }
    }

    // STEP 2
    if ($step === '2') {

        $zip_saved_path = isset($_POST['ai_tg_zip_saved']) ? sanitize_text_field($_POST['ai_tg_zip_saved']) : '';
        if (!$zip_saved_path || !file_exists($zip_saved_path)) {
            $errors[] = 'Saved ZIP file is missing. Please run Step 1 (Upload & Scan) again.';
        }

        $page_status = isset($_POST['ai_tg_page_status']) ? sanitize_text_field($_POST['ai_tg_page_status']) : 'draft';
        if (!in_array($page_status, ['draft','publish'], true)) $page_status = 'draft';

        $rows = isset($_POST['ai_tg_pages']) ? (array) $_POST['ai_tg_pages'] : [];
        $clean = [];

        foreach ($rows as $i => $r) {
            if (!is_array($r)) continue;
            $clean[$i] = [
                'path'   => isset($r['path']) ? sanitize_text_field($r['path']) : '',
                'title'  => isset($r['title']) ? sanitize_text_field($r['title']) : '',
                'slug'   => isset($r['slug']) ? sanitize_text_field($r['slug']) : '',
                'create' => isset($r['create']) ? sanitize_text_field($r['create']) : '0',
                'featured' => isset($r['featured']) ? sanitize_text_field($r['featured']) : '0',
            ];
        }

        // Keep table after submit
        $detected_pages = [];
        foreach ($clean as $r) {
            $detected_pages[] = [
                'path'            => $r['path'],
                'suggested_title' => $r['title'],
                'suggested_slug'  => ai_tg_slugify($r['slug']),
                'create'          => $r['create'],
                'featured'        => $r['featured'],
            ];
        }

        // Theme directory (existing or new)
        $created_theme_slug = '';
        $theme_dir = ai_tg_get_target_theme_dir($target_mode, $new_theme, $created_theme_slug);

        if (!file_exists($theme_dir)) {
            $errors[] = 'Theme directory not found or could not be created.';
        }

        /**
         * Phase 4 order (CORRECTED):
         * 1) Extract ZIP to theme root
         * 2) Collect ALL assets from ZIP originals (header/footer/pages) BEFORE any modification
         * 3) Correct header/footer (strip tags, add wp_head/wp_footer)
         * 4) Write enqueues to functions.php
         * 5) Create page templates
         */

        // 1) Extract ZIP to theme root
        if (empty($errors)) {
            $extract_err = '';
            if (!ai_tg_extract_zip_to_theme_root($zip_saved_path, $theme_dir, $extract_err)) {
                $errors[] = $extract_err ?: 'Failed extracting ZIP into theme.';
            }
        }

        $all_css = [];
        $all_js  = [];

        // 2) Collect ALL assets from ZIP originals (BEFORE any file modifications)
        if (empty($errors)) {

            error_log('=== ASSET COLLECTION DEBUG START ===');
            error_log('ZIP path: ' . $zip_saved_path);
            error_log('ZIP exists: ' . (file_exists($zip_saved_path) ? 'YES' : 'NO'));

            // 2a) Collect from header.php/header.html in ZIP
            $zip_err = '';
            $zip_header = ai_tg_get_zip_file_content($zip_saved_path, 'header.php', $zip_err);

            error_log('Looking for header.php...');
            error_log('Zip error: ' . $zip_err);
            error_log('Header content length: ' . strlen($zip_header));

            if ($zip_err || $zip_header === '') {
                error_log('header.php not found or empty, trying header.html...');
                $zip_err = '';
                $zip_header = ai_tg_get_zip_file_content($zip_saved_path, 'header.html', $zip_err);
                error_log('header.html zip error: ' . $zip_err);
                error_log('header.html content length: ' . strlen($zip_header));
            }

            if ($zip_header !== '') {
                error_log('Header found! First 200 chars:');
                error_log(substr($zip_header, 0, 200));

                $a = ai_tg_collect_assets_from_html($zip_header);
                error_log('Assets collected from header:');
                error_log('CSS count: ' . count($a['css']));
                error_log('CSS: ' . print_r($a['css'], true));
                error_log('JS count: ' . count($a['js']));
                error_log('JS: ' . print_r($a['js'], true));

                $all_css = array_merge($all_css, $a['css']);
                $all_js  = array_merge($all_js,  $a['js']);
            } else {
                error_log('NO HEADER FOUND IN ZIP!');
            }

            // 2b) Collect from footer.php/footer.html in ZIP
            $zip_err2 = '';
            $zip_footer = ai_tg_get_zip_file_content($zip_saved_path, 'footer.php', $zip_err2);

            error_log('Looking for footer.php...');
            error_log('Zip error: ' . $zip_err2);

            if ($zip_err2 || $zip_footer === '') {
                error_log('footer.php not found or empty, trying footer.html...');
                $zip_err2 = '';
                $zip_footer = ai_tg_get_zip_file_content($zip_saved_path, 'footer.html', $zip_err2);
                error_log('footer.html zip error: ' . $zip_err2);
            }

            if ($zip_footer !== '') {
                error_log('Footer found!');
                $a2 = ai_tg_collect_assets_from_html($zip_footer);
                error_log('Assets from footer - CSS: ' . count($a2['css']) . ', JS: ' . count($a2['js']));
                $all_css = array_merge($all_css, $a2['css']);
                $all_js  = array_merge($all_js,  $a2['js']);
            } else {
                error_log('NO FOOTER FOUND IN ZIP!');
            }

            // 2c) Collect from selected pages
            error_log('Collecting from pages...');
            foreach ($clean as $r) {
                if (empty($r['create']) || (string)$r['create'] !== '1') continue;

                $source_basename = basename($r['path']);
                error_log('Looking for page: ' . $source_basename);

                $zip_err3 = '';
                $html = ai_tg_get_zip_file_content($zip_saved_path, $source_basename, $zip_err3);

                if ($zip_err3 || $html === '') {
                    error_log('Page not found or error: ' . $zip_err3);
                    continue;
                }

                $assets = ai_tg_collect_assets_from_html($html);
                error_log('Page assets - CSS: ' . count($assets['css']) . ', JS: ' . count($assets['js']));
                $all_css = array_merge($all_css, $assets['css']);
                $all_js  = array_merge($all_js,  $assets['js']);
            }

            // Remove duplicates
            $all_css = array_values(array_unique(array_filter($all_css)));
            $all_js  = array_values(array_unique(array_filter($all_js)));

            error_log('=== FINAL TOTALS ===');
            error_log('Total unique CSS: ' . count($all_css));
            error_log('All CSS: ' . print_r($all_css, true));
            error_log('Total unique JS: ' . count($all_js));
            error_log('All JS: ' . print_r($all_js, true));
            error_log('=== ASSET COLLECTION DEBUG END ===');
        }

        // Build map: "sobre-nos.php" => "sobre-nos" (slug from table)
        $page_link_map = [];
        foreach ($clean as $r) {
            $path = isset($r['path']) ? basename($r['path']) : '';
            $slug = isset($r['slug']) ? sanitize_title($r['slug']) : '';
            if (!$path || !$slug) continue;

            $base_no_ext = preg_replace('/\.[^.]+$/', '', $path);

            $page_link_map[strtolower($path)] = $slug;        // sobre-nos.php
            $page_link_map[strtolower($base_no_ext)] = $slug; // sobre-nos
        }
        $GLOBALS['ai_tg_page_link_map'] = $page_link_map;



        // 3) Build header/footer from ZIP (ONCE)
        if (empty($errors)) {
            $hf_err = '';
            if (!ai_tg_build_header_footer_from_zip(
                $zip_saved_path,
                $theme_dir,
                $all_css,
                $all_js,
                $page_link_map,
                $hf_err
            )) {

                $errors[] = $hf_err ?: 'Failed generating header/footer from ZIP.';
            }
        }


        // 4) Write enqueues to functions.php
        if (empty($errors)) {
            $fn_err = '';
            if (!ai_tg_write_enqueues_into_functions_php($theme_dir, $all_css, $all_js, $new_theme['text_domain'], $fn_err)) {
                $errors[] = $fn_err ?: 'Failed writing enqueues to functions.php';
            }
        }

        // 5) Create pages + templates
        if (empty($errors)) {
            [$created, $create_errors] = ai_tg_create_wp_pages_with_templates(
                $clean,
                $page_status,
                $zip_saved_path,
                $theme_dir,
                $target_mode,
                $created_theme_slug,
                $activate_theme
            );

            if (!empty($created)) {
                $success[] = 'Created ' . count($created) . ' WordPress page(s) as ' . $page_status . '.';
                foreach ($created as $c) {
                    $success[] = "Created: {$c['title']} (slug: {$c['slug']}) — Template: {$c['template']} — ID {$c['id']}";
                }
            }

            foreach ($create_errors as $e) {
                $errors[] = $e;
            }
        }


    }

}

// Always safe
$detected_pages = is_array($detected_pages) ? $detected_pages : [];
?>
<div class="wrap ai-theme-generator">
    <h1>Theme Generator</h1>
    <p class="ai-tg-subtitle">Upload a ZIP and generate pages using the existing theme or a new theme.</p>

    <?php if (!empty($success)): ?>
        <div class="notice notice-success">
            <p><strong>Success</strong></p>
            <ul>
                <?php foreach ($success as $s): ?>
                    <li><?php echo esc_html($s); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="notice notice-error">
            <p><strong>Issues</strong></p>
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo esc_html($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="ai-theme-generator-form" class="ai-tg-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('ai_tg_generate_theme', 'ai_tg_nonce'); ?>

        <input type="hidden" name="ai_tg_step" value="<?php echo esc_attr($step); ?>" id="ai_tg_step">
        <input type="hidden" name="ai_tg_zip_saved" value="<?php echo esc_attr($zip_saved_path); ?>" id="ai_tg_zip_saved">

        <!-- TARGET MODE -->
        <div class="ai-tg-card">
            <h2 class="ai-tg-card-title">1) Where should we generate?</h2>

            <div class="ai-tg-radio-row">
                <label class="ai-tg-radio">
                    <input type="radio" name="ai_tg_target_mode" value="existing" <?php checked($target_mode, 'existing'); ?>>
                    <span class="ai-tg-radio-ui"></span>
                    <span class="ai-tg-radio-text">
                        <strong>Use existing active theme</strong>
                        <small>Generate templates inside current theme</small>
                    </span>
                </label>

                <label class="ai-tg-radio">
                    <input type="radio" name="ai_tg_target_mode" value="new" <?php checked($target_mode, 'new'); ?>>
                    <span class="ai-tg-radio-ui"></span>
                    <span class="ai-tg-radio-text">
                        <strong>Create a new theme</strong>
                        <small>Creates a theme folder + templates inside it</small>
                    </span>
                </label>
            </div>

            <!-- NEW THEME OPTIONS -->
            <div class="ai-tg-new-theme-options">
                <div class="ai-tg-grid">
                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_theme_name">Theme Name *</label>
                        <input type="text" id="ai_tg_theme_name" name="ai_tg_theme_name"
                               value="<?php echo esc_attr($new_theme['name']); ?>"
                               placeholder="My New Theme">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_theme_slug">Theme Slug</label>
                        <input type="text" id="ai_tg_theme_slug" name="ai_tg_theme_slug"
                               value="<?php echo esc_attr($new_theme['slug']); ?>"
                               placeholder="my-new-theme">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_text_domain">Text Domain *</label>
                        <input type="text" id="ai_tg_text_domain" name="ai_tg_text_domain"
                               value="<?php echo esc_attr($new_theme['text_domain']); ?>"
                               placeholder="my-new-theme">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_theme_uri">Theme URI</label>
                        <input type="url" id="ai_tg_theme_uri" name="ai_tg_theme_uri"
                               value="<?php echo esc_attr($new_theme['theme_uri']); ?>"
                               placeholder="https://example.com">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_author">Author</label>
                        <input type="text" id="ai_tg_author" name="ai_tg_author"
                               value="<?php echo esc_attr($new_theme['author']); ?>">
                    </div>

                    <div class="ai-field ai-tg-field">
                        <label for="ai_tg_author_uri">Author URI</label>
                        <input type="url" id="ai_tg_author_uri" name="ai_tg_author_uri"
                               value="<?php echo esc_attr($new_theme['author_uri']); ?>">
                    </div>
                </div>

                <label class="ai-tg-check">
                    <input type="checkbox" id="ai_tg_activate_theme" name="ai_tg_activate_theme" value="1" <?php checked($activate_theme, true); ?>>
                    <span class="ai-tg-check-ui"></span>
                    <span class="ai-tg-check-text">
                        Activate theme after generation <small>(optional)</small>
                    </span>
                </label>
            </div>
        </div>

        <!-- ZIP UPLOAD -->
        <div class="ai-tg-card">
            <h2 class="ai-tg-card-title">2) Upload ZIP</h2>
            <div class="ai-field ai-tg-upload">
                <label for="ai_tg_zip">ZIP File *</label>
                <input type="file" id="ai_tg_zip" name="ai_tg_zip" accept=".zip">
                <p class="description">Step 2 will use the saved ZIP path automatically.</p>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="ai-tg-actions">
            <button type="submit" class="button button-primary ai-tg-btn" id="ai_tg_btn_scan">
                Upload & Scan
            </button>
            <button type="reset" class="button ai-tg-btn-secondary">Reset</button>
        </div>

        <!-- OUTPUT -->
        <div class="ai-tg-card ai-tg-log">
            <h2 class="ai-tg-card-title">Output</h2>
            <div class="ai-tg-log-box">
                <div class="ai-tg-log-line"><span class="ai-dot"></span> Ready</div>
                <div class="ai-tg-log-line ai-muted">Scan ZIP → edit title/slug → create WP pages + templates.</div>
            </div>
        </div>

        <?php if ($is_submitted && $step === '1' && !empty($zip_saved_path) && file_exists($zip_saved_path)): ?>
            <div class="ai-card">
                <h2 class="ai-card-title">Detected Pages (Root level)</h2>

                <table class="ai-table" id="ai-tg-pages-table">
                    <thead>
                    <tr>
                        <th>File</th>
                        <th>Page Title</th>
                        <th>Slug</th>
                        <th>Create</th>
                        <th>Featured Image</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($detected_pages)): ?>
                        <tr>
                            <td colspan="4" style="padding:14px;">
                                <em>No root-level pages detected (html/php) or ZIP scan failed.</em>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($detected_pages as $i => $p): ?>
                            <tr class="ai-page-row">
                                <td>
                                    <code><?php echo esc_html($p['path']); ?></code>
                                    <input type="hidden" name="ai_tg_pages[<?php echo (int)$i; ?>][path]"
                                           value="<?php echo esc_attr($p['path']); ?>">
                                </td>

                                <td>
                                    <input type="text"
                                           class="regular-text ai-page-title"
                                           name="ai_tg_pages[<?php echo (int)$i; ?>][title]"
                                           value="<?php echo esc_attr($p['suggested_title']); ?>"
                                           autocomplete="off">
                                </td>

                                <td>
                                    <input type="text"
                                           class="regular-text ai-page-slug"
                                           name="ai_tg_pages[<?php echo (int)$i; ?>][slug]"
                                           value="<?php echo esc_attr($p['suggested_slug']); ?>"
                                           autocomplete="off">
                                </td>
                                <td style="text-align:center;">
                                    <label class="ai-switch">
                                        <input type="checkbox"
                                               name="ai_tg_pages[<?php echo (int)$i; ?>][create]"
                                               value="1"
                                            <?php checked(!empty($p['create']), true); ?>>
                                        <span class="ai-switch-slider"></span>
                                    </label>
                                </td>

                                <td style="text-align:center;">
                                    <label class="ai-switch">
                                        <input type="checkbox"
                                               name="ai_tg_pages[<?php echo (int)$i; ?>][featured]"
                                               value="1"
                                            <?php checked(!empty($p['featured']), true); ?>>
                                        <span class="ai-switch-slider"></span>
                                    </label>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>

                <?php if (!empty($detected_pages)): ?>
                    <div class="ai-tg-card" style="margin-top:14px;">
                        <h2 class="ai-tg-card-title">3) Page Status</h2>

                        <div class="ai-field ai-tg-field">
                            <label for="ai_tg_page_status">Create pages as</label>
                            <select id="ai_tg_page_status" name="ai_tg_page_status">
                                <option value="draft" selected>Draft</option>
                                <option value="publish">Publish</option>
                            </select>
                            <p class="description">Draft is safer while testing.</p>
                        </div>

                        <div class="ai-tg-actions" style="margin-top:14px;">
                            <button type="submit" class="button button-primary ai-tg-btn" id="ai_tg_btn_create_pages">
                                Create Selected WordPress Pages + Templates
                            </button>
                            <p class="description" style="margin:8px 0 0;">
                                This will generate <code>page-{slug}.php</code> inside the theme root and attach it to each created page.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

    </form>
</div>

