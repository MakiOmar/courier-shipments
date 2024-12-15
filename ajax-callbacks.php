<?php
/**
 * Define custom AJAX calbacks.
 *
 * This file defines and registers AJAX actions' calbacks
 * using the `maglev_ajax_actions` filter.
 *
 * @package WordPress Maglev
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
 * AJAX callback for searching a tracking number.
 *
 * @return void Outputs JSON results or an error message.
 */
function courier_ajax_search_tracking_number() {
	$req = wp_unslash( $_POST );
	// Validate and sanitize the tracking number.
	$tracking_number = isset( $req['tracking_number'] ) ? sanitize_text_field( $req['tracking_number'] ) : '';
	check_ajax_referer();
	if ( empty( $tracking_number ) ) {
		wp_send_json_error( array( 'message' => 'Tracking number is required.' ) );
	}

	// Search for the shipment.
	$shipment = courier_search_tracking_number( $tracking_number );
	if ( $shipment ) {
		wp_send_json_success( $shipment );
	} else {
		wp_send_json_error( array( 'message' => 'No shipment found for this tracking number.' ) );
	}
}
