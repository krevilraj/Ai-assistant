<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    AI_Assistant
 * @subpackage AI_Assistant/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    AI_Assistant
 * @subpackage AI_Assistant/includes
 * @author     Your Name <email@example.com>
 */
class AI_Assistant_Loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions = [];

    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters = [];

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string    $hook       The name of the WordPress action that is being registered.
     * @param    object    $component  A reference to the instance of the object on which the action is defined.
     * @param    string    $callback   The name of the function definition on the $component.
     */
    public function add_action( $hook, $component, $callback ) {
        if ( is_callable( [ $component, $callback ] ) ) {
            $this->actions[] = compact('hook', 'component', 'callback');
        } else {
            error_log( "Action not callable: $callback on $hook" );
        }
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    string    $hook       The name of the WordPress filter that is being registered.
     * @param    object    $component  A reference to the instance of the object on which the filter is defined.
     * @param    string    $callback   The name of the function definition on the $component.
     */
    public function add_filter( $hook, $component, $callback ) {
        if ( is_callable( [ $component, $callback ] ) ) {
            $this->filters[] = compact('hook', 'component', 'callback');
        } else {
            error_log( "Filter not callable: $callback on $hook" );
        }
    }

    /**
     * Register the actions and filters with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        // Loop through actions and register them
        foreach ( $this->actions as $action ) {
            add_action( $action['hook'], [ $action['component'], $action['callback'] ] );
        }

        // Loop through filters and register them
        foreach ( $this->filters as $filter ) {
            add_filter( $filter['hook'], [ $filter['component'], $filter['callback'] ] );
        }
    }
}


