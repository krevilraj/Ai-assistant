<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * Remove CSS + JS assets but keep structure
 */
function ai_tg_strip_assets($html) {

    // Remove <link rel="stylesheet" ...>
    $html = preg_replace(
        '#<link(?![^>]*rel=["\']preconnect["\'])[^>]*href=["\'][^"\']+\.css[^"\']*["\'][^>]*>#i',
        '',
        $html
    );

    // Remove <script src="..."></script>
    $html = preg_replace(
        '#<script[^>]*src=["\'][^"\']+["\'][^>]*>\s*</script>#i',
        '',
        $html
    );

    return $html;
}

if ( ! function_exists('ai_tg_slugify') ) {
    function ai_tg_slugify($str) {
        $str = strtolower(trim((string) $str));
        $str = preg_replace('/[^\p{L}\p{N}]+/u', '-', $str);
        $str = preg_replace('/-+/', '-', $str);
        $str = trim($str, '-');
        $str = preg_replace('/[^a-z0-9-]/', '', $str);
        return $str ?: 'page';
    }
}

if ( ! function_exists('ai_tg_title_from_filename') ) {
    function ai_tg_title_from_filename($filename) {
        $name = preg_replace('/\.[^.]+$/', '', (string)$filename);
        $name = str_replace(['-', '_'], ' ', $name);
        $name = trim($name);

        if ($name === 'index') return 'Home';
        if ($name === 'home')  return 'Home';

        return ucwords($name);
    }
}

/**
 * Return array of zip entry names (files only).
 * Uses ZipArchive if available; otherwise uses WP's PclZip.
 */
if ( ! function_exists('ai_tg_zip_list_entries') ) {
    function ai_tg_zip_list_entries($zip_path, &$err = '') {
        $err = '';
        $names = [];

        if ( ! file_exists($zip_path) ) {
            $err = 'ZIP file not found.';
            return [];
        }

        // ZipArchive
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zip_path) !== true) {
                $err = 'Could not open the ZIP file.';
                return [];
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (empty($stat['name'])) continue;
                $n = $stat['name'];
                if (substr($n, -1) === '/') continue;
                $names[] = $n;
            }

            $zip->close();
            return $names;
        }

        // PclZip fallback
        if ( ! class_exists('PclZip') ) {
            $pcl = ABSPATH . 'wp-admin/includes/class-pclzip.php';
            if (file_exists($pcl)) {
                require_once $pcl;
            }
        }

        if ( ! class_exists('PclZip') ) {
            $err = 'ZIP support is not available (ZipArchive missing and PclZip not found).';
            return [];
        }

        $archive = new PclZip($zip_path);
        $list = $archive->listContent();
        if ($list === 0) {
            $err = 'Could not read the ZIP file (PclZip failed).';
            return [];
        }

        foreach ($list as $item) {
            if (empty($item['filename'])) continue;
            $n = $item['filename'];
            if (substr($n, -1) === '/') continue;
            $names[] = $n;
        }

        return $names;
    }
}

/**
 * Get file content from zip by matching basename (root wrapped zips supported).
 */
