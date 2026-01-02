<?php
if ( ! defined('ABSPATH') ) exit;

// Register recommended plugins
add_action('tgmpa_register', 'ai_assistant_register_recommended_plugins');

function ai_assistant_register_recommended_plugins() {

    $plugins = [

        [
            'name'     => 'Contact Form 7',
            'slug'     => 'contact-form-7',
            'required' => false,
        ],

        [
            'name'     => 'Classic Editor',
            'slug'     => 'classic-editor',
            'required' => false,
        ],

        [
            'name'     => 'Custom Post Type UI',
            'slug'     => 'custom-post-type-ui',
            'required' => false,
        ],
        [
            'name'     => 'Post Duplicator',
            'slug'     => 'post-duplicator',
            'required' => false,
        ]
    ];

    $config = [
        'id'           => 'ai-assistant',
        'menu'         => 'ai-assistant-install-plugins',
        'parent_slug'  => 'plugins.php', // keep it in Plugins menu (most stable)
        'capability'   => 'manage_options',
        'has_notices'  => true,
        'dismissable'  => true,
        'is_automatic' => false,
    ];

    tgmpa($plugins, $config);
}
