<?php
/**
 * Helpers
 *
 * @package Courier
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Load a view file and pass data to it.
 *
 * @param string $view The name of the view file (without `.php` extension).
 * @param array  $data The data to pass to the view.
 *
 * @return void Includes the view file with data.
 */
function load_view( $view, $data = array() ) {
	$view_path = COURSH_PATH . '/views/' . $view . '.php'; // Adjust the path to your views directory.

	if ( file_exists( $view_path ) ) {
		//phpcs:disable
		extract( $data ); // Extract the data array into variables.
		//phpcs:enable
		include $view_path;
	} else {
		echo '<p>Error: View file not found.</p>';
	}
}

/**
 * Get the full name of a WordPress user by their ID.
 *
 * This function retrieves the user's first name and last name from the user meta
 * and combines them to form the full name.
 *
 * @param int $user_id The ID of the user.
 * @return string The full name of the user, or an empty string if not available.
 */
function get_user_full_name( $user_id ) {
	// Validate and sanitize the user ID.
	$user_id = absint( $user_id );

	if ( ! $user_id ) {
		return '';
	}

	// Get the user's first and last names from meta.
	$first_name = get_user_meta( $user_id, 'first_name', true );
	$last_name  = get_user_meta( $user_id, 'last_name', true );

	// Combine first and last name into a full name.
	$full_name = trim( $first_name . ' ' . $last_name );

	return $full_name;
}


/**
 * Retrieve a specific key value from the `theme_mods_jupiterx-child` option.
 *
 * This function queries the WordPress options table using $wpdb
 * to fetch a specific key's value from the `theme_mods_jupiterx-child` option.
 *
 * @param string $key The key to retrieve from the theme_mods_jupiterx-child option.
 * @return mixed|null The value of the specified key, or null if the key or option does not exist.
 */
function get_theme_mods_child_key( $key ) {
	global $wpdb;

	// Sanitize the key input.
	$key = sanitize_key( $key );

	// Define the option name to query.
	$option_name = 'theme_mods_jupiterx-child';

	// Query the option value from the database.
	$option_value = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
			$option_name
		)
	);

	// Check if the option exists.
	if ( ! $option_value ) {
		return '';
	}

	// Unserialize the option value.
	$option_data = maybe_unserialize( $option_value );

	// Check if the key exists in the unserialized array and return its value.
	return isset( $option_data[ $key ] ) ? $option_data[ $key ] : '';
}
/**
 * Insert tracking number
 *
 * @param int|string $id Shipment ID.
 * @return void
 */
function snks_generate_tracking_number( $id ) {
	// Format the tracking number to exactly 11 digits.
	$tr = format_to_eleven_digits( $id );

	// Use wp_query_builder() to perform the database operation.
	$builder = wp_query_builder();

	try {
		$builder->from( 'jet_cct_shipments' )
				->set( array( 'tracking_number' => $tr ) )
				->where( array( '_ID' => $id ) )
				->update();
	} catch ( Exception $e ) {
		// Log any errors for debugging purposes.
		debug_log( 'Error updating tracking number: ' . $e->getMessage() );
	}
}