if ( ! function_exists('ai_tg_get_zip_file_content') ) {
    function ai_tg_get_zip_file_content($zip_path, $wanted_basename, &$err = '') {
        $err = '';

        if ( ! file_exists($zip_path) ) {
            $err = 'ZIP file not found.';
            return '';
        }

        $wanted_basename = (string)$wanted_basename;

        // ZipArchive
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($zip_path) !== true) {
                $err = 'Could not open the ZIP file.';
                return '';
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (!$name || substr($name, -1) === '/') continue;

                if (basename($name) === $wanted_basename) {
                    $c = $zip->getFromIndex($i);
                    $zip->close();

                    if ($c === false) {
                        $err = 'Could not read file from ZIP: ' . $wanted_basename;
                        return '';
                    }
                    return $c;
                }
            }

            $zip->close();
            $err = 'File not found in ZIP: ' . $wanted_basename;
            return '';
        }

        // PclZip fallback
        if ( ! class_exists('PclZip') ) {
            $pcl = ABSPATH . 'wp-admin/includes/class-pclzip.php';
            if (file_exists($pcl)) {
                require_once $pcl;
            }
        }

        if ( ! class_exists('PclZip') ) {
            $err = 'ZIP support is not available (ZipArchive missing and PclZip not found).';
            return '';
        }

        $archive = new PclZip($zip_path);
        $list = $archive->listContent();
        if ($list === 0) {
            $err = 'Could not read the ZIP file (PclZip failed).';
            return '';
        }

        $upload = wp_upload_dir();
        $tmp_dir = trailingslashit($upload['basedir']) . 'ai_theme_generator_tmp/';
        if (!file_exists($tmp_dir)) {
            wp_mkdir_p($tmp_dir);
        }

        foreach ($list as $item) {
            if (empty($item['filename'])) continue;
            if (substr($item['filename'], -1) === '/') continue;

            if (basename($item['filename']) === $wanted_basename) {
                $res = $archive->extract(
                    PCLZIP_OPT_BY_NAME, $item['filename'],
                    PCLZIP_OPT_PATH, $tmp_dir,
                    PCLZIP_OPT_REPLACE_NEWER
                );

                if ($res === 0) {
                    $err = 'Failed extracting: ' . $wanted_basename;
                    return '';
                }

                // find extracted
                $found = '';
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp_dir, RecursiveDirectoryIterator::SKIP_DOTS));
                foreach ($it as $f) {
                    if ($f->isFile() && $f->getFilename() === $wanted_basename) {
                        $found = $f->getPathname();
                        break;
                    }
                }

                if (!$found || !file_exists($found)) {
                    $err = 'Extracted file missing: ' . $wanted_basename;
                    return '';
                }

                $content = file_get_contents($found);
                if ($content === false) {
                    $err = 'Could not read extracted file: ' . $wanted_basename;
                    return '';
                }

                return $content;
            }
        }

        $err = 'File not found in ZIP: ' . $wanted_basename;
        return '';
    }
}

/**
 * Detect root-level html/php pages from ZIP + exclude rules.
 */
if ( ! function_exists('ai_tg_detect_pages_from_zip') ) {
    function ai_tg_detect_pages_from_zip($zip_path) {
        $pages = [];
        $zip_err = '';

        $names = ai_tg_zip_list_entries($zip_path, $zip_err);
        if ($zip_err) return [[], $zip_err];
        if (empty($names)) return [[], 'ZIP is empty (no files).'];

        // Detect top folder prefix
        $prefix = '';
        $first = $names[0];
        if (strpos($first, '/') !== false) {
            $maybe = substr($first, 0, strpos($first, '/') + 1);
            $all_have = true;
            foreach ($names as $n) {
                if (strpos($n, $maybe) !== 0) { $all_have = false; break; }
            }
            if ($all_have) $prefix = $maybe;
        }

        $exclude_files = [
            'header.php','footer.php','functions.php',
            'single.php','archive.php','404.php','search.php',
            'style.css','screenshot.png',
        ];

        foreach ($names as $n) {
            $relative = $prefix ? preg_replace('#^' . preg_quote($prefix, '#') . '#', '', $n) : $n;

            // root-level only
            if (strpos($relative, '/') !== false) continue;

            $ext = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
            if (!in_array($ext, ['html', 'php'], true)) continue;

            $lower = strtolower($relative);
            if (in_array($lower, $exclude_files, true)) continue;
            if (preg_match('/-single\.php$/', $lower)) continue;
            if (preg_match('/-archive\.php$/', $lower)) continue;

            $title = ai_tg_title_from_filename($relative);
            $slug  = ai_tg_slugify($title);

            $pages[] = [
                'path'            => $relative,
                'suggested_title' => $title,
                'suggested_slug'  => $slug,
            ];
        }

        return [$pages, ''];
    }
}

/**
 * Save uploaded zip into /uploads/ai-theme-generator/
 */
if ( ! function_exists('ai_tg_save_uploaded_zip') ) {
    function ai_tg_save_uploaded_zip($file) {
        if (empty($file['tmp_name']) || !file_exists($file['tmp_name'])) {
            return new WP_Error('zip_missing', 'Uploaded ZIP not found (tmp file missing).');
        }

        $name = isset($file['name']) ? (string)$file['name'] : 'upload.zip';
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            return new WP_Error('zip_type', 'Only ZIP files are allowed.');
        }

        $upload = wp_upload_dir();
        if (!empty($upload['error'])) {
            return new WP_Error('upload_dir', $upload['error']);
        }

        $dir = trailingslashit($upload['basedir']) . 'ai-theme-generator/';
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }

        $safe = sanitize_file_name(preg_replace('/\.zip$/i', '', $name));
        if (!$safe) $safe = 'theme';
        $dest = $dir . $safe . '-' . time() . '.zip';

        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            if (!@copy($file['tmp_name'], $dest)) {
                return new WP_Error('zip_move', 'Failed to move uploaded ZIP to uploads folder.');
            }
        }

        return $dest;
    }
}

