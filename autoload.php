<?php
/**
 * Autoloader for custom classes in the theme.
 *
 * This function registers an autoloader for loading classes from specified directories.
 *
 * @package SurvTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

spl_autoload_register(
	/**
	 * Autoload custom classes.
	 *
	 * This function autoloads classes from the specified base directories
	 * by converting the namespace to the file path and including the file.
	 *
	 * @param string $class The fully qualified class name.
	 *
	 * @return void
	 */
	function ( $class ) {
		// Define the base directories for autoloading.
		$base_dirs = array(
			COURSH_PATH . '/http/controllers/',
			COURSH_PATH . '/http/models/',
		);

		// Replace namespace separators with directory separators and append .php.
		$relative_path = str_replace( '\\', '/', $class ) . '.php';

		// Loop through base directories to find and include the file.
		foreach ( $base_dirs as $base_dir ) {
			$file = $base_dir . $relative_path;

			if ( file_exists( $file ) ) {
				require_once $file; // Include the class file.
				break; // Stop searching once the file is found.
			}
		}
	}
);
