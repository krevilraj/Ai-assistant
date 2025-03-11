<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    AI_Assistant
 * @subpackage AI_Assistant/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    AI_Assistant
 * @subpackage AI_Assistant/admin
 * @author     Your Name <email@example.com>
 */
class AI_Assistant_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in AI_Assistant_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The AI_Assistant_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/ai_assistant-admin.css', array(), $this->version, 'all');
        wp_enqueue_style($this->plugin_name . '-popup', plugin_dir_url(dirname(__FILE__)) . 'public/css/ai_assistant-popup.css', array(), $this->version, 'all');

        // ✅ Check if we're on the correct admin page
        if (isset($_GET['page']) && $_GET['page'] === 'ai_assistant-theme-editor') {
            wp_enqueue_style($this->plugin_name . 'dark-theme', plugin_dir_url(__FILE__) . 'css/darktheme.css', array(), $this->version, 'all');
            wp_enqueue_style($this->plugin_name . 'coder_snippet', plugin_dir_url(__FILE__) . 'css/ai_assistant-snippet.css', array(), $this->version, 'all');
        }

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in AI_Assistant_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The AI_Assistant_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */


        wp_enqueue_script($this->plugin_name.'-theme-switch1', plugin_dir_url(__FILE__) . 'js/ai_assistant-theme-switch.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/ai_assistant-admin.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name . '-popup', plugin_dir_url(dirname(__FILE__)) . 'public/js/ai_assistant-popup.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_name.'-coder-snippet', plugin_dir_url(__FILE__) . 'js/ai_assistant_snippet.js', array('jquery'), $this->version, false);
        // Localize script to pass ajaxurl to JavaScript
        wp_localize_script($this->plugin_name, 'ajax_object',[
            'ajax_url' => admin_url('admin-ajax.php'),
            'saved_theme' => get_option('ai_assistant_codemirror_theme', 'default')]);

        // Load WordPress CodeMirror library
        wp_enqueue_script('code-editor');
        wp_enqueue_style('code-editor');
        wp_enqueue_script('jquery');

        // Localize script for JS usage
        wp_add_inline_script('code-editor', 'jQuery(document).ready(function($) { aiAssistantInitEditor(); });');




    }


    public function add_admin_menu()
    {
        $menu_icon_url = plugin_dir_url(__FILE__) . 'image/logo.png'; // Path to logo.png

        add_menu_page(
            __('AI Assistant Settings', 'ai_assistant'), // Page title
            __('AI Assistant', 'ai_assistant'),          // Menu title
            'manage_options',                                // Capability
            'ai_assistant-settings',                       // Menu slug
            [$this, 'display_settings_page'],             // Callback function
            $menu_icon_url,                                 // Menu icon URL
            81                                              // Position in menu
        );
        // 🎨 Custom Theme Editor Submenu
        add_submenu_page(
            'ai_assistant-settings',
            __('Theme Editor', 'ai_assistant'),
            __('Theme Editor', 'ai_assistant'),
            'manage_options',
            'ai_assistant-theme-editor',
            [$this, 'display_theme_editor_page']
        );
    }

    //display setting page
    public function display_settings_page()
    {
        ?>
        <div class="wrap ai_assistant-settings-page">
            <h1><?php _e('AI Assistant Settings', 'ai_assistant'); ?></h1>
            <h2 class="nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'ai_assistant'); ?></a>
                <a href="#template" class="nav-tab"><?php _e('Template', 'ai_assistant'); ?></a>
                <a href="#design" class="nav-tab"><?php _e('Design', 'ai_assistant'); ?></a>
            </h2>

            <div id="general" class="tab-content active">
                <h2><?php _e('General Settings', 'ai_assistant'); ?></h2>
                <form method="post" action="">
                    <?php wp_nonce_field('plugin_name_settings_save', 'plugin_name_settings_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="general_field"><?php _e('General Field', 'ai_assistant'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="general_field" name="general_field"
                                       value="<?php echo esc_attr(get_option('general_field', '')); ?>"
                                       class="regular-text"/>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>

            <div id="template" class="tab-content">
                <h2><?php _e('Template Settings', 'ai_assistant'); ?></h2>
                <p><?php _e('Here you can configure your template settings.', 'ai_assistant'); ?></p>
            </div>

            <div id="design" class="tab-content">
                <h2><?php _e('Design Settings', 'ai_assistant'); ?></h2>
                <p><?php _e('Here you can configure your design settings.', 'ai_assistant'); ?></p>
            </div>
        </div>
        <?php
    }

    //display Editor page
    public function display_theme_editor_page()
    {
        include plugin_dir_path(__FILE__) . 'pages/custom_theme_editor.php';
    }


    //add link that opens a popup from the adminbar
    public function add_custom_field_link_to_admin_bar($wp_admin_bar)
    {
        // Show only if the user has the required capability
        if (!current_user_can('manage_options')) {
            return;
        }

        // Add the custom link to the admin bar
        $wp_admin_bar->add_node(array(
            'id' => 'ai_assistant',
            'title' => 'Ai Assistant',
            'href' => '#',
            'meta' => array(
                'class' => 'open-custom-field-popup', // CSS class for styling
            ),
            'parent' => false, // Place it on the left side
        ));
    }

    //arrange the order of the menu in adminbar
    public function add_custom_field_js()
    {
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                // Find the "Customize" link
                var customizeLink = document.getElementById('wp-admin-bar-customize');

                // Find the custom link
                var customLink = document.getElementById('wp-admin-bar-create_custom_field');

                // If both links exist, move the custom link after the "Customize" link
                if (customizeLink && customLink) {
                    // Check if we're in the admin dashboard
                    if (document.body.classList.contains('wp-admin')) {
                        // In the admin dashboard, the admin bar is inside an iframe
                        var adminBar = document.getElementById('wpadminbar');
                        if (adminBar) {
                            customizeLink.parentNode.insertBefore(customLink, customizeLink.nextSibling);
                        }
                    } else {
                        // In the frontend, move the link directly
                        customizeLink.parentNode.insertBefore(customLink, customizeLink.nextSibling);
                    }
                }
            });
        </script>
        <?php
    }


    public function add_custom_field_popup()
    {
        include plugin_dir_path(__FILE__) . 'partials/ai_assistant-popup.php';
    }


}