/**
 * Ensure theme directory (existing or new). Returns theme directory path.
 */
if ( ! function_exists('ai_tg_get_target_theme_dir') ) {
    function ai_tg_get_target_theme_dir($target_mode, $new_theme, &$created_theme_slug = '') {
        $created_theme_slug = '';

        if ($target_mode === 'existing') {
            return get_stylesheet_directory();
        }

        $name = isset($new_theme['name']) ? sanitize_text_field($new_theme['name']) : '';
        $slug = isset($new_theme['slug']) ? sanitize_title($new_theme['slug']) : '';
        $text_domain = isset($new_theme['text_domain']) ? sanitize_title($new_theme['text_domain']) : '';

        if (!$name) $name = 'AI Generated Theme';
        if (!$slug) $slug = sanitize_title($name);
        if (!$text_domain) $text_domain = $slug;

        $themes_root = get_theme_root();
        $theme_dir = trailingslashit($themes_root) . $slug;

        if (!file_exists($theme_dir)) {
            wp_mkdir_p($theme_dir);
        }

        // style.css required
        $style_css = trailingslashit($theme_dir) . 'style.css';
        if (!file_exists($style_css)) {
            $theme_uri  = isset($new_theme['theme_uri']) ? esc_url_raw($new_theme['theme_uri']) : '';
            $author     = isset($new_theme['author']) ? sanitize_text_field($new_theme['author']) : '';
            $author_uri = isset($new_theme['author_uri']) ? esc_url_raw($new_theme['author_uri']) : '';

            $header = "/*
Theme Name: {$name}
Theme URI: {$theme_uri}
Author: {$author}
Author URI: {$author_uri}
Version: 1.0.0
Text Domain: {$text_domain}
*/\n";
            file_put_contents($style_css, $header);
        }

        // Minimal index.php
        $index_php = trailingslashit($theme_dir) . 'index.php';
        if (!file_exists($index_php)) {
            file_put_contents($index_php, "<?php\nget_header();\n?>\n<div style=\"padding:20px;\">AI Generated Theme</div>\n<?php\nget_footer();\n");
        }

        // Minimal header/footer placeholders (will be overwritten if ZIP contains them)
        $header_php = trailingslashit($theme_dir) . 'header.php';
        if (!file_exists($header_php)) {
            file_put_contents($header_php, "<?php if ( ! defined('ABSPATH') ) exit; ?>\n<!doctype html>\n<html <?php language_attributes(); ?>><head>\n<meta charset=\"<?php bloginfo('charset'); ?>\" />\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />\n<?php wp_head(); ?>\n</head><body <?php body_class(); ?>>\n");
        }

        $footer_php = trailingslashit($theme_dir) . 'footer.php';
        if (!file_exists($footer_php)) {
            file_put_contents($footer_php, "<?php wp_footer(); ?>\n</body></html>");
        }

        $created_theme_slug = $slug;
        return $theme_dir;
    }
}

function ai_tg_is_enqueueable_url($url) {
    $url = trim((string)$url);
    if ($url === '') return false;

    // skip non-enqueueable
    if (preg_match('~^(?:data:|mailto:|tel:|#)~i', $url)) return false;

    // allow:
    // - absolute external: http(s)://
    // - protocol-relative: //
    // - internal relative: /assets/... or assets/...
    return true;
}

function ai_tg_strip_php_header_footer_includes($content) {
    $content = (string) $content;

    $patterns = [
        // include/require with parentheses: include("header.php"); OR include('./header.php')
        '~<\?php\s*(?:include|require|include_once|require_once)\s*\(\s*[\'"](?:\./)?header\.php[\'"]\s*\)\s*;?\s*\?>~i',
        '~<\?php\s*(?:include|require|include_once|require_once)\s*\(\s*[\'"](?:\./)?footer\.php[\'"]\s*\)\s*;?\s*\?>~i',

        // include/require without parentheses: include "header.php"; OR include './header.php'
        '~<\?php\s*(?:include|require|include_once|require_once)\s+[\'"](?:\./)?header\.php[\'"]\s*;?\s*\?>~i',
        '~<\?php\s*(?:include|require|include_once|require_once)\s+[\'"](?:\./)?footer\.php[\'"]\s*;?\s*\?>~i',
    ];

    return preg_replace($patterns, '', $content);
}

