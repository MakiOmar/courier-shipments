<?php
/**
 * Plugin Name: Courier shipments
 * Plugin URI: https://example.com/my-basic-plugin
 * Description: Courier shipments management.
 * Version: 1.0.0
 * Author: Mohammad Omar
 * Author URI: https://makiomar.com
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: coursh
 * Domain Path: /languages
 *
 * @package MyBasicPlugin
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Plugin version.
define( 'COURSH_VERSION', '1.0.0' );

// Plugin directory path.
define( 'COURSH_PATH', plugin_dir_path( __FILE__ ) );

// Plugin URL.
define( 'COURSH_URL', plugin_dir_url( __FILE__ ) );

// Plugin basename.
define( 'COURSH_BASENAME', plugin_basename( __FILE__ ) );

require_once COURSH_PATH . 'vendor/autoload.php';
require_once COURSH_PATH . 'autoload.php';
require_once COURSH_PATH . 'includes/database.php';
require_once COURSH_PATH . 'includes/helpers.php';
require_once COURSH_PATH . 'includes/salary-management.php';
require_once COURSH_PATH . 'includes/hooks.php';
require_once COURSH_PATH . 'ajax-actions.php';
require_once COURSH_PATH . 'ajax-callbacks.php';
require_once COURSH_PATH . 'shortcode.php';
require_once COURSH_PATH . 'htmx-after-response.php';

define( 'COURSH_LOGO', get_theme_mods_child_key( 'jupiterx_logo_secondary' ) );

/**
* Plugin activation hook
*
* @return void
*/
register_activation_hook(
	__FILE__,
	function () {
		create_shipment_tracking_table();
		create_salary_management_tables();
	}
);

/**
 * Main plugin bootstrap function.
 *
 * This function initializes the plugin.
 *
 * @return void
 */
function coursh_init() {
	// Load text domain for translations.
	load_plugin_textdomain( 'coursh', false, dirname( COURSH_BASENAME ) . '/languages' );

	// Additional initialization logic here.
}
add_action( 'init', 'coursh_init' );

/**
 * Enqueue plugin scripts and styles.
 *
 * @return void
 */
function coursh_enqueue_scripts() {
}
add_action( 'wp_enqueue_scripts', 'coursh_enqueue_scripts' );

/**
 * Debug helper function to log data using error_log.
 *
 * This function logs data into the error log file for debugging purposes.
 * It uses print_r to format arrays and objects.
 *
 * @param mixed  $data      The data to log (string, array, object, etc.).
 * @param string $log_label Optional. A label to identify the log entry. Default 'Debug'.
 * @return void
 */
function debug_log( $data, $log_label = 'Debug' ) {
	if ( is_array( $data ) || is_object( $data ) ) {
		$data = print_r( $data, true ); // Convert arrays/objects to string.
	}

	$message = sprintf(
		'[%s] %s: %s',
		gmdate( 'Y-m-d H:i:s' ), // Timestamp.
		$log_label,           // Custom label.
		$data                 // Debug data.
	);

	error_log( $message ); // Log the formatted message.
}

/**
 * Helper function to format a number to exactly 11 digits by appending an auto-generated number.
 *
 * This function ensures the main number is placed at the beginning and appends
 * an automatically generated number to complete the total length to 11 digits.
 *
 * @param int|string $main_number The main number that should be at the beginning.
 * @return string The resulting 11-digit number.
 */
function format_to_eleven_digits( $main_number ) {
	// Ensure the main number is a string.
	$main_number = (string) $main_number;

	// Calculate the remaining length needed to make it 11 digits.
	$remaining_length = 11 - strlen( $main_number );

	// If the main number already exceeds 11 digits, truncate it.
	if ( $remaining_length < 0 ) {
		$main_number      = substr( $main_number, 0, 11 );
		$remaining_length = 0;
	}

	// Generate a random number with the required length.
	$append_number = $remaining_length > 0
		? wp_rand( pow( 10, $remaining_length - 1 ), pow( 10, $remaining_length ) - 1 )
		: '';

	// Return the formatted number.
	return $main_number . substr( $append_number, 0, $remaining_length );
}

add_action(
	'jet-form-builder/custom-action/after_insert_cct',
	function ( $request ) {
		// Check if the inserted CCT shipment ID is provided.
		if ( ! empty( $request['inserted_cct_shipments'] ) ) {
			snks_generate_tracking_number( $request['inserted_cct_shipments'] );
		} else {
			debug_log( 'No CCT Shipment ID provided.' );
		}
	}
);
