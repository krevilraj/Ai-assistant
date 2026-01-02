<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    AI_Assistant
 * @subpackage AI_Assistant/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    AI_Assistant
 * @subpackage AI_Assistant/includes
 * @author     Your Name <email@example.com>
 */

class AI_Assistant_Activator {

    public static function activate() {

        // plugin root directory (because this file is /includes/)
        $plugin_root = plugin_dir_path( dirname( __FILE__ ) );

        // source: your-plugin/plugin_files/
        $source_dir = trailingslashit( $plugin_root . 'plugin_files' );

        // destination: wp-content/plugins/
        $dest_dir   = trailingslashit( WP_PLUGIN_DIR );

        // If folder doesn't exist, show notice (but DO NOT break activation)
        if ( ! is_dir( $source_dir ) ) {
            update_option( 'ai_assistant_copy_notice', 'Missing folder: ' . $source_dir );
            return;
        }

        // Copy contents
        $overwrite = false; // change to true if you want to overwrite existing files
        $result = self::recursive_copy( $source_dir, $dest_dir, $overwrite );

        if ( $result !== true ) {
            update_option( 'ai_assistant_copy_notice', $result );
        } else {
            delete_option( 'ai_assistant_copy_notice' );
        }
    }

    /**
     * Recursively copy $src directory contents into $dst.
     * If $overwrite is false, existing files are skipped.
     *
     * @return true|string True on success, or error message string.
     */
    private static function recursive_copy( $src, $dst, $overwrite = false ) {
        $src = trailingslashit( $src );
        $dst = trailingslashit( $dst );

        if ( ! is_dir( $src ) ) {
            return "Source directory not found: {$src}";
        }

        if ( ! is_dir( $dst ) && ! wp_mkdir_p( $dst ) ) {
            return "Failed to create destination directory: {$dst}";
        }

        $dir = opendir( $src );
        if ( ! $dir ) {
            return "Unable to open source directory: {$src}";
        }

        while ( false !== ( $file = readdir( $dir ) ) ) {
            if ( $file === '.' || $file === '..' ) {
                continue;
            }

            $src_path = $src . $file;
            $dst_path = $dst . $file;

            if ( is_dir( $src_path ) ) {
                if ( ! is_dir( $dst_path ) && ! wp_mkdir_p( $dst_path ) ) {
                    closedir( $dir );
                    return "Failed to create directory: {$dst_path}";
                }

                $res = self::recursive_copy( $src_path, $dst_path, $overwrite );
                if ( $res !== true ) {
                    closedir( $dir );
                    return $res;
                }
            } else {
                if ( ! $overwrite && file_exists( $dst_path ) ) {
                    continue;
                }

                $parent = dirname( $dst_path );
                if ( ! is_dir( $parent ) && ! wp_mkdir_p( $parent ) ) {
                    closedir( $dir );
                    return "Failed to create parent directory: {$parent}";
                }

                if ( ! @copy( $src_path, $dst_path ) ) {
                    closedir( $dir );
                    return "Failed to copy file: {$src_path} → {$dst_path}";
                }
            }
        }

        closedir( $dir );
        return true;
    }
}

