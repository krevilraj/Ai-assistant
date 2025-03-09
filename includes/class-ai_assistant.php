<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    AI_Assistant
 * @subpackage AI_Assistant/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    AI_Assistant
 * @subpackage AI_Assistant/includes
 * @author     Your Name <email@example.com>
 */
class AI_Assistant
{

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct()
    {
        $this->plugin_name = 'ai_assistant';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function define_admin_hooks()
    {
        $plugin_admin = new AI_Assistant_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu'); // Add admin menu

        add_action('admin_bar_menu', array($plugin_admin, 'add_custom_field_link_to_admin_bar'), 100);
        add_action('wp_footer', array($plugin_admin, 'add_custom_field_popup'));
        add_action('wp_footer', array($plugin_admin, 'add_custom_field_js'));
        add_action('admin_footer', array($plugin_admin, 'add_custom_field_popup'));
        add_action('admin_footer', array($plugin_admin, 'add_custom_field_js'));

        add_action('wp_ajax_save_acf_json', [$this, 'save_acf_json']);

        // Register AJAX action
        add_action('wp_ajax_fetch_acf_location_data', [$this, 'fetch_acf_location_data']);
        add_action('wp_ajax_nopriv_fetch_acf_location_data', [$this, 'fetch_acf_location_data']); // Allow non-logged-in users if needed
        add_action('wp_ajax_get_custom_fields_from_url', [$this, 'get_custom_fields_from_url']);
        add_action('wp_ajax_set_homepage', [$this, 'set_homepage']);
        add_action('wp_ajax_reset_permalink', [$this, 'reset_permalink_structure']);
        $this->loader->add_action('wp_ajax_ai_assistant_create_theme', $this, 'ai_assistant_create_theme');
        add_action('wp_ajax_ai_assistant_create_theme', [$this, 'ai_assistant_create_theme']);
        add_action('wp_ajax_ai_assistant_get_theme_details', [$this, 'ai_assistant_get_theme_details']);
        add_action('wp_ajax_ai_assistant_create_page_and_template', [$this, 'ai_assistant_create_page_and_template']);
        add_action('wp_ajax_ai_assistant_create_menu', [$this, 'ai_assistant_create_menu']);
        add_action('wp_ajax_ai_assistant_correct_header', [$this, 'ai_assistant_correct_header']);
        add_action('wp_ajax_ai_assistant_correct_footer', [$this, 'ai_assistant_correct_footer']);
        add_action('wp_ajax_ai_assistant_correct_menu', [$this, 'ai_assistant_detect_and_convert_menu']);
        add_action('wp_ajax_ai_assistant_get_theme_files', [$this, 'ai_assistant_get_theme_files']);
        add_action('wp_ajax_ai_assistant_load_theme_file', [$this, 'ai_assistant_load_theme_file']);
        add_action('wp_ajax_ai_assistant_create_cpt', [$this, 'ai_assistant_create_cpt_handler']);
        add_action('wp_ajax_ai_assistant_delete_file', [$this, 'ai_assistant_delete_file']);
        add_action('wp_ajax_ai_assistant_create_user_type', [$this, 'ai_assistant_create_user_type']);
        add_action('wp_ajax_ai_assistant_delete_user_role', [$this, 'ai_assistant_delete_user_role']);
        add_action('wp_ajax_ai_assistant_save_file', [$this, 'ai_assistant_save_file']);
        add_action('wp_ajax_get_custom_field_groups', [$this, 'get_custom_field_groups']);
        add_action('wp_ajax_get_custom_fields', [$this, 'get_custom_fields']);
        add_action('wp_ajax_save_codemirror_theme', [$this, 'save_codemirror_theme']);
        add_action('admin_body_class', [$this, 'add_dark_theme_body_class']);
    }

    function add_dark_theme_body_class($classes) {
        // ✅ Check if we're on the "ai_assistant-theme-editor" admin page
        if (isset($_GET['page']) && $_GET['page'] === 'ai_assistant-theme-editor') {
            $selected_theme = get_option('ai_assistant_codemirror_theme', 'default');

            // ✅ If the theme is dark, add "ai-dark-theme" to <body> class
            if ($selected_theme === 'dracula') {
                $classes .= ' ai-dark-theme';
            }
        }

        return $classes;
    }


    // ✅ Save CodeMirror theme selection in wp_options
    function save_codemirror_theme() {
        if (isset($_POST['theme'])) {
            update_option('ai_assistant_codemirror_theme', sanitize_text_field($_POST['theme']));
            wp_send_json_success(['message' => 'Theme updated successfully']);
        } else {
            wp_send_json_error(['message' => 'Invalid request']);
        }
    }


    // Function to get custom field groups
    function get_custom_field_groups() {
        $field_groups = acf_get_field_groups(); // Get ACF field groups
        $data = array();

        foreach ($field_groups as $group) {
            $data[] = array(
                'key' => $group['key'],
                'title' => $group['title'],
                'location' => $group['location'][0][0]['param'] // Example location
            );
        }

        wp_send_json_success($data);
    }

// Function to get fields for a specific group
    function get_custom_fields() {
        $group_key = $_POST['group_key'];
        $fields = acf_get_fields($group_key); // Get fields for the group
        $data = array();

        foreach ($fields as $field) {
            $data[] = array(
                'key' => $field['key'],
                'label' => $field['label']
            );
        }

        wp_send_json_success($data);
    }

    private function define_public_hooks()
    {
        $plugin_public = new AI_Assistant_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');


    }

    public function run(){
        $this->loader->run();
    }

    public function get_plugin_name(){
        return $this->plugin_name;
    }

    public function get_version(){
        return $this->version;
    }

    private function load_dependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ai_assistant-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ai_assistant-i18n.php'; // Ensure this is included
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ai_assistant-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-ai_assistant-public.php';

        $this->loader = new AI_Assistant_Loader();
    }

