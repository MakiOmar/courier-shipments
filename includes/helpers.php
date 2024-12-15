<?php
/**
 * Helpers
 *
 * @package Courier
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Helper function to insert a record into the shipment_tracking table.
 *
 * @param int    $shipment_id The shipment ID (foreign key).
 * @param int    $employee_id The employee ID (foreign key).
 * @param string $status The shipment status (collected, packaged, processing, shipped, delivered).
 * @param string $description Optional description of the shipment.
 * @return bool|WP_Error True on success, or WP_Error on failure.
 */
function insert_shipment_tracking_record( $shipment_id, $employee_id, $status, $description = '' ) {
	global $wpdb;

	// Validate the status value
	$allowed_statuses = array( 'collected', 'packaged', 'processing', 'shipped', 'delivered' );
	if ( ! in_array( $status, $allowed_statuses, true ) ) {
		return new WP_Error( 'invalid_status', 'Invalid shipment status.' );
	}

	// Prepare the data to insert
	$data = array(
		'shipment_id' => intval( $shipment_id ),
		'employee_id' => intval( $employee_id ),
		'status'      => sanitize_text_field( $status ),
		'description' => sanitize_textarea_field( $description ),
		'created_at'  => current_time( 'mysql' ), // Current timestamp
	);

	// Insert the data into the shipment_tracking table
	$result = $wpdb->insert(
		$wpdb->prefix . 'shipment_tracking', // Table name
		$data, // Data to insert
		array( '%d', '%d', '%s', '%s', '%s' ) // Data format
	);

	// Check if the insert was successful
	if ( $result === false ) {
		return new WP_Error( 'db_error', 'Failed to insert record into shipment_tracking table.' );
	}

	return true;
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
 * Search for a shipment by tracking number.
 *
 * This function queries the `jet_cct_shipment` table for a shipment
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
		->from( 'jet_cct_shipment' )
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

/**
 * Search for a shipment by its ID.
 *
 * This function queries the `jet_cct_shipment` table for a shipment
 * matching the given ID.
 *
 * @param int|string $_ID The ID to search for.
 * @return array|null The matching shipment data, or null if not found.
 */
function courier_search_by_id( $_ID ) {
	// Sanitize the ID to ensure it's safe for the query.
	$_ID = intval( $_ID );

	// Build the query using wp_query_builder().
	$builder = wp_query_builder()
		->select( '*' )
		->from( 'jet_cct_shipment' )
		->where(
			array(
				'_ID' => $_ID,
			)
		);

	// Execute the query and fetch the results.
	$result = $builder->get();

	// Return the first matching shipment or null if no match is found.
	return ! empty( $result ) ? $result[0] : null;
}
