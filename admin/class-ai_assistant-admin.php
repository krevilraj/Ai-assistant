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
            wp_enqueue_style('codemirror-foldgutter-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/fold/foldgutter.min.css');
        }

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        // === Enqueue base scripts ===
        wp_enqueue_script(
            $this->plugin_name . '-theme-switch1',
            plugin_dir_url(__FILE__) . 'js/ai_assistant-theme-switch.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/ai_assistant-admin.js',
            array('jquery'),
            $this->version,
            false
        );

        // Popup JS (for the admin-bar popup, including WPML tab)
        wp_enqueue_script(
            $this->plugin_name . '-popup',
            plugin_dir_url(dirname(__FILE__)) . 'public/js/ai_assistant-popup.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_enqueue_script(
            $this->plugin_name . '-coder-snippet',
            plugin_dir_url(__FILE__) . 'js/ai_assistant_snippet.js',
            array('jquery'),
            $this->version,
            false
        );

        // ========= LOCALIZE (existing object – keep it) =========
        $text_domain = wp_get_theme()->get('TextDomain');

        wp_localize_script(
            $this->plugin_name,
            'ajax_object',
            array(
                'text_domain'  => $text_domain,
                'ajax_url'     => admin_url('admin-ajax.php'),
                'nonce'        => wp_create_nonce('ai_assistant_nonce'),
                'is_admin'     => current_user_can('administrator') ? '1' : '0',
                'saved_theme'  => get_option('ai_assistant_codemirror_theme', 'default'),
            )
        );

        // ========= NEW: LOCALIZE FOR WPML TAB (current post id + nonce) =========

        // Try to detect current post ID on edit screens (post/page/CPT)
        $current_post_id = 0;
        if ( is_admin() ) {
            global $post;
            if ( $post instanceof WP_Post ) {
                $current_post_id = (int) $post->ID;
            } elseif ( isset($_GET['post']) ) { // fallback
                $current_post_id = (int) $_GET['post'];
            }
        }

        wp_localize_script(
            $this->plugin_name . '-popup',
            'aiAssistantAdmin',
            array(
                'ajax_url'        => admin_url('admin-ajax.php'),
                'nonce'           => wp_create_nonce('ai_assistant_wpml'),
                'current_post_id' => $current_post_id,
            )
        );

        // ========= CodeMirror / editor stuff (unchanged) =========
        wp_enqueue_script('code-editor');
        wp_enqueue_style('code-editor');
        wp_enqueue_script('jquery');

        wp_add_inline_script(
            'code-editor',
            'jQuery(document).ready(function($) { aiAssistantInitEditor(); });'
        );

        if (isset($_GET['page']) && $_GET['page'] === 'ai_assistant-theme-editor') {
            wp_enqueue_script(
                'codemirror-foldcode',
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/fold/foldcode.min.js',
                array('wp-codemirror'),
                null,
                true
            );
            wp_enqueue_script(
                'codemirror-foldgutter',
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/fold/foldgutter.min.js',
                array('wp-codemirror'),
                null,
                true
            );
            wp_enqueue_script(
                'codemirror-brace-fold',
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/fold/brace-fold.min.js',
                array('wp-codemirror'),
                null,
                true
            );
            wp_enqueue_script(
                'codemirror-comment-fold',
                'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/addon/fold/comment-fold.min.js',
                array('wp-codemirror'),
                null,
                true
            );
        }
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

        // 🔥 NEW: Live CSS Editor submenu
        add_submenu_page(
            'ai_assistant-settings',
            __('Live CSS Editor', 'ai_assistant'),
            __('Live CSS Editor', 'ai_assistant'),
            'manage_options',
            'ai_assistant-live-css-editor',
            [$this, 'display_live_css_editor_page']
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

    public function display_live_css_editor_page()
    {
        if ( ! current_user_can('manage_options') ) {
            wp_die( esc_html__( 'You do not have permission to access this page.', 'ai_assistant' ) );
        }

        $option_name = 'live_css_sync_api_key';
        $message     = '';

        // Regenerate key
        if ( isset( $_POST['ai_assistant_regenerate_live_css_key'] ) ) {
            check_admin_referer( 'ai_assistant_regenerate_live_css_key' );
            $new_key = wp_generate_password( 32, false );
            update_option( $option_name, $new_key );
            $message = __( 'API key regenerated successfully.', 'ai_assistant' );
        }

        // Ensure key exists
        $api_key = get_option( $option_name );
        if ( empty( $api_key ) ) {
            $api_key = wp_generate_password( 32, false );
            update_option( $option_name, $api_key );
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Live CSS Editor', 'ai_assistant' ); ?></h1>

            <?php if ( ! empty( $message ) ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html( $message ); ?></p>
                </div>
            <?php endif; ?>

            <p><?php esc_html_e( 'Use this API key in your Chrome extension to connect to this site.', 'ai_assistant' ); ?></p>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="ai-assistant-live-css-api-key">
                            <?php esc_html_e( 'API Key', 'ai_assistant' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text"
                               id="ai-assistant-live-css-api-key"
                               class="regular-text"
                               readonly
                               value="<?php echo esc_attr( $api_key ); ?>" />
                        <p class="description">
                            <?php esc_html_e( 'Click inside the field and press Ctrl+C to copy.', 'ai_assistant' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <form method="post">
                <?php wp_nonce_field( 'ai_assistant_regenerate_live_css_key' ); ?>
                <p>
                    <button type="submit"
                            name="ai_assistant_regenerate_live_css_key"
                            class="button button-secondary">
                        <?php esc_html_e( 'Regenerate API Key', 'ai_assistant' ); ?>
                    </button>
                </p>
            </form>

            <h2><?php esc_html_e( 'Chrome Extension Setup', 'ai_assistant' ); ?></h2>
            <ol>
                <li>
                    <?php
                    printf(
                        esc_html__( 'In the extension popup, set "WordPress Site URL" to: %s', 'ai_assistant' ),
                        '<code>' . esc_url( home_url( '/' ) ) . '</code>'
                    );
                    ?>
                </li>
                <li>
                    <?php esc_html_e( 'Paste the API key above into the extension\'s API Key field.', 'ai_assistant' ); ?>
                </li>
                <li>
                    <?php esc_html_e( 'Click "Test Connection" in the extension. It should show a success message.', 'ai_assistant' ); ?>
                </li>
            </ol>
        </div>
        <?php
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
