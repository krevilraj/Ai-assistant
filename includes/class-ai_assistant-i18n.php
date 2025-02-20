<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    AI_Assistant
 * @subpackage AI_Assistant/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    AI_Assistant
 * @subpackage AI_Assistant/includes
 * @author     Your Name <email@example.com>
 */
class AI_Assistant_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

        load_plugin_textdomain(
            'ai_assistant',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );


    }



}
