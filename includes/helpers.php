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

/**
 * Search for a shipment by tracking number.
 *
 * This function queries the `jet_cct_shipments` table for a shipment
 * matching the given tracking number.
 *
 * @param string $tracking_number The tracking number to search for.
 * @return array|null The matching shipment data, or null if not found.
 */
function courier_search_tracking_number( $tracking_number ) {
	// Sanitize the tracking number.
	$tracking_number = sanitize_text_field( $tracking_number );

	// Build the query using wp_query_builder().
	$builder = wp_query_builder()
		->select( '*' )
		->from( 'jet_cct_shipments' )
		->where(
			array(
				'tracking_number' => $tracking_number,
			)
		);

	// Execute the query and fetch the results.
	$result = $builder->get();
	if ( ! empty( $result ) ) {

		$_return = $result[0];

		// Unset unwanted object properties.
		unset( $_return->cct_status );
		unset( $_return->terms );
		unset( $_return->cct_modified );

		// Get and replace the author property.
		$author = $_return->cct_author_id;
		unset( $_return->cct_author_id );

		// Add the client name property.
		$_return->client = get_user_full_name( $author );

		return array(
			'ID'              => $_return->_ID,
			'Tracking number' => $_return->tracking_number,
			'Client name'     => $_return->client,
			'Receive rname'   => $_return->receivername,
			'Receive Address' => $_return->receiveraddress,
			'Receive country' => $_return->receivercountry,
			'Receive city'    => $_return->receivercity,
			'Receive phone'   => $_return->receiverphone,
			'Total weight'    => $_return->totalweight,
			'Unit weight'     => $_return->unitweight,
			'Description'     => $_return->contentdescription,
			'Created at'      => $_return->cct_created,
		);
	}

	return null;
}
