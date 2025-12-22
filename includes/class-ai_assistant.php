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

        // 🔥 init Live CSS REST routes
        if ( class_exists( 'AI_Assistant_Live_CSS' ) ) {
            AI_Assistant_Live_CSS::init();
        }

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
        add_action('wp_ajax_toggle_wp_debug', [$this, 'ai_assistant_toggle_wp_debug']);
        $this->loader->add_action('wp_ajax_ai_assistant_create_theme', $this, 'ai_assistant_create_theme');
        add_action('wp_ajax_ai_assistant_create_theme', [$this, 'ai_assistant_create_theme']);
        add_action('wp_ajax_ai_assistant_get_theme_details', [$this, 'ai_assistant_get_theme_details']);
        add_action('wp_ajax_ai_assistant_create_page_and_template', [$this, 'ai_assistant_create_page_and_template']);
        add_action('wp_ajax_ai_assistant_create_menu', [$this, 'ai_assistant_create_menu']);
        add_action('wp_ajax_ai_assistant_correct_header', [$this, 'ai_assistant_correct_header']);
        add_action('wp_ajax_ai_assistant_correct_footer', [$this, 'ai_assistant_correct_footer']);
        add_action('wp_ajax_ai_assistant_correct_page', [$this, 'ai_assistant_correct_page']);
        add_action('wp_ajax_ai_assistant_correct_menu', [$this, 'ai_assistant_detect_and_convert_menu']);
        add_action('wp_ajax_ai_assistant_get_theme_files', [$this, 'ai_assistant_get_theme_files']);
        add_action('wp_ajax_ai_assistant_load_theme_file', [$this, 'ai_assistant_load_theme_file']);
        add_action('wp_ajax_ai_assistant_create_cpt', [$this, 'ai_assistant_create_cpt_handler']);
        add_action('wp_ajax_create_custom_post_type_page', [$this, 'ai_assistant_create_cpt_pages_handler']);
        add_action('wp_ajax_ai_assistant_delete_file', [$this, 'ai_assistant_delete_file']);
        add_action('wp_ajax_ai_assistant_create_user_type', [$this, 'ai_assistant_create_user_type']);
        add_action('wp_ajax_ai_assistant_delete_user_role', [$this, 'ai_assistant_delete_user_role']);
        add_action('wp_ajax_ai_assistant_save_file', [$this, 'ai_assistant_save_file']);
        add_action('wp_ajax_ai_assistant_change_admin_email', [$this, 'ai_assistant_change_admin_email_callback']);
        add_action('wp_ajax_ai_assistant_change_user_language', [$this, 'ai_assistant_change_user_language_callback']);
        add_action('wp_ajax_update_cf7_translated_messages', [$this, 'ai_assistant_update_cf7_translated_messages']);

        add_action('wp_ajax_get_custom_field_groups', [$this, 'get_custom_field_groups']);
        add_action('wp_ajax_get_custom_fields', [$this, 'get_custom_fields']);
        add_action('wp_ajax_save_codemirror_theme', [$this, 'save_codemirror_theme']);
        add_action('admin_body_class', [$this, 'add_dark_theme_body_class']);
        add_action('wp_ajax_ai_assistant_create_template_part', [$this, 'ai_assistant_create_template_part']);
        add_action('wp_ajax_ai_assistant_search_pages', [$this, 'ai_assistant_search_pages_callback']);


        add_action('wp_ajax_get_pages_list', [$this, 'ai_assistant_handle_get_pages_list']);
        add_action('admin_enqueue_scripts', [$this, 'ai_assistant_enqueue_pages_list_assets']);

        add_action('wp_ajax_upload_theme_image', [$this, 'upload_theme_image']);
        add_action('wp_ajax_save_customizer_code', [$this, 'save_customizer_code']);

        // Create file / folder in theme
        add_action('wp_ajax_ai_assistant_create_file', [$this, 'ai_assistant_create_file']);
        add_action('wp_ajax_ai_assistant_create_folder', [$this, 'ai_assistant_create_folder']);

        // admin bar to edit template php file
        add_action( 'admin_bar_menu', [ $this, 'ai_assistant_add_admin_bar_link' ], 90 );

        //change the visit store and visit site to open in different tab
        add_action( 'admin_bar_menu', [ $this, 'ai_assistant_admin_bar_open_new_tab' ], 999 );
        add_action( 'wp_ajax_ai_assistant_get_meta_json', [ $this, 'ai_assistant_get_meta_json' ] );
        add_action( 'wp_ajax_ai_assistant_update_meta_from_json', [ $this, 'ai_assistant_update_meta_from_json' ] );
        add_action( 'admin_head', [ $this, 'ai_assistant_imp_style' ] );


        // create new theme
        add_action('admin_post_ai_tg_upload_zip', [$this, 'handle_theme_zip_upload']);
        add_action('admin_post_ai_tg_create_pages', [$this, 'handle_create_pages']);





    }



    function save_customizer_code()
    {
        if (!isset($_POST['customizer_code'])) {
            wp_send_json_error("Missing data!");
        }

        $code = stripslashes($_POST['customizer_code']); // ✅ Remove extra escaping

        // ✅ Extract the section name from the code
        preg_match("/add_section\('([^']+)'/", $code, $matches);
        if (!isset($matches[1])) {
            wp_send_json_error("Invalid section name.");
        }

        $sectionSlug = sanitize_title($matches[1]); // ✅ Convert to a safe filename
        $themeDir = get_template_directory();
        $customizerDir = $themeDir . "/customizer/";

        // ✅ Ensure the customizer directory exists
        if (!file_exists($customizerDir)) {
            mkdir($customizerDir, 0755, true);
        }

        // ✅ Create a unique function name
        $functionName = "theme_customizer_" . $sectionSlug . "_settings";

        // ✅ Define the full file path
        $filePath = $customizerDir . "{$sectionSlug}.php";

        // ✅ Write the Customizer function into the separate file
        $customizerFunction = "<?php
function {$functionName}(\$wp_customize) {
{$code}
}
add_action('customize_register', '{$functionName}');
";

        file_put_contents($filePath, $customizerFunction);

        // ✅ Ensure the file is included in `functions.php`
        $functionsPath = $themeDir . "/functions.php";
        $includeCode = "include_once get_template_directory() . '/customizer/{$sectionSlug}.php';\n";

        // ✅ Insert the include statement at the top of functions.php
        $functionsContent = file_get_contents($functionsPath);
        if (strpos($functionsContent, $includeCode) === false) {
            $functionsContent = preg_replace("/<\?php\s*/", "<?php\n" . $includeCode, $functionsContent, 1);
            file_put_contents($functionsPath, $functionsContent);
        }

        wp_send_json_success("Customizer settings saved in `/customizer/{$sectionSlug}.php` and included in `functions.php`.");
    }



    function upload_theme_image()
    {
        if (!isset($_POST['image_path'])) {
            wp_send_json_error("No image path provided.");
            return;
        }

        $image_path = sanitize_text_field($_POST['image_path']);
        $theme_path = get_stylesheet_directory(); // Base theme directory

        // ✅ Remove leading slashes to normalize path
        $relative_path = ltrim($image_path, '/');
        $full_path = $theme_path . '/' . $relative_path;

        // ✅ Ensure file exists
        if (!file_exists($full_path)) {
            wp_send_json_error("Image not found in theme folder.");
            return;
        }

        // ✅ Prepare image for upload
        $file_array = array(
            'name' => basename($full_path),
            'tmp_name' => $full_path
        );

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // ✅ Upload the file to WordPress media library
        $attachment_id = media_handle_sideload($file_array, 0);

        if (is_wp_error($attachment_id)) {
            wp_send_json_error("Image upload failed.");
            return;
        }

        $image_url = wp_get_attachment_url($attachment_id);

        // ✅ Return success with the new image ID and URL
        wp_send_json_success(array(
            'image_id' => $attachment_id,
            'image_url' => $image_url
        ));
    }

    function add_dark_theme_body_class($classes)
    {
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
    function save_codemirror_theme()
    {
        if (isset($_POST['theme'])) {
            update_option('ai_assistant_codemirror_theme', sanitize_text_field($_POST['theme']));
            wp_send_json_success(['message' => 'Theme updated successfully']);
        } else {
            wp_send_json_error(['message' => 'Invalid request']);
        }
    }


    // Function to get custom field groups
    function get_custom_field_groups()
    {
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
    function get_custom_fields()
    {
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

    public function run()
    {
        $this->loader->run();
    }

    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    public function get_version()
    {
        return $this->version;
    }

    private function load_dependencies()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ai_assistant-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ai_assistant-i18n.php'; // Ensure this is included
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-ai_assistant-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-ai_assistant-public.php';

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ai_assistant-live-css.php';

        $this->loader = new AI_Assistant_Loader();
    }

    private function set_locale()
    {
        $plugin_i18n = new AI_Assistant_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    public function save_acf_json()
    {
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

        $theme_dir = get_stylesheet_directory() . '/acf-json'; // Save inside active theme

        // Ensure the directory exists
        if (!file_exists($theme_dir)) {
            mkdir($theme_dir, 0755, true);
        }

        $file_path = $theme_dir . '/' . sanitize_title($json_data['title']) . '.json';

        // Write JSON file
        if (file_put_contents($file_path, json_encode($json_data, JSON_PRETTY_PRINT))) {

            // ✅ Auto-import the field group into ACF
            if (function_exists('acf_import_field_group')) {
                acf_import_field_group($json_data);
            }

            wp_send_json_success("ACF JSON saved & imported automatically!");
        } else {
            wp_send_json_error("Failed to save JSON.");
        }
    }


    public function ai_assistant_save_file()
    {
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

    function ai_assistant_change_admin_email_callback() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("You are not allowed to perform this action.");
        }

        $new_email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (!is_email($new_email)) {
            wp_send_json_error("Invalid email format.");
        }

        if (update_option('admin_email', $new_email)) {
            wp_send_json_success("Admin email changed to: $new_email");
        } else {
            wp_send_json_error("Failed to update admin email.");
        }
    }

    function ai_assistant_change_user_language_callback() {
        if (!is_user_logged_in()) {
            wp_send_json_error("Not logged in.");
        }

        $language = sanitize_text_field($_POST['language'] ?? '');
        $available = get_available_languages();

        if (!in_array($language, $available)) {
            wp_send_json_error("Invalid language selected.");
        }

        $user_id = get_current_user_id();
        update_user_meta($user_id, 'locale', $language);

        wp_send_json_success("✅ Language changed to: $language");
    }

    function ai_assistant_update_cf7_translated_messages() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Permission denied.");
        }

        $form_id = intval($_POST['form_id'] ?? 0);
        $messages_json = $_POST['messages_json'] ?? [];

        if (!$form_id || empty($messages_json) || !is_array($messages_json)) {
            wp_send_json_error("Missing or invalid data.");
        }

        $updated_messages = [];

        // Directly copy each string key => value into array
        foreach ($messages_json as $key => $value) {
            $updated_messages[$key] = sanitize_text_field($value);
        }

        update_post_meta($form_id, '_messages', $updated_messages);
        wp_send_json_success("✅ Messages updated successfully.");
    }


    function get_acf_location_data()
    {
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

    public function set_homepage()
    {
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

    public function reset_permalink_structure()
    {
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

    function ai_assistant_toggle_wp_debug() {
        // Only allow admins
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $config_path = ABSPATH . 'wp-config.php';

        if (!file_exists($config_path) || !is_writable($config_path)) {
            wp_send_json_error("Cannot access wp-config.php.");
        }

        $config = file_get_contents($config_path);
        if ($config === false) {
            wp_send_json_error("Failed to read wp-config.php.");
        }

        // Match current WP_DEBUG line
        $pattern = "/(define\s*\(\s*['\"]WP_DEBUG['\"]\s*,\s*)(true|false)(\s*\)\s*;)/i";

        if (preg_match($pattern, $config, $matches)) {
            $current_value = strtolower($matches[2]);
            $new_value = ($current_value === 'true') ? 'false' : 'true';
            $new_line = $matches[1] . $new_value . $matches[3];

            // Replace in file
            $updated_config = preg_replace($pattern, $new_line, $config, 1);

            if (file_put_contents($config_path, $updated_config)) {
                wp_send_json_success("WP_DEBUG set to {$new_value}.");
            } else {
                wp_send_json_error("Failed to write to wp-config.php.");
            }
        } else {
            wp_send_json_error("WP_DEBUG definition not found.");
        }
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
    public function ai_assistant_get_theme_details()
    {
        wp_send_json_success([
            'theme_name' => get_option('ai_assistant_theme_name', ''),
            'theme_uri' => get_option('ai_assistant_theme_uri', ''),
            'author' => get_option('ai_assistant_author', ''),
            'author_uri' => get_option('ai_assistant_author_uri', ''),
            'text_domain' => get_option('ai_assistant_text_domain', '')
        ]);
    }

    public function ai_assistant_create_page_and_template()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $page_name = sanitize_text_field($_POST['page_name']);
        $create_template = isset($_POST['create_template']) ? boolval($_POST['create_template']) : false;
        $theme_dir = get_stylesheet_directory();
        $template_slug = 'template-' . sanitize_title($page_name) . '.php';

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
            'post_title' => $page_name,
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'page_template' => $create_template ? $template_slug : ''
        ]);

        if (is_wp_error($page_id) || !$page_id) {
            wp_send_json_error("Failed to create page.");
        }

        $message = "Page '{$page_name}' created successfully.";
        $message .= $create_template ? " Template attached: {$template_slug}" : "";

        wp_send_json_success($message);
    }

    //create menu and respond menu id
    public function ai_assistant_create_menu()
    {
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
    public function ai_assistant_correct_header()
    {
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

    public function ai_assistant_correct_page() {
        check_ajax_referer('ai_assistant_nonce');

        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error('Unauthorized access.', 403);
        }

        $raw = isset($_POST['content']) ? $_POST['content'] : ( $_POST['page_content'] ?? '' );
        if ( empty($raw) ) {
            wp_send_json_error('No page content received.');
        }

        $page_content = wp_unslash($raw);

        // Helper: determine if URL should be skipped (absolute, data, etc.)
        $should_skip = function(string $url): bool {
            return (bool) preg_match('~^(?:https?:)?//|data:|mailto:|tel:|#~i', $url);
        };

        // Helper: normalize relative paths like ./assets/x or /assets/x
        $normalize_rel = function(string $path): string {
            $path = trim($path);
            $path = preg_replace('~^\./+~', '', $path); // remove leading ./
            $path = ltrim($path, '/');                  // remove leading /
            return $path;
        };

        // ✅ A) Replace <a href="index.html|index.php"> with PHP home_url()
        $page_content = preg_replace(
            '/<a([^>]+)href=["\']([^"\']*(?:index\.html|index\.php))["\']/i',
            '<a$1href="<?php echo home_url(); ?>"',
            $page_content
        );

        // ✅ B) Fix <img src="relative"> including ./relative
        $page_content = preg_replace_callback(
            '/<img\b([^>]*?)\bsrc=["\']([^"\']+)["\']([^>]*)>/i',
            function ($m) use ($should_skip, $normalize_rel) {
                $before = $m[1];
                $src    = $m[2];
                $after  = $m[3];

                if ($should_skip($src)) {
                    return $m[0];
                }

                $src = $normalize_rel($src);

                // Use get_stylesheet_directory_uri() (child-theme safe)
                return '<img' . $before . 'src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/' . esc_attr($src) . '"' . $after . '>';
            },
            $page_content
        );

        // ✅ C) Fix inline CSS url(...) safely (avoid quote-breaking)
        $page_content = preg_replace_callback(
            '/\bstyle=(["\'])(.*?)\1/i',
            function ($m) use ($should_skip, $normalize_rel) {
                $outer_quote = $m[1];
                $style       = $m[2];

                $style = preg_replace_callback(
                    '~url\(\s*(["\']?)([^"\')]+)\1\s*\)~i',
                    function ($u) use ($should_skip, $normalize_rel) {
                        $url = $u[2];

                        if ($should_skip($url)) {
                            return $u[0];
                        }

                        $url = $normalize_rel($url);

                        // Always single-quote inside url(...) to avoid breaking style="..."
                        return "url('<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/$url')";
                    },
                    $style
                );

                return 'style=' . $outer_quote . $style . $outer_quote;
            },
            $page_content
        );

        wp_send_json_success($page_content);
    }




    // correct footer
    public function ai_assistant_correct_footer()
    {
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

    public function ai_assistant_detect_and_convert_menu()
    {
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
                'message' => "✅ Bootstrap menu converted successfully!"
            ]);
        } // ✅ Handle Custom-Styled Menu
        elseif (preg_match('/class=["\'].*?(menu-item|menu-link|dropdown-list).*?["\']/', $menu_html)) {
            $menu_code = "<?php wp_nav_menu([
            'menu' => '{$menu_name}',
            'menu_class' => '{$menu_class}',
            'container' => false
        ]); ?>";

            wp_send_json_success([
                'menu_code' => $menu_code,
                'message' => "✅ Custom menu converted successfully!"
            ]);
        } // ✅ Handle Default Basic Menu
        else {
            $menu_code = "<?php wp_nav_menu([
            'menu' => '{$menu_name}',
            'menu_class' => '{$menu_class}',
            'container' => false
        ]); ?>";

            wp_send_json_success([
                'menu_code' => $menu_code,
                'message' => "✅ Basic menu converted successfully!"
            ]);
        }
    }


    // ✅ Fetch all theme files
    public function ai_assistant_get_theme_files()
    {
        $theme_dir = get_stylesheet_directory();
        $files = array_diff(scandir($theme_dir), array('.', '..'));
        wp_send_json_success(['files' => array_values($files)]);
    }

    // 🗑 Delete file or folder (recursively)
    public function ai_assistant_delete_file()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("❌ Unauthorized access.");
        }

        if (empty($_POST['file_path'])) {
            wp_send_json_error("❌ Missing file or folder path.");
        }

        // Relative path from theme root (what JS sends)
        $relative_path = sanitize_text_field(wp_unslash($_POST['file_path']));
        $relative_path = ltrim($relative_path, "/\\");

        // Security: no going outside theme
        if (strpos($relative_path, '..') !== false) {
            wp_send_json_error("❌ Invalid path.");
        }

        $theme_dir = realpath(get_stylesheet_directory());
        if (!$theme_dir) {
            wp_send_json_error("❌ Theme directory not found.");
        }

        $theme_dir = wp_normalize_path($theme_dir);
        $full_path = wp_normalize_path($theme_dir . '/' . $relative_path);

        // Ensure the path is actually inside the theme directory
        if (strpos($full_path, $theme_dir) !== 0) {
            wp_send_json_error("❌ Access denied.");
        }

        if (!file_exists($full_path)) {
            wp_send_json_error("❌ File or folder not found.");
        }

        // Folder → recursive delete, File → unlink
        if (is_dir($full_path)) {
            $this->ai_assistant_delete_folder_recursive($full_path);
        } else {
            if (!@unlink($full_path)) {
                wp_send_json_error("❌ Failed to delete file.");
            }
        }

        wp_send_json_success("✅ Successfully deleted: " . basename($relative_path));
    }

    /**
     * Recursive folder deletion (internal helper)
     */
    private function ai_assistant_delete_folder_recursive($folder)
    {
        $folder = wp_normalize_path($folder);

        if (!is_dir($folder)) {
            return;
        }

        $items = scandir($folder);
        if (!is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $folder . '/' . $item;

            if (is_dir($path)) {
                $this->ai_assistant_delete_folder_recursive($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($folder);
    }


    // ✅ Load file content for editor
    public function ai_assistant_load_theme_file()
    {
        if (!isset($_POST['filename'])) wp_send_json_error('Filename missing.');

        $theme_dir = get_stylesheet_directory();
        $file_path = $theme_dir . '/' . sanitize_file_name($_POST['filename']);

        if (!file_exists($file_path)) wp_send_json_error('File not found.');
        $content = file_get_contents($file_path);
        wp_send_json_success(['content' => $content]);
    }

    // create cpt
    function ai_assistant_create_cpt_handler()
    {
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


    //create cpt single and archive page
    function ai_assistant_create_cpt_pages_handler() {

        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        $cpt_slug = isset($_POST['cpt_slug']) ? sanitize_key($_POST['cpt_slug']) : '';
        $create_single  = !empty($_POST['create_template']) ? (int) $_POST['create_template'] : 0;
        $create_archive = !empty($_POST['create_archive_template']) ? (int) $_POST['create_archive_template'] : 0;
        $no_of_posts    = isset($_POST['no_of_posts']) ? max(1, (int) $_POST['no_of_posts']) : 10;

        if (!$cpt_slug) {
            wp_send_json_error("❌ Please select a CPT.");
        }

        $cpt_obj = get_post_type_object($cpt_slug);
        if (!$cpt_obj) {
            wp_send_json_error("❌ Invalid CPT.");
        }

        $theme_dir = get_stylesheet_directory();
        $created = [];

        // ✅ Single template
        if ($create_single === 1) {
            $single_file = $theme_dir . "/single-{$cpt_slug}.php";

            if (!file_exists($single_file)) {
                $single_content =
                    "<?php\n" .
                    "// Single Template for '{$cpt_slug}' CPT\n" .
                    "get_header();\n" .
                    "?>\n\n" .
                    "<div class=\"container\">\n" .
                    "    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>\n" .
                    "        <h1><?php the_title(); ?></h1>\n" .
                    "        <div class=\"content\"><?php the_content(); ?></div>\n" .
                    "    <?php endwhile; endif; ?>\n" .
                    "</div>\n\n" .
                    "<?php get_footer(); ?>\n";

                if (file_put_contents($single_file, $single_content) !== false) {
                    $created[] = "single-{$cpt_slug}.php";
                }
            }
        }

        // ✅ Archive template
        if ($create_archive === 1) {
            $archive_file = $theme_dir . "/archive-{$cpt_slug}.php";

            if (!file_exists($archive_file)) {
                $archive_content = <<<PHP
<?php
// Archive Template for '{$cpt_slug}' CPT
get_header(); ?>

<div class="container">
    <h1><?php post_type_archive_title(); ?></h1>

    <?php if (have_posts()) : ?>
        <div class="post-list">
            <?php while (have_posts()) : the_post(); ?>
                <article class="post-item">
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <?php the_excerpt(); ?>
                </article>
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

                if (file_put_contents($archive_file, $archive_content) !== false) {
                    $created[] = "archive-{$cpt_slug}.php";
                }
            }

            // ✅ Add posts_per_page for this CPT archive ONLY ONCE
            $functions_path = $theme_dir . "/functions.php";
            $marker = "AI_ASSISTANT_PPP_{$cpt_slug}";

            $existing = file_exists($functions_path) ? file_get_contents($functions_path) : '';

            if (strpos($existing, $marker) === false) {
                $ppp_code  = "\n\n// {$marker}\n";
                $ppp_code .= "add_action('pre_get_posts', function(\$query){\n";
                $ppp_code .= "    if (!is_admin() && \$query->is_main_query() && is_post_type_archive('{$cpt_slug}')) {\n";
                $ppp_code .= "        \$query->set('posts_per_page', {$no_of_posts});\n";
                $ppp_code .= "    }\n";
                $ppp_code .= "});\n";

                file_put_contents($functions_path, $ppp_code, FILE_APPEND);
                $created[] = "functions.php posts_per_page={$no_of_posts}";
            }
        }

        if (empty($created)) {
            wp_send_json_success("✅ Nothing to create. Templates already exist.");
        }

        wp_send_json_success("✅ Created/updated: " . implode(", ", $created));
    }


    // Create user type
    function ai_assistant_create_user_type()
    {
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

    function ai_assistant_delete_user_role()
    {
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


    function ai_assistant_create_template_part()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
        }

        if (empty($_POST['filename']) || empty($_POST['template_content'])) {
            wp_send_json_error("Filename or template content is missing.");
        }

        $filename = sanitize_file_name($_POST['filename']);
        $template_content = stripslashes($_POST['template_content']);

        $theme_dir = get_template_directory();
        $partial_dir = $theme_dir . '/partials';

        // Ensure the 'partial' directory exists
        if (!file_exists($partial_dir)) {
            wp_mkdir_p($partial_dir);
        }

        $file_path = $partial_dir . "/partial-{$filename}.php";

        // Attempt to create and write to the file
        if (file_put_contents($file_path, $template_content) === false) {
            wp_send_json_error("Failed to create template file.");
        }

        wp_send_json_success("Template file created successfully: partial-{$filename}.php");
    }

    function ai_assistant_handle_get_pages_list() {
        check_ajax_referer('pages_nonce', 'nonce');

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        $args = array(
            'post_type' => 'page',
            'posts_per_page' => -1,
            'post_status' => 'publish,draft,private',
            'orderby' => 'title',
            'order' => 'ASC'
        );

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $pages = get_posts($args);

        if (empty($pages)) {
            wp_send_json_success(array(
                'html' => '<div class="no-pages">No pages found</div>'
            ));
        }

        ob_start();
        foreach ($pages as $page) {
            $edit_url = get_edit_post_link($page->ID);
            $view_url = get_permalink($page->ID);
            $status = get_post_status($page->ID);
            $status_class = 'status-' . $status;
            ?>
            <div class="page-item <?php echo esc_attr($status_class); ?>">
                <div class="page-info">
                    <div class="page-title"><?php echo esc_html($page->post_title); ?></div>
                    <div class="page-meta">
                        <span class="page-status"><?php echo esc_html(ucfirst($status)); ?></span>
                        <span class="page-id">ID: <?php echo esc_html($page->ID); ?></span>
                        <span class="page-date"><?php echo esc_html(get_the_date('M j, Y', $page->ID)); ?></span>
                    </div>
                </div>
                <div class="page-actions">
                    <a href="<?php echo esc_url($edit_url); ?>" class="btn-edit" target="_blank">
                        <span class="dashicons dashicons-edit"></span> Edit
                    </a>
                    <a href="<?php echo esc_url($view_url); ?>" class="btn-view" target="_blank">
                        <span class="dashicons dashicons-visibility"></span> View
                    </a>
                </div>
            </div>
            <?php
        }
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'count' => count($pages)
        ));
    }

    function ai_assistant_enqueue_pages_list_assets() {
        wp_enqueue_script(
            'pages-list-script',
            plugin_dir_url(__FILE__) . 'js/pages-list.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('pages-list-script', 'pagesListAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pages_nonce')
        ));

        wp_enqueue_style(
            'pages-list-style',
            plugin_dir_url(__FILE__) . 'css/pages-list.css',
            array(),
            '1.0.0'
        );
    }


    public function ai_assistant_create_file() {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            wp_send_json_error( '❌ Unauthorized access.' );
        }

        if ( empty( $_POST['file_path'] ) ) {
            wp_send_json_error( '❌ Missing file path.' );
        }

        $relative_path = ltrim( sanitize_text_field( wp_unslash( $_POST['file_path'] ) ), '/\\' );

        if ( strpos( $relative_path, '..' ) !== false ) {
            wp_send_json_error( '❌ Invalid path.' );
        }

        $theme_dir = realpath( get_stylesheet_directory() );
        if ( ! $theme_dir ) {
            wp_send_json_error( '❌ Theme directory not found.' );
        }

        $theme_dir = wp_normalize_path( $theme_dir );
        $target    = wp_normalize_path( $theme_dir . '/' . $relative_path );

        if ( strpos( $target, $theme_dir ) !== 0 ) {
            wp_send_json_error( '❌ Access denied.' );
        }

        $parent_dir = dirname( $target );
        if ( ! is_dir( $parent_dir ) ) {
            if ( ! wp_mkdir_p( $parent_dir ) ) {
                wp_send_json_error( '❌ Failed to create parent directory.' );
            }
        }

        if ( file_exists( $target ) ) {
            wp_send_json_error( '❌ File already exists.' );
        }

        $filename        = basename( $relative_path );
        $default_content = "<?php\n\n// {$filename} created by AI Assistant.\n";

        if ( file_put_contents( $target, $default_content ) === false ) {
            wp_send_json_error( '❌ Failed to create file.' );
        }

        wp_send_json_success( '✅ File created: ' . $relative_path );
    }

    public function ai_assistant_create_folder() {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            wp_send_json_error( '❌ Unauthorized access.' );
        }

        if ( empty( $_POST['folder_path'] ) ) {
            wp_send_json_error( '❌ Missing folder path.' );
        }

        $relative_path = ltrim( sanitize_text_field( wp_unslash( $_POST['folder_path'] ) ), '/\\' );

        if ( strpos( $relative_path, '..' ) !== false ) {
            wp_send_json_error( '❌ Invalid path.' );
        }

        $theme_dir = realpath( get_stylesheet_directory() );
        if ( ! $theme_dir ) {
            wp_send_json_error( '❌ Theme directory not found.' );
        }

        $theme_dir = wp_normalize_path( $theme_dir );
        $target    = wp_normalize_path( $theme_dir . '/' . $relative_path );

        if ( strpos( $target, $theme_dir ) !== 0 ) {
            wp_send_json_error( '❌ Access denied.' );
        }

        if ( is_dir( $target ) ) {
            wp_send_json_error( '❌ Folder already exists.' );
        }

        if ( ! wp_mkdir_p( $target ) ) {
            wp_send_json_error( '❌ Failed to create folder.' );
        }

        wp_send_json_success( '✅ Folder created: ' . $relative_path );
    }

    public function ai_assistant_add_admin_bar_link( $wp_admin_bar ) {
        // Only on frontend, logged in, with proper capability
        if ( is_admin() || ! is_user_logged_in() || ! current_user_can( 'edit_theme_options' ) ) {
            return;
        }

        global $template;

        if ( empty( $template ) ) {
            return;
        }

        $theme_dir = realpath( get_stylesheet_directory() );
        if ( ! $theme_dir ) {
            return;
        }

        $theme_dir       = wp_normalize_path( $theme_dir );
        $current_template = wp_normalize_path( $template );

        // Only if current template is inside active theme
        if ( strpos( $current_template, $theme_dir ) !== 0 ) {
            return;
        }

        // Get relative path, e.g. "page.php" or "templates/home.php"
        $relative_path = ltrim( substr( $current_template, strlen( $theme_dir ) ), '/\\' );

        // 🔴 IMPORTANT: change this slug to whatever you use for this page
        // This should match the slug used in add_menu_page()/add_submenu_page()
        // that renders custom_theme_editor.php
        $editor_url = add_query_arg(
            [
                'page' => 'ai_assistant-theme-editor',
                'file' => $relative_path,
            ],
            admin_url( 'admin.php' )
        );

        $wp_admin_bar->add_node( [
            'id'    => 'ai-assistant-edit-template',
            'title' => 'Edit with AI Editor',
            'href'  => $editor_url,
            'meta'  => [
                'title' => 'Edit this template in the AI editor',
            ],
        ] );
    }

    public function ai_assistant_admin_bar_open_new_tab( $wp_admin_bar ) {

        // IDs we want to change. Adjust / extend if needed.
        $ids = array(
            'view-site',        // Default "Visit Site"
            'view-store',       // WooCommerce "Visit Store" (common)
            'woocommerce-store' // Alternative ID in some WooCommerce versions
        );

        foreach ( $ids as $id ) {
            $node = $wp_admin_bar->get_node( $id );

            if ( $node ) {
                // Ensure meta exists as array
                if ( empty( $node->meta ) || ! is_array( $node->meta ) ) {
                    $node->meta = array();
                }

                // Force open in new tab
                $node->meta['target'] = '_blank';

                // Re-add the modified node
                $wp_admin_bar->add_node( $node );
            }
        }
    }

    function ai_assistant_get_meta_json() {
        check_ajax_referer( 'ai_assistant_wpml', 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => 'Missing post ID.' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            wp_send_json_error( array( 'message' => 'Post not found.' ) );
        }

        // 🧩 Core post data
        $post_data = array(
            'title'   => $post->post_title,
            'slug'    => $post->post_name,
            'content' => $post->post_content,
        );

        // 🧩 Custom fields (meta)
        $raw_meta = get_post_meta( $post_id );
        $meta     = array();

        foreach ( $raw_meta as $key => $values ) {
            // Skip internal WP stuff
            if ( in_array( $key, array( '_edit_lock', '_edit_last' ), true ) ) {
                continue;
            }

            // Normalize values (maybe_unserialize)
            if ( count( $values ) === 1 ) {
                $value = maybe_unserialize( $values[0] );
            } else {
                $value = array_map( 'maybe_unserialize', $values );
            }

            // 🔹 Skip ACF "field reference" meta:
            // keys starting with "_" whose value looks like "field_..."
            if (
                strpos( $key, '_' ) === 0   // meta key begins with "_"
                && is_string( $value )
                && strpos( $value, 'field_' ) === 0
            ) {
                continue;
            }

            // 🔹 Remove empties inside arrays
            if ( is_array( $value ) ) {
                $value = array_filter(
                    $value,
                    static function( $v ) {
                        if ( is_string( $v ) ) {
                            return trim( $v ) !== '';
                        }
                        if ( is_array( $v ) ) {
                            return ! empty( $v );
                        }
                        return $v !== null;
                    }
                );
            }

            // 🔹 Skip empty values entirely
            $is_empty =
                ( is_string( $value ) && trim( $value ) === '' ) ||
                ( is_array( $value ) && empty( $value ) ) ||
                $value === null;

            if ( $is_empty ) {
                continue;
            }

            // Keep this meta
            $meta[ $key ] = $value;
        }

        wp_send_json_success(
            array(
                'post' => $post_data,
                'meta' => $meta,
            )
        );
    }



    function ai_assistant_update_meta_from_json() {
        check_ajax_referer( 'ai_assistant_wpml', 'nonce' );

        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => 'Missing post ID.' ) );
        }

        $json = isset( $_POST['meta_json'] ) ? wp_unslash( $_POST['meta_json'] ) : '';
        if ( ! $json ) {
            wp_send_json_error( array( 'message' => 'Missing JSON data.' ) );
        }

        $data = json_decode( $json, true );
        if ( ! is_array( $data ) ) {
            wp_send_json_error( array( 'message' => 'Invalid JSON data.' ) );
        }

        /**
         * 1) Update post core fields: title / slug / content
         */
        if ( isset( $data['post'] ) && is_array( $data['post'] ) ) {
            $post_update = array( 'ID' => $post_id );

            if ( isset( $data['post']['title'] ) ) {
                $post_update['post_title'] = $data['post']['title'];
            }

            if ( isset( $data['post']['slug'] ) ) {
                // Let WP sanitize it, but we still pass what AI gave
                $post_update['post_name'] = $data['post']['slug'];
            }

            if ( isset( $data['post']['content'] ) ) {
                $post_update['post_content'] = $data['post']['content'];
            }

            // Only call wp_update_post if we actually have something to update
            if ( count( $post_update ) > 1 ) {
                $result = wp_update_post( $post_update, true );

                if ( is_wp_error( $result ) ) {
                    wp_send_json_error( array(
                        'message' => 'Failed to update post data.',
                        'error'   => $result->get_error_message(),
                    ) );
                }
            }
        }

        /**
         * 2) Update custom fields (meta)
         */
        $meta_data = isset( $data['meta'] ) && is_array( $data['meta'] )
            ? $data['meta']
            : array();

        foreach ( $meta_data as $key => $value ) {
            if ( ! is_string( $key ) || $key === '' ) {
                continue;
            }

            // Skip WP internals
            if ( in_array( $key, array( '_edit_lock', '_edit_last' ), true ) ) {
                continue;
            }

            // 🔹 Skip ACF "field reference" meta:
            // keys starting with "_" and value looks like "field_..."
            if (
                strpos( $key, '_' ) === 0 &&
                is_string( $value ) &&
                strpos( $value, 'field_' ) === 0
            ) {
                continue;
            }

            // Remove existing meta for that key
            delete_post_meta( $post_id, $key );

            if ( is_array( $value ) ) {
                foreach ( $value as $single ) {
                    add_post_meta( $post_id, $key, $single );
                }
            } else {
                update_post_meta( $post_id, $key, $value );
            }
        }


        wp_send_json_success( array( 'message' => 'Post + custom fields updated successfully.' ) );
    }


    function ai_assistant_imp_style() {

        // If AI_DB is NOT defined OR is defined but FALSE → hide UI
        if ( !defined('AI_DB') || (defined('AI_DB') && AI_DB === false) ) {
            ?>
            <style>
                #toplevel_page_ai_assistant-settings {
                    display: none !important;
                }
                tr[data-plugin="Ai-assistant/ai_assistant.php"],
                #wp-admin-bar-ai_assistant {
                    display: none !important;
                }
		#wp-admin-bar-ai-assistant-edit-template{
					display:none !important
				}
            </style>
            <?php
        }

    }


    /**
     * STEP 1: Upload ZIP -> Extract -> Detect root pages -> Save to transient -> Redirect back with tg_session
     */
    public function handle_theme_zip_upload() {
        if ( ! current_user_can('manage_options') ) {
            wp_die( esc_html__( 'Unauthorized.', 'ai_assistant' ) );
        }

        check_admin_referer('ai_tg_upload_zip_nonce');

        if ( empty($_FILES['ai_tg_zip']) || empty($_FILES['ai_tg_zip']['name']) ) {
            wp_safe_redirect( add_query_arg(['tg_error' => 'no_zip'], wp_get_referer()) );
            exit;
        }

        $file = $_FILES['ai_tg_zip'];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            wp_safe_redirect( add_query_arg(['tg_error' => 'not_zip'], wp_get_referer()) );
            exit;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $uploads = wp_upload_dir();
        if ( empty($uploads['basedir']) ) {
            wp_safe_redirect( add_query_arg(['tg_error' => 'upload_dir'], wp_get_referer()) );
            exit;
        }

        $session_id = wp_generate_uuid4();

        $base_dir   = trailingslashit($uploads['basedir']) . 'ai-theme-generator/' . $session_id . '/';
        $zip_path   = $base_dir . 'source.zip';
        $extract_dir= $base_dir . 'extracted/';

        wp_mkdir_p($base_dir);
        wp_mkdir_p($extract_dir);

        // Move uploaded ZIP
        if ( ! @move_uploaded_file($file['tmp_name'], $zip_path) ) {
            wp_safe_redirect( add_query_arg(['tg_error' => 'move_failed'], wp_get_referer()) );
            exit;
        }

        // Extract
        $unzipped = unzip_file($zip_path, $extract_dir);
        if ( is_wp_error($unzipped) ) {
            wp_safe_redirect( add_query_arg(['tg_error' => 'unzip_failed'], wp_get_referer()) );
            exit;
        }

        // Detect root pages
        $detected_pages = $this->detect_root_pages($extract_dir);

        // Store for Phase 2 create step
        set_transient('ai_tg_session_' . $session_id, [
            'session_id'      => $session_id,
            'extract_dir'     => $extract_dir,
            'detected_pages'  => $detected_pages,
            'created_at'      => time(),
        ], HOUR_IN_SECONDS);

        // Redirect back to the page (same admin page) with session id
        $back = wp_get_referer();
        $back = remove_query_arg(['tg_error'], $back);
        wp_safe_redirect( add_query_arg(['tg_session' => $session_id, 'tg_step' => 'review'], $back) );
        exit;
    }

    /**
     * STEP 2: Create WP pages (draft) from detected pages + renamed titles
     */
    public function handle_create_pages() {
        if ( ! current_user_can('manage_options') ) {
            wp_die( esc_html__( 'Unauthorized.', 'ai_assistant' ) );
        }

        check_admin_referer('ai_tg_create_pages_nonce');

        $session_id = isset($_POST['tg_session']) ? sanitize_text_field($_POST['tg_session']) : '';
        if ( empty($session_id) ) {
            wp_safe_redirect( add_query_arg(['tg_error' => 'missing_session'], wp_get_referer()) );
            exit;
        }

        $session = get_transient('ai_tg_session_' . $session_id);
        if ( empty($session) || empty($session['detected_pages']) || empty($session['extract_dir']) ) {
            wp_safe_redirect( add_query_arg(['tg_error' => 'session_expired'], wp_get_referer()) );
            exit;
        }

        $detected_pages = $session['detected_pages'];
        $extract_dir    = $session['extract_dir'];

        $submitted_pages = isset($_POST['pages']) && is_array($_POST['pages']) ? $_POST['pages'] : [];
        $include         = isset($_POST['include']) && is_array($_POST['include']) ? $_POST['include'] : [];

        $created = 0;
        $updated = 0;

        foreach ($detected_pages as $i => $p) {
            $file = $p['file'];
            $slug = $p['slug'];

            // Only if checked
            if ( empty($include[$i]) ) {
                continue;
            }

            $title = $p['title'];
            if ( isset($submitted_pages[$i]['title']) ) {
                $title = sanitize_text_field($submitted_pages[$i]['title']);
            }
            if ( $title === '' ) {
                $title = $p['title'];
            }

            // Find existing page by slug
            $existing = get_page_by_path($slug, OBJECT, 'page');

            $content = "<!-- AI Theme Generator Source: {$file} (session: {$session_id}) -->\n";

            if ( $existing && $existing instanceof WP_Post ) {
                wp_update_post([
                    'ID'         => $existing->ID,
                    'post_title' => $title,
                ]);
                update_post_meta($existing->ID, '_ai_tg_source_file', $file);
                update_post_meta($existing->ID, '_ai_tg_session', $session_id);
                $updated++;
            } else {
                $new_id = wp_insert_post([
                    'post_type'   => 'page',
                    'post_status' => 'draft', // keep safe
                    'post_title'  => $title,
                    'post_name'   => $slug,
                    'post_content'=> $content,
                ]);

                if ( ! is_wp_error($new_id) && $new_id ) {
                    update_post_meta($new_id, '_ai_tg_source_file', $file);
                    update_post_meta($new_id, '_ai_tg_session', $session_id);
                    $created++;
                }
            }
        }

        // Keep session for Phase 3, but you can expire it later if you want
        $back = wp_get_referer();
        $back = remove_query_arg(['tg_error'], $back);

        wp_safe_redirect( add_query_arg([
            'tg_session' => $session_id,
            'tg_step'    => 'done',
            'created'    => $created,
            'updated'    => $updated,
        ], $back) );
        exit;
    }

    /**
     * Root-level page detection: only files directly in extracted root (no folders)
     */
    private function detect_root_pages($extract_dir) {
        $detected_pages = [];

        if ( ! is_dir($extract_dir) ) {
            return $detected_pages;
        }

        $files = scandir($extract_dir);
        if ( ! is_array($files) ) {
            return $detected_pages;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $full = $extract_dir . $file;

            if (!is_file($full)) continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ( ! in_array($ext, ['html', 'php'], true) ) continue;
            if ( str_starts_with($file, '_') ) continue;

            $slug = sanitize_title(pathinfo($file, PATHINFO_FILENAME));

            // You can choose to skip index.php here if you want:
            // if ($file === 'index.php') continue;

            $detected_pages[] = [
                'file'  => $file,
                'slug'  => $slug,
                'title' => ucwords(str_replace('-', ' ', $slug)),
            ];
        }

        return $detected_pages;
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



