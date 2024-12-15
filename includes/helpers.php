<?php
/**
 * Helpers
 *
 * @package Courier
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

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
		unset( $_return['_ID'] );
		unset( $_return['cct_status'] );
		unset( $_return['terms'] );
		unset( $_return['cct_modified'] );
		$uthor = $_return['cct_author_id'];
		unset( $_return['cct_author_id'] );
		$_return['client'] = get_user_full_name( $uthor );
		return $_return;
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