    private function set_locale()
    {
        $plugin_i18n = new AI_Assistant_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    public function save_acf_json(){
        if (!isset($_POST['json_data']) || !isset($_POST['location_data'])) {
            wp_send_json_error("Invalid request.");
            return;
        }

        $json_data = json_decode(stripslashes($_POST['json_data']), true);
        $location_data = json_decode(stripslashes($_POST['location_data']), true);

        if (!$json_data || !$location_data) {
            wp_send_json_error("Invalid JSON format.");
            return;
        }

        // Include the location rules in the JSON data
        $json_data['location'] = $location_data;

        $theme_dir = get_stylesheet_directory() . '/acf-json'; // Save inside the active theme

        // Ensure the directory exists
        if (!file_exists($theme_dir)) {
            mkdir($theme_dir, 0755, true);
        }

        $file_path = $theme_dir . '/' . sanitize_title($json_data['title']) . '.json';

        // Write JSON file
        if (file_put_contents($file_path, json_encode($json_data, JSON_PRETTY_PRINT))) {
            wp_send_json_success("ACF JSON with location rules saved in theme folder!");
        } else {
            wp_send_json_error("Failed to save JSON.");
        }
    }

    public function ai_assistant_save_file() {
        // ✅ Check user capability
        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        // ✅ Validate inputs
        if (empty($_POST['file_path']) || !isset($_POST['file_content'])) {
            wp_send_json_error("Missing file path or content.");
        }

        $relative_path = sanitize_text_field($_POST['file_path']);
        $file_content = stripslashes($_POST['file_content']);

        $theme_dir = realpath(get_stylesheet_directory()); // Theme directory
        $file_path = realpath($theme_dir . '/' . $relative_path); // Resolve absolute path

        // ✅ Security Check: Ensure the file is within the theme directory
        if (!$file_path || strpos($file_path, $theme_dir) !== 0 || !file_exists($file_path)) {
            wp_send_json_error("File not found or access denied.");
        }

        // ✅ Write new content to the file
        if (file_put_contents($file_path, $file_content) === false) {
            wp_send_json_error("Failed to write to file.");
        }

        wp_send_json_success("File updated successfully!");
    }

    function get_acf_location_data(){
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized");
        }

        // Get all post types
        $post_types = get_post_types(['public' => true], 'names');

        // Get all registered taxonomies
        $taxonomies = get_taxonomies(['public' => true], 'names');

        // Get available page templates
        $page_templates = wp_get_theme()->get_page_templates();

        // Send response
        wp_send_json_success([
            'post_types' => array_values($post_types),
            'page_templates' => array_values($page_templates),
            'taxonomies' => array_values($taxonomies),
        ]);
    }

