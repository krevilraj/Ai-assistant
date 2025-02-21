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
class AI_Assistant {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'ai_assistant';
        $this->version = '1.0.0';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function define_admin_hooks() {
        $plugin_admin = new AI_Assistant_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' ); // Add admin menu



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

    }

    private function define_public_hooks() {
        $plugin_public = new AI_Assistant_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );




    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ai_assistant-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ai_assistant-i18n.php'; // Ensure this is included
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ai_assistant-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ai_assistant-public.php';

        $this->loader = new AI_Assistant_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new AI_Assistant_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    public function save_acf_json() {
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

    function get_acf_location_data() {
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

    public function fetch_acf_location_data() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error("Unauthorized access.");
            return;
        }

        // Get post types
        $post_types = get_post_types(['public' => true], 'names');

        // Get pages
        $pages = get_posts([
            'post_type'      => 'page',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
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
                'file'  => $template_file,  // Correct file name (e.g., template-home.php)
                'label' => $template_name   // Correct human-readable label (e.g., "Home Template")
            ];
        }

        // Get taxonomies
        $taxonomies = get_taxonomies(['public' => true], 'names');

        wp_send_json_success([
            'post_types'     => array_values($post_types),
            'pages'          => $page_list,
            'page_templates' => $page_templates,
            'taxonomies'     => array_values($taxonomies),
        ]);
    }

    public function get_custom_fields_from_url() {
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
                            'slug'  => $field['name'],
                            'type'  => $field['type'], // ✅ Pass 'type' for JS usage
                        ];
                    }
                }
            }
        } else {
            $meta_fields = get_post_meta($page_id);
            foreach ($meta_fields as $key => $value) {
                $fields_data[] = [
                    'label' => $key,
                    'slug'  => $key,
                    'type'  => 'text' // ✅ Default type
                ];
            }
        }

        wp_send_json_success($fields_data);
    }

    public function set_homepage() {
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

        wp_send_json_success("Permalink structure reset to 'Post name' and flushed.");
    }





}

