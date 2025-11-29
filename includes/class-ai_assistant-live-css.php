<?php
/**
 * Live CSS Editor REST API integration for AI Assistant.
 *
 * Exposes:
 * - GET  /wp-json/live-css-sync/v1/test
 * - POST /wp-json/live-css-sync/v1/update
 *
 * Uses the option: live_css_sync_api_key
 */

if ( ! class_exists( 'AI_Assistant_Live_CSS' ) ) {

    class AI_Assistant_Live_CSS {

        /**
         * Hook into WordPress.
         */
        public static function init() {
            add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
        }

        /**
         * Register REST routes.
         */
        public static function register_routes() {

            // Test endpoint (used by extension popup).
            register_rest_route(
                'live-css-sync/v1',
                '/test',
                array(
                    'methods'             => 'GET',
                    'callback'            => array( __CLASS__, 'test_connection' ),
                    'permission_callback' => array( __CLASS__, 'verify_api_key' ),
                )
            );

            // Main CSS update endpoint.
            register_rest_route(
                'live-css-sync/v1',
                '/update',
                array(
                    'methods'             => 'POST',
                    'callback'            => array( __CLASS__, 'update_css' ),
                    'permission_callback' => array( __CLASS__, 'verify_api_key' ),
                    'args'                => array(
                        'file'     => array(
                            'required' => true,
                            'type'     => 'string',
                        ),
                        'selector' => array(
                            'required' => true,
                            'type'     => 'string',
                        ),
                        'oldCSS'   => array(
                            'required' => false,
                            'type'     => 'string',
                        ),
                        'newCSS'   => array(
                            'required' => true,
                            'type'     => 'string',
                        ),
                        'line'     => array(
                            'required' => false,
                            'type'     => 'integer',
                        ),
                    ),
                )
            );
        }

        /**
         * API key validation. Expects header "X-API-Key".
         */
        public static function verify_api_key( $request ) {
            $api_key    = $request->get_header( 'X-API-Key' );
            $stored_key = get_option( 'live_css_sync_api_key' );

            if ( empty( $api_key ) || empty( $stored_key ) ) {
                return false;
            }

            return hash_equals( $stored_key, $api_key );
        }

        /**
         * Simple test endpoint.
         */
        public static function test_connection() {
            return new WP_REST_Response(
                array(
                    'success' => true,
                    'message' => 'Live CSS Sync is working via AI Assistant.',
                    'theme'   => wp_get_theme()->get( 'Name' ),
                ),
                200
            );
        }

        /**
         * Update CSS rule in the given file (replace block if possible, else append).
         */
        public static function update_css( $request ) {
            $file_url = $request->get_param( 'file' );
            $selector = $request->get_param( 'selector' );
            $oldCSS   = $request->get_param( 'oldCSS' );
            $newCSS   = $request->get_param( 'newCSS' );

            if ( empty( $file_url ) || empty( $selector ) || empty( $newCSS ) ) {
                return new WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => 'Missing file, selector or newCSS',
                    ),
                    400
                );
            }

            // Map URL -> local path.
            $home_url = home_url( '/' );

            if ( strpos( $file_url, $home_url ) !== 0 ) {
                return new WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => 'File URL is not under home_url',
                    ),
                    400
                );
            }

            $relative   = ltrim( str_replace( $home_url, '', $file_url ), '/\\' );
            $file_path  = ABSPATH . $relative;
            $real_path  = realpath( $file_path );
            $contentDir = realpath( WP_CONTENT_DIR );

            if ( ! $real_path || strpos( $real_path, $contentDir ) !== 0 ) {
                return new WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => 'Access denied or invalid path',
                    ),
                    403
                );
            }

            if ( ! file_exists( $real_path ) ) {
                return new WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => 'File not found on disk: ' . $real_path,
                    ),
                    404
                );
            }

            if ( ! is_writable( $real_path ) ) {
                return new WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => 'File is not writable: ' . $real_path,
                    ),
                    500
                );
            }

            $css = file_get_contents( $real_path );
            if ( false === $css ) {
                return new WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => 'Failed to read CSS file',
                    ),
                    500
                );
            }

            // Replace selector block if found.
            $escaped_selector = preg_quote( $selector, '/' );
            $pattern          = '/' . $escaped_selector . '\s*\{[^}]*\}/s';

            $replaced  = false;
            $new_block = $selector . " {\n    " . $newCSS . "\n}";

            $new_css = preg_replace_callback(
                $pattern,
                function ( $matches ) use ( $new_block, &$replaced ) {
                    $replaced = true;
                    return $new_block;
                },
                $css,
                1
            );

            if ( ! $replaced ) {
                // Fallback: append at end.
                $append  = "\n\n/* LIVE CSS SYNC APPEND (" . current_time( 'mysql' ) . ') */' . "\n";
                $append .= $new_block . "\n";
                $new_css = $css . $append;
                $message = 'Selector block not found; appended new block instead.';
            } else {
                $message = 'Selector block replaced successfully.';
            }

            $result = file_put_contents( $real_path, $new_css );

            if ( false === $result ) {
                return new WP_REST_Response(
                    array(
                        'success' => false,
                        'message' => 'Failed to write to CSS file',
                    ),
                    500
                );
            }

            return new WP_REST_Response(
                array(
                    'success' => true,
                    'message' => $message,
                ),
                200
            );
        }
    }
}