    public function fetch_acf_location_data()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
            return;
        }

        // Get post types
        $post_types = get_post_types(['public' => true], 'names');

        // Get pages
        $pages = get_posts([
            'post_type' => 'page',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);
        $page_list = [];
        foreach ($pages as $page) {
            $page_list[] = ['id' => $page->ID, 'title' => $page->post_title];
        }

        // Get page templates (Fetch correct label names)
        $page_templates = [];
        $templates = wp_get_theme()->get_page_templates();
        foreach ($templates as $template_file => $template_name) {
            $page_templates[] = [
                'file' => $template_file,  // Correct file name (e.g., template-home.php)
                'label' => $template_name   // Correct human-readable label (e.g., "Home Template")
            ];
        }

        // Get taxonomies
        $taxonomies = get_taxonomies(['public' => true], 'names');

        wp_send_json_success([
            'post_types' => array_values($post_types),
            'pages' => $page_list,
            'page_templates' => $page_templates,
            'taxonomies' => array_values($taxonomies),
        ]);
    }

    public function get_custom_fields_from_url()
    {
        if (!isset($_POST['page_url'])) {
            wp_send_json_error("URL not provided.");
            return;
        }

        $url = esc_url_raw($_POST['page_url']);
        $page = get_page_by_path(trim(parse_url($url, PHP_URL_PATH), '/'));

        if (!$page) {
            wp_send_json_error("Page not found.");
            return;
        }

        $page_id = $page->ID;
        $fields_data = [];

        if (function_exists('acf_get_field_groups') && function_exists('acf_get_fields')) {
            $field_groups = acf_get_field_groups(['post_id' => $page_id]);

            foreach ($field_groups as $group) {
                $fields = acf_get_fields($group['key']);
                if ($fields) {
                    foreach ($fields as $field) {
                        $fields_data[] = [
                            'label' => $field['label'],
                            'slug' => $field['name'],
                            'type' => $field['type'], // ✅ Pass 'type' for JS usage
                        ];
                    }
                }
            }
        } else {
            $meta_fields = get_post_meta($page_id);
            foreach ($meta_fields as $key => $value) {
                $fields_data[] = [
                    'label' => $key,
                    'slug' => $key,
                    'type' => 'text' // ✅ Default type
                ];
            }
        }

        wp_send_json_success($fields_data);
    }

    public function set_homepage(){
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
            return;
        }

        if (!isset($_POST['page_id']) || !is_numeric($_POST['page_id'])) {
            wp_send_json_error("Invalid page ID.");
            return;
        }

        $page_id = intval($_POST['page_id']);

        if (get_post_status($page_id) !== 'publish') {
            wp_send_json_error("Page not found or not published.");
            return;
        }

        update_option('show_on_front', 'page');
        update_option('page_on_front', $page_id);

        wp_send_json_success("Homepage set successfully.");
    }

    public function reset_permalink_structure() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
            return;
        }

        // ✅ Update permalink structure to 'post name'
        update_option('permalink_structure', '/%postname%/');

        // ✅ Flush rewrite rules to apply changes immediately
        flush_rewrite_rules();

        // ✅ Clear cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        wp_send_json_success("Permalink structure reset to 'Post name', rewrite rules flushed, and cache cleared.");
    }

    public function ai_assistant_create_theme()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $theme_name = sanitize_text_field($_POST['theme_name']);
        $theme_uri = esc_url_raw($_POST['theme_uri']);
        $author = sanitize_text_field($_POST['author']);
        $author_uri = esc_url_raw($_POST['author_uri']);
        $text_domain = sanitize_text_field($_POST['text_domain']);

        $theme_slug = sanitize_title($theme_name); // Theme slug for folder name
        $theme_dir = get_theme_root() . '/' . $theme_slug;

        if (file_exists($theme_dir)) {
            wp_send_json_error("Theme already exists.");
        }

        if (!mkdir($theme_dir, 0755, true)) {
            wp_send_json_error("Failed to create theme directory.");
        }

        // ✅ Copy files from 'theme_template' folder
        $plugin_template_dir = plugin_dir_path(__FILE__) . '../theme_template/';
        $files_to_copy = ['header.php', 'footer.php', 'functions.php', 'template-home.php'];

        foreach ($files_to_copy as $file) {
            $source = $plugin_template_dir . $file;
            $destination = $theme_dir . '/' . $file;

            if (!copy($source, $destination)) {
                wp_send_json_error("Failed to copy file: {$file}");
            }
        }

        // ✅ Append custom function to functions.php
        $functions_php = $theme_dir . '/functions.php';
        $append_content = <<<EOT

// ✅ Custom menu configuration by {$theme_name}
function {$text_domain}_config() {

    // This theme uses wp_nav_menu() in two locations.
    register_nav_menus(
        array(
            '{$text_domain}_main_menu'   => '{$theme_name} Main Menu',
            '{$text_domain}_footer_menu' => '{$theme_name} Footer Menu',
        )
    );

}

add_action('after_setup_theme', '{$text_domain}_config', 0);

EOT;

        if (file_put_contents($functions_php, $append_content, FILE_APPEND | LOCK_EX) === false) {
            wp_send_json_error("Failed to append custom menu configuration in functions.php.");
        }

        // ✅ Create index.php
        file_put_contents($theme_dir . '/index.php', "<?php\n// Silence is golden.\n");

        // ✅ Create blank screenshot.png
        $image = imagecreatetruecolor(1200, 900);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        imagepng($image, $theme_dir . '/screenshot.png');
        imagedestroy($image);

        // ✅ Create style.css
        $style_content = "/*
