<?php

/**
 * Plugin Name: AI Assistant
 * Plugin URI: https://asdf.com
 * Description: The AI Assistant for advanced designs, visibility controls, and customization.
 * Version: 1.0
 * Author: Rajkumar Sharma
 * Author URI: https://rajkumarsharma.com.np
 * License: GPL-2.0+
 * Text Domain: ai_assistant
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGINNAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ai_assistant-activator.php
 */
function activate_AI_Assistant() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai_assistant-activator.php';
	AI_Assistant_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ai_assistant-deactivator.php
 */
function deactivate_AI_Assistant() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ai_assistant-deactivator.php';
	AI_Assistant_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_AI_Assistant' );
register_deactivation_hook( __FILE__, 'deactivate_AI_Assistant' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ai_assistant.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_AI_Assistant() {

	$plugin = new AI_Assistant();
	$plugin->run();

}
run_AI_Assistant();
