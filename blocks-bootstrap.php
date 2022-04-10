<?php
/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @file    wp-content/themes/x/source/gutenberg/blocks-bootstrap.php
 * @package Theme\Gutenberg\Blocks
 */

add_action( 'init', 'register_theme_gutenberg_blocks' );
/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function register_theme_gutenberg_blocks() {

	// Gather all of the block directory paths relative to this directory.
	$blocks = glob( __DIR__ . DIRECTORY_SEPARATOR . 'blocks/*' . DIRECTORY_SEPARATOR );

	if ( $blocks ) {
		foreach ( $blocks as $block_type ) {

			$block_json_path = $block_type . 'block.json';

			/*
			 * If this is local development environment, check if the block.json exits and deliver a wp_die message.
			 * Requires the following constants being defined within wp-config.php or the like;
			 * define( 'WP_ENVIRONMENT_TYPE', 'local' );
			 * define( 'WP_DEBUG_DISPLAY', true );
			 */
			if ( ! file_exists( $block_json_path ) &&
				function_exists( 'wp_get_environment_type' ) &&
				'local' === wp_get_environment_type() &&
				defined( 'WP_DEBUG_DISPLAY' ) &&
				WP_DEBUG_DISPLAY ) {

				// Local environment error.
				wp_die( sprintf( 'Block json file missing for block directory %s.', $block_type ) ); // phpcs:ignore
			}

			$args_path = $block_type . 'args.php';

			if ( file_exists( $args_path ) ) {
				$args = include_once $args_path;
			} else {
				$args = [];
			}

			/**
			 * Variable Type Definition.
			 *
			 * @param string|WP_Block_Type $block_type A path to the folder where the `block.json` file is located.
			 * @param array                $args       Optional. Array of block type arguments. Accepts any public property
			 *                                         of `WP_Block_Type`. See WP_Block_Type::__construct() for information
			 *                                         on accepted arguments. Default empty array.
			 */
			register_block_type( $block_type, $args );
		}
	}
}

add_filter( 'plugins_url', 'filter_theme_gutenberg_block_urls', 10, 3 );
/**
 * WodPress is designed to point the at the plugin directory for block asset urls. This filter
 * replaces the url path with the correct path to theme block assets.
 *
 * See wp-includes/blocks.php line 110, WP 5.9.3
 *
 * @param string $url    The complete URL to the plugins directory including scheme and path.
 * @param string $path   Path relative to the URL to the plugins directory. Blank string if no path is specified.
 * @param string $plugin The plugin file path to be relative to. Blank string if no plugin is specified.
 *
 * @return string
 */
function filter_theme_gutenberg_block_urls( $url, $path, $plugin ) {

	$theme_dir = get_stylesheet_directory();

	// Bail early, if the $url param is incorrect, or the theme directory is not within the URL.
	if ( ! $url || false === strpos( $url, $theme_dir ) ) {
		return $url;
	}

	// Bail early, if the $plugin param is incorrect, or the block.json file is not within $plugin var.
	if ( ! $plugin || false === strpos( $plugin, 'block.json' ) ) {
		return $url;
	}

	// Get the block path from the $plugin string, by removing the "block.json" part.
	$block_path = str_replace( 'block.json', '', $plugin );

	// Gather all of the block directory paths relative to this directory.
	$blocks = glob( __DIR__ . DIRECTORY_SEPARATOR . 'blocks/*' . DIRECTORY_SEPARATOR );

	// Check for matching paths.
	$matches = array_find_partial_match( $blocks, $block_path );

	// Bail early, if no matches found.
	if ( ! $matches ) {
		return $url;
	}

	// This replicates the incorrect string component.
	$part_to_remove = 'wp-content/plugins' . ABSPATH;

	// This will replace the path to plugin so the url leads to the theme.
	return str_replace( $part_to_remove, '', $url );
}