Theme Name: {$theme_name}
Theme URI: {$theme_uri}
Author: {$author}
Author URI: {$author_uri}
Version: 1.0
Text-domain: {$text_domain}
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: e-commerce, custom-menu, custom-logo, featured-images, footer-widgets, theme-options, translation-ready, blog, right-sidebar, sticky-post, threaded-comments
*/";
        file_put_contents($theme_dir . '/style.css', $style_content);

        // ✅ Save values in the database
        update_option('ai_assistant_theme_name', $theme_name);
        update_option('ai_assistant_theme_uri', $theme_uri);
        update_option('ai_assistant_author', $author);
        update_option('ai_assistant_author_uri', $author_uri);
        update_option('ai_assistant_text_domain', $text_domain);

        // 🚀 ✅ Activate the theme
        switch_theme($theme_slug);

        wp_send_json_success("Theme '{$theme_name}' created, files copied, menus configured, and activated successfully.");
    }

    // ✅ Fetch theme details for display
    public function ai_assistant_get_theme_details() {
        wp_send_json_success([
            'theme_name' => get_option('ai_assistant_theme_name', ''),
            'theme_uri' => get_option('ai_assistant_theme_uri', ''),
            'author' => get_option('ai_assistant_author', ''),
            'author_uri' => get_option('ai_assistant_author_uri', ''),
            'text_domain' => get_option('ai_assistant_text_domain', '')
        ]);
    }

    public function ai_assistant_create_page_and_template() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $page_name = sanitize_text_field($_POST['page_name']);
        $create_template = isset($_POST['create_template']) ? boolval($_POST['create_template']) : false;
        $theme_dir = get_stylesheet_directory();
        $template_slug = 'template-'.sanitize_title($page_name) . '.php';

        // ✅ Create template file if checkbox is checked
        if ($create_template) {
            $template_content = "<?php
/*
Template Name: {$page_name}
*/
get_header(); ?>

<!-- Page Content -->
<h1>{$page_name}</h1>

<?php get_footer(); ?>";

            $template_path = $theme_dir . '/' . $template_slug;
            if (file_put_contents($template_path, $template_content) === false) {
                wp_send_json_error("Failed to create template file.");
            }
        }

        // ✅ Create WordPress page
        $page_id = wp_insert_post([
            'post_title'   => $page_name,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'page_template'=> $create_template ? $template_slug : ''
        ]);

        if (is_wp_error($page_id) || !$page_id) {
            wp_send_json_error("Failed to create page.");
        }

        $message = "Page '{$page_name}' created successfully.";
        $message .= $create_template ? " Template attached: {$template_slug}" : "";

        wp_send_json_success($message);
    }

    //create menu and respond menu id
    public function ai_assistant_create_menu() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $menu_name = sanitize_text_field($_POST['menu_name']);
        $menu_exists = wp_get_nav_menu_object($menu_name);

        if (!$menu_exists) {
            $menu_id = wp_create_nav_menu($menu_name);

            if (is_wp_error($menu_id)) {
                wp_send_json_error("Failed to create menu.");
            }

            wp_send_json_success(['menu_id' => $menu_id]);
        } else {
            wp_send_json_error("Menu already exists.");
        }
    }
    // correct header
    public function ai_assistant_correct_header() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $header_content = stripslashes($_POST['header_content']);
        if (empty($header_content)) {
            wp_send_json_error("No header content received.");
        }

        // ✅ 1. Get text-domain from options
        // First, check if the text-domain is stored in options
        $text_domain = get_option('ai_assistant_text_domain', '');

        // If it's empty, fetch it from the current theme
        if (empty($text_domain)) {
            $theme = wp_get_theme();
            $text_domain = $theme->get('TextDomain'); // Get the text-domain from style.css

            // If no TextDomain is found, fall back to 'ai_assistant'
            if (empty($text_domain)) {
                $text_domain = 'ai_assistant';
            }
        }


        // ✅ 2. Extract all CSS links
        preg_match_all('/<link[^>]+href=["\']([^"\']+)["\']/i', $header_content, $matches);

        $theme_dir = get_stylesheet_directory();
        $functions_path = $theme_dir . '/functions.php';
        $header_path = $theme_dir . '/header.php';


        // ✅ Read existing content of functions.php
        $functions_content = file_exists($functions_path) ? file_get_contents($functions_path) : "";

        // ✅ Define the function name
        $enqueue_function_name = "{$text_domain}_enqueue_styles";

        // ✅ Check if the function already exists in functions.php
        if (strpos($functions_content, "function {$enqueue_function_name}()") !== false) {
            // 🔹 Function exists: Add new styles inside the function before closing }

            // Generate new styles
            $new_enqueue_code = "";
            foreach ($matches[1] as $url) {
                $handle = sanitize_title(basename($url, '.css')) . '-css';
                $new_enqueue_code .= (strpos($url, 'http') === 0)
                    ? "    wp_enqueue_style('{$handle}', '{$url}');\n"
                    : "    wp_enqueue_style('{$handle}', get_template_directory_uri() . '/{$url}', array(), null, 'all');\n";
            }

            // ✅ Find and insert new styles inside the function before closing }
            $pattern = "/(function {$enqueue_function_name}\(\) \{.*?)(\n\})/s";

            if (preg_match($pattern, $functions_content, $matches)) {
                // Insert new styles before the last closing }
                $updated_function = str_replace($matches[2], "\n" . $new_enqueue_code . $matches[2], $matches[0]);
                $functions_content = str_replace($matches[0], $updated_function, $functions_content);
            }

            // ✅ Overwrite functions.php with the updated content
            if (file_put_contents($functions_path, $functions_content) === false) {
                wp_send_json_error("Failed to update functions.php.");
            }
        } else {
            // 🔹 Function does NOT exist: Append the new function at the bottom of functions.php

            $enqueue_code = "\n// 🔗 Enqueued styles from header\nfunction {$enqueue_function_name}() {\n";

            foreach ($matches[1] as $url) {
                $handle = sanitize_title(basename($url, '.css')) . '-css';
                $enqueue_code .= (strpos($url, 'http') === 0)
                    ? "    wp_enqueue_style('{$handle}', '{$url}');\n"
                    : "    wp_enqueue_style('{$handle}', get_template_directory_uri() . '/{$url}', array(), null, 'all');\n";
            }

            $enqueue_code .= "    wp_enqueue_style('{$text_domain}-style', get_stylesheet_uri(), array(), filemtime(get_template_directory() . '/style.css'), 'all');\n";
            $enqueue_code .= "}\nadd_action('wp_enqueue_scripts', '{$enqueue_function_name}');\n";

            // ✅ Append the function at the bottom of functions.php
            if (file_put_contents($functions_path, $enqueue_code, FILE_APPEND) === false) {
                wp_send_json_error("Failed to append new function.");
            }
        }

        // ✅ 5. Replace <a href="index.html|index.php"> with home_url()
        $header_content = preg_replace(
            '/<a([^>]+)href=["\']([^"\']*(index\.html|index\.php))["\']/i',
            '<a$1href="<?php echo home_url(); ?>"',
            $header_content
        );

        // ✅ 6. Replace internal <img src=""> with bloginfo('template_url')
        $header_content = preg_replace_callback(
            '/<img([^>]+)src=["\']([^"\':]+)["\']/i',
            function ($matches) {
                $src = $matches[2];
                return '<img' . $matches[1] . 'src="<?php bloginfo(\'template_url\'); ?>/' . ltrim($src, '/') . '"';
            },
            $header_content
        );


        // ✅ 7. Extract class from <body> if it exists
        $body_class = '';
        if (preg_match('/<body[^>]*class=["\']([^"\']+)["\']/i', $header_content, $matches)) {
            $body_class = trim($matches[1]); // Extract existing classes
        }

        // ✅ 8. Modify `body_class();` in `header.php` if class exists
        if (!empty($body_class)) {
            $header_php_content = file_get_contents($header_path);

            // ✅ Check if `body_class();` exists in header.php
            if (strpos($header_php_content, 'body_class()') !== false) {
                // Append extracted class inside `body_class()`
                $header_php_content = preg_replace(
                    '/body_class\(\)/',
                    'body_class("' . esc_attr($body_class) . '")',
                    $header_php_content
                );
            } else {
                // If `body_class();` is missing, insert the full <body> tag with class
                $header_php_content = preg_replace(
                    '/<body([^>]*)>/i',
                    '<body$1 <?php body_class("' . esc_attr($body_class) . '"); ?>>',
                    $header_php_content
                );
            }

            // ✅ Save updated header.php with merged class
            file_put_contents($header_path, $header_php_content);
        }

        // ✅ 9. Extract content inside <body> and append to header.php
        if (preg_match('/<body[^>]*>(.*)$/is', $header_content, $body_content)) {
            $body_html = trim($body_content[1]);

            if (!empty(trim($body_html))) {
                if (file_put_contents($header_path, "\n<!-- Content from header correction -->\n{$body_html}\n", FILE_APPEND) === false) {
                    wp_send_json_error("Failed to update header.php.");
                } else {
                    wp_send_json_success("Header processed, class preserved, and content appended successfully.");
                }
            } else {
                wp_send_json_error("Extracted <body> content is empty.");
            }
        } else {
            wp_send_json_error("No valid <body> content found in header.");
        }

    }
    // correct footer
    public function ai_assistant_correct_footer() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $footer_content = stripslashes($_POST['footer_content']);
        if (empty($footer_content)) {
            wp_send_json_error("No footer content received.");
        }

        // ✅ 1. Get text-domain from options
        $text_domain = get_option('ai_assistant_text_domain', 'ai_assistant');

        $theme_dir = get_stylesheet_directory();
        $functions_path = $theme_dir . '/functions.php';
        $footer_path = $theme_dir . '/footer.php';

        // ✅ 2. Extract all JS links
        preg_match_all('/<script[^>]+src=["\']([^"\']+)["\']/i', $footer_content, $matches);

        // ✅ 3. Generate wp_enqueue_script() calls
        $enqueue_function_name = "{$text_domain}_enqueue_scripts";

        // ✅ Read functions.php content
        $functions_content = file_exists($functions_path) ? file_get_contents($functions_path) : "";

        if (strpos($functions_content, "function {$enqueue_function_name}()") !== false) {
            // 🔹 Function exists: Add new scripts inside the function before closing }
            $new_enqueue_code = "";
            foreach ($matches[1] as $url) {
                $handle = sanitize_title(basename($url, '.js')) . '-js';
                $new_enqueue_code .= (strpos($url, 'http') === 0)
                    ? "    wp_enqueue_script('{$handle}', '{$url}', array('jquery'), null, true);\n"
                    : "    wp_enqueue_script('{$handle}', get_template_directory_uri() . '/{$url}', array('jquery'), null, true);\n";
            }

            // ✅ Find and insert new scripts inside the function before closing }
            $pattern = "/(function {$enqueue_function_name}\(\) \{.*?)(\n\})/s";

            if (preg_match($pattern, $functions_content, $matches)) {
                // Insert new scripts before the last closing }
                $updated_function = str_replace($matches[2], "\n" . $new_enqueue_code . $matches[2], $matches[0]);
                $functions_content = str_replace($matches[0], $updated_function, $functions_content);
            }

            // ✅ Overwrite functions.php with the updated content
            if (file_put_contents($functions_path, $functions_content) === false) {
                wp_send_json_error("Failed to update functions.php.");
            }

        } else {
            // 🔹 Function does NOT exist: Append the new function at the bottom of functions.php
            $enqueue_code = "\n// 🚀 Enqueued scripts from footer\nfunction {$enqueue_function_name}() {\n";

            foreach ($matches[1] as $url) {
                $handle = sanitize_title(basename($url, '.js')) . '-js';
                $enqueue_code .= (strpos($url, 'http') === 0)
                    ? "    wp_enqueue_script('{$handle}', '{$url}', array('jquery'), null, true);\n"
                    : "    wp_enqueue_script('{$handle}', get_template_directory_uri() . '/{$url}', array('jquery'), null, true);\n";
            }

            $enqueue_code .= "}\nadd_action('wp_enqueue_scripts', '{$enqueue_function_name}');\n";

            // ✅ Append the function at the bottom of functions.php
            if (file_put_contents($functions_path, $enqueue_code, FILE_APPEND) === false) {
                wp_send_json_error("Failed to append new function.");
            }
        }

        // ✅ 5. Remove <script> tags, </body>, </html>, and comments
        $footer_content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $footer_content);
        $footer_content = preg_replace('/<\/body>|<\/html>/i', '', $footer_content);
        $footer_content = preg_replace('/<!--(.*?)-->/s', '', $footer_content);

        // ✅ 6. Replace <a> tags linking to index.html/index.php with home_url()
        $footer_content = preg_replace(
            '/<a([^>]+)href=["\']([^"\']*(index\.html|index\.php))["\']/i',
            '<a$1href="<?php echo home_url(); ?>"',
            $footer_content
        );

        // ✅ 7. Replace internal <img src="">
        $footer_content = preg_replace_callback(
            '/<img([^>]+)src=["\']([^"\':]+)["\']/i',
            function ($matches) {
                $src = $matches[2];
                return '<img' . $matches[1] . 'src="<?php bloginfo(\'template_url\'); ?>/' . ltrim($src, '/') . '"';
            },
            $footer_content
        );

        // ✅ 8. Remove empty lines and trim extra whitespace
        $footer_content = preg_replace('/^\h*\v+/m', '', $footer_content);  // Remove empty lines
        $footer_content = trim($footer_content);                           // Trim leading/trailing whitespace

        // ✅ 9. Prepend processed content to footer.php
        $existing_footer = file_exists($footer_path) ? file_get_contents($footer_path) : '';
        $new_footer_content = $footer_content . "\n" . $existing_footer;
        if (file_put_contents($footer_path, $new_footer_content) === false) {
            wp_send_json_error("Failed to update footer.php.");
        }

        wp_send_json_success("Footer processed, scripts enqueued, empty lines removed, and content prepended successfully.");
    }
    //correct menu

    public function ai_assistant_detect_and_convert_menu() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $menu_html = stripslashes($_POST['menu_html']);
        if (empty($menu_html)) {
            wp_send_json_error("❌ Menu HTML content missing.");
        }

        $menu_name = sanitize_text_field($_POST['menu_name']);
        if (empty($menu_name)) {
            wp_send_json_error("❌ Menu name missing.");
        }

        $theme_dir = get_stylesheet_directory();
        $menu_folder = $theme_dir . '/menu';
        $navwalker_file = $menu_folder . '/bootstrap-navwalker.php';
        $functions_file = $theme_dir . '/functions.php';

        // ✅ Extract classes from <nav> and <ul>
        preg_match('/<nav[^>]*class=["\']([^"\']+)["\']/', $menu_html, $nav_classes);
        preg_match('/<ul[^>]*class=["\']([^"\']+)["\']/', $menu_html, $ul_classes);
        $nav_class = isset($nav_classes[1]) ? esc_attr($nav_classes[1]) : 'navbar navbar-expand-lg navbar-light bg-light';
        $menu_class = isset($ul_classes[1]) ? esc_attr($ul_classes[1]) : 'navbar-nav';

        // ✅ Extract menu items
        preg_match_all('/<li[^>]*>\s*<a[^>]*href=["\']([^"\']+)["\']>(.*?)<\/a>\s*<\/li>/i', $menu_html, $menu_items, PREG_SET_ORDER);

        // ✅ Check if menu exists
        $menu_exists = wp_get_nav_menu_object($menu_name);
        if (!$menu_exists) {
            // ✅ Create the menu if not exists
            $menu_id = wp_create_nav_menu($menu_name);
            if (is_wp_error($menu_id)) {
                wp_send_json_error("❌ Failed to create menu.");
            }

            // ✅ Add menu items dynamically
            foreach ($menu_items as $item) {
                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title' => esc_html($item[2]),
                    'menu-item-url' => esc_url($item[1]),
                    'menu-item-status' => 'publish',
                ]);
            }

            // ✅ Register the menu location dynamically
            register_nav_menus([
                sanitize_title($menu_name) => esc_html($menu_name),
            ]);
        }

        // ✅ Handle Bootstrap Menu
        if (strpos($menu_html, 'navbar') !== false || strpos($menu_html, 'dropdown') !== false) {
            // ✅ 1. Ensure "menu" folder exists
            if (!file_exists($menu_folder)) {
                mkdir($menu_folder, 0755, true);
            }

            // ✅ 2. Ensure "bootstrap-navwalker.php" exists
            if (!file_exists($navwalker_file)) {
                $navwalker_code = "<?php
class WP_Bootstrap_Navwalker extends Walker_Nav_Menu {
    function start_lvl( &\$output, \$depth = 0, \$args = null ) {
        \$output .= \"<ul class='dropdown-menu'>\";
    }

    function start_el( &\$output, \$item, \$depth = 0, \$args = null, \$id = 0 ) {
        \$classes = empty(\$item->classes) ? [] : (array) \$item->classes;
        \$class_names = join(' ', apply_filters('nav_menu_css_class', array_filter(\$classes), \$item, \$args));
        \$output .= \"<li class='nav-item \". esc_attr(\$class_names) . \"'>\";

        \$attributes = !empty(\$item->url) ? ' href=\"' . esc_attr(\$item->url) . '\"' : '';
        \$item_output = \"<a class='nav-link'\" . \$attributes . \">\" . apply_filters('the_title', \$item->title, \$item->ID) . \"</a>\";
        \$output .= \$item_output;
    }

    function end_lvl( &\$output, \$depth = 0, \$args = null ) {
        \$output .= \"</ul>\";
    }

    function end_el( &\$output, \$item, \$depth = 0, \$args = null ) {
        \$output .= \"</li>\";
    }
}";
                file_put_contents($navwalker_file, $navwalker_code);
            }

            // ✅ 3. Include "bootstrap-navwalker.php" in functions.php
            $functions_content = file_get_contents($functions_file);
            $include_code = "require_once get_template_directory() . '/menu/bootstrap-navwalker.php';";

            if (strpos($functions_content, "bootstrap-navwalker.php") === false) {
                $functions_content = preg_replace('/<\?php\s*/', "<?php\n" . $include_code . "\n", $functions_content, 1);
                file_put_contents($functions_file, $functions_content);
            }

            // ✅ 4. Generate Bootstrap `wp_nav_menu()`
            $menu_code = "<?php wp_nav_menu([
            'menu' => '{$menu_name}',
            'menu_class' => '{$menu_class}',
            'container' => 'nav',
            'container_class' => '{$nav_class}',
            'walker' => new WP_Bootstrap_Navwalker()
        ]); ?>";

            wp_send_json_success([
                'menu_code' => $menu_code,
                'message'   => "✅ Bootstrap menu converted successfully!"
            ]);
        }

        // ✅ Handle Custom-Styled Menu
        elseif (preg_match('/class=["\'].*?(menu-item|menu-link|dropdown-list).*?["\']/', $menu_html)) {
            $menu_code = "<?php wp_nav_menu([
            'menu' => '{$menu_name}',
            'menu_class' => '{$menu_class}',
            'container' => false
        ]); ?>";

            wp_send_json_success([
                'menu_code' => $menu_code,
                'message'   => "✅ Custom menu converted successfully!"
            ]);
        }

        // ✅ Handle Default Basic Menu
        else {
            $menu_code = "<?php wp_nav_menu([
            'menu' => '{$menu_name}',
            'menu_class' => '{$menu_class}',
            'container' => false
        ]); ?>";

            wp_send_json_success([
                'menu_code' => $menu_code,
                'message'   => "✅ Basic menu converted successfully!"
            ]);
        }
    }





    // ✅ Fetch all theme files
    public function ai_assistant_get_theme_files() {
        $theme_dir = get_stylesheet_directory();
        $files = array_diff(scandir($theme_dir), array('.', '..'));
        wp_send_json_success(['files' => array_values($files)]);
    }
    // Delete files and folder
    function ai_assistant_delete_file() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("❌ Unauthorized access.");
        }

        $theme_dir = get_stylesheet_directory();
        $file_path = sanitize_text_field($_POST['file_path']);
        $full_path = $theme_dir . '/' . $file_path;

        if (!file_exists($full_path)) {
            wp_send_json_error("❌ File or folder not found.");
        }

        // 🗑 Delete folder or file
        if (is_dir($full_path)) {
            delete_folder($full_path);
        } else {
            unlink($full_path);
        }
        wp_send_json_success("✅ Successfully deleted: " . basename($file_path));
    }
    // Recursive folder deletion
    function delete_folder($folder) {
        foreach (glob($folder . '/*') as $file) {
            if (is_dir($file)) {
                delete_folder($file);
            } else {
                unlink($file);
            }
        }
        rmdir($folder);
    }
    // ✅ Load file content for editor
    public function ai_assistant_load_theme_file() {
        if (!isset($_POST['filename'])) wp_send_json_error('Filename missing.');

        $theme_dir = get_stylesheet_directory();
        $file_path = $theme_dir . '/' . sanitize_file_name($_POST['filename']);

        if (!file_exists($file_path)) wp_send_json_error('File not found.');
        $content = file_get_contents($file_path);
        wp_send_json_success(['content' => $content]);
    }
    // create cpt
    function ai_assistant_create_cpt_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $cpt_slug = sanitize_title($_POST['cpt_slug']);
        $plural_label = sanitize_text_field($_POST['plural_label']);
        $singular_label = sanitize_text_field($_POST['singular_label']);
        $no_of_posts = isset($_POST['no_of_posts']) ? intval($_POST['no_of_posts']) : 2; // Default to 2
        $dashi_icon = sanitize_text_field($_POST['dashi_icon']);
        $supports = isset($_POST['supports']) ? $_POST['supports'] : [];

        $create_template = $_POST['create_template'];
        $create_archive_template = $_POST['create_archive_template'];

        if (empty($cpt_slug) || empty($plural_label) || empty($singular_label)) {
            wp_send_json_error("❌ Required fields are missing.");
        }

        $functions_path = get_stylesheet_directory() . '/functions.php';
        $cpt_code = "\n// 🎯 Custom Post Type: {$cpt_slug}\n";
        $cpt_code .= "function register_{$cpt_slug}_cpt() {\n";
        $cpt_code .= "    \$labels = [\n";
        $cpt_code .= "        'name' => __('{$plural_label}', '{$cpt_slug}'),\n";
        $cpt_code .= "        'singular_name' => __('{$singular_label}', '{$cpt_slug}'),\n";
        $cpt_code .= "    ];\n";
        $cpt_code .= "    \$args = [\n";
        $cpt_code .= "        'label' => __('{$plural_label}', '{$cpt_slug}'),\n";
        $cpt_code .= "        'public' => true,\n";
        $cpt_code .= "        'menu_icon' => '{$dashi_icon}',\n";
        $cpt_code .= "        'supports' => " . var_export($supports, true) . ",\n";
        $cpt_code .= "        'has_archive' => true,\n";
        $cpt_code .= "        'show_in_rest' => true,\n";
        $cpt_code .= "        'query_var' => true,\n";
        $cpt_code .= "        'rewrite' => ['slug' => '{$cpt_slug}']\n";
        $cpt_code .= "    ];\n";
        $cpt_code .= "    register_post_type('{$cpt_slug}', \$args);\n";
        $cpt_code .= "}\nadd_action('init', 'register_{$cpt_slug}_cpt');\n";

        // Append Pagination Query Modifier
        $cpt_code .= "\n// Modify Archive Query for '{$cpt_slug}' CPT\n";
        $cpt_code .= "function modify_{$cpt_slug}_archive_query(\$query) {\n";
        $cpt_code .= "    if (!is_admin() && \$query->is_main_query() && is_post_type_archive('{$cpt_slug}')) {\n";
        $cpt_code .= "        \$query->set('posts_per_page', {$no_of_posts});\n";
        $cpt_code .= "    }\n";
        $cpt_code .= "}\nadd_action('pre_get_posts', 'modify_{$cpt_slug}_archive_query');\n";

        if (file_put_contents($functions_path, $cpt_code, FILE_APPEND) === false) {
            wp_send_json_error("❌ Failed to register the CPT in functions.php.");
        }

        // Create Single Template
        if ($create_template == 1) {
            $template_file = get_stylesheet_directory() . "/single-{$cpt_slug}.php";
            $template_content = "<?php\n// Single Template for '{$cpt_slug}' CPT\nget_header();\n?>\n\n<div class='container'>\n    <?php\n    if (have_posts()) :\n        while (have_posts()) : the_post();\n            echo '<h1>' . get_the_title() . '</h1>';\n            the_content();\n        endwhile;\n    endif;\n    ?>\n</div>\n\n<?php get_footer(); ?>";
            file_put_contents($template_file, $template_content);
        }

        // Create Archive Template with Custom Pagination
        if ($create_archive_template == 1) {
            $archive_file = get_stylesheet_directory() . "/archive-{$cpt_slug}.php";
            $archive_content = <<<PHP
<?php
// Archive Template for '{$cpt_slug}' CPT
get_header(); ?>

<div class="container">
    <h1><?php post_type_archive_title(); ?></h1>

    <?php if (have_posts()) : ?>
        <div class="post-list">
            <?php while (have_posts()) : the_post(); ?>
                <div class="post-item">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <p><?php the_excerpt(); ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php
            global \$wp_query;
            echo paginate_links([
                'total'     => \$wp_query->max_num_pages,
                'current'   => max(1, get_query_var('paged')),
                'prev_text' => '&laquo; Previous',
                'next_text' => 'Next &raquo;',
            ]);
            ?>
        </div>

    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
PHP;

            file_put_contents($archive_file, $archive_content);
        }


        wp_send_json_success("🎉 Custom Post Type '{$plural_label}' created successfully" . ($create_template ? " with single template!" : "") . ($create_archive_template ? " and archive template!" : ""));
    }

    // Create user type
    function ai_assistant_create_user_type() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("❌ Unauthorized access.");
        }

        $user_type = sanitize_key($_POST['user_type']);
        $user_role = sanitize_key($_POST['user_role']);

        if (empty($user_type) || empty($user_role)) {
            wp_send_json_error("❌ User type and role are required.");
        }

        if (get_role($user_type)) {
            wp_send_json_error("❌ User type '{$user_type}' already exists.");
        }

        // Create the new user role
        $role = add_role(
            $user_type, // Role slug
            ucfirst($user_type), // Display name
            get_role($user_role)->capabilities // Copy capabilities from selected role
        );

        if ($role !== null) {
            wp_send_json_success("✅ User type '{$user_type}' created with '{$user_role}' role.");
        } else {
            wp_send_json_error("❌ Failed to create user type '{$user_type}'.");
        }
    }

    function ai_assistant_delete_user_role() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("❌ Unauthorized access.");
        }

        $role = sanitize_key($_POST['role']);

        if (empty($role)) {
            wp_send_json_error("❌ Role slug is missing.");
        }

        if (!get_role($role)) {
            wp_send_json_error("⚠️ Role '{$role}' does not exist.");
        }

        remove_role($role);

        if (!get_role($role)) {
            wp_send_json_success("✅ Role '{$role}' has been deleted.");
        } else {
            wp_send_json_error("❌ Failed to delete role '{$role}'.");
        }
    }


}

if (!function_exists('ai_assistant_render_spark_button')) {
    /**
     * Render the spark effect button globally with dynamic data-action.
     *
     * @param string $action_value The value for the data-action attribute.
     * @since 1.0.0
     */
    function ai_assistant_render_spark_button($action_value = '')
    {
        do_action('ai_assistant_before_spark_button');
        include plugin_dir_path(dirname(__FILE__)) . 'admin/partials/ai-assistant-button.php';
        do_action('ai_assistant_after_spark_button');
    }
}



