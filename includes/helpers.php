<?php
/**
 * Helpers
 *
 * @package Courier
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

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

	// Return the first matching shipment or null if no match is found.
	return ! empty( $result ) ? $result[0] : null;
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
