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
		wp_send_json_error( array( 'message' => esc_html__( 'Tracking number is required.', 'coursh' ) ) );
	}

	// Search for the shipment.
	$shipment = courier_search_tracking_number( $tracking_number );
	if ( $shipment ) {
		wp_send_json_success( $shipment );
	} else {
		wp_send_json_error( array( 'message' => esc_html__( 'No shipment found for this tracking number.', 'coursh' ) ) );
	}
}


// Handle bulk action with AJAX.

function coursh_bulk_print_qr() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => esc_html( 'Forbidden!', 'coursh' ) ) );
		return;
	}
	$shipment_ids = isset( $_POST['shipment_ids'] ) ? array_map( 'intval', $_POST['shipment_ids'] ) : array();

	if ( empty( $shipment_ids ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'No shipment is selected', 'coursh' ) ) );
	}

	$shipments_data = array();
	foreach ( $shipment_ids as $shipment_id ) {
		$shipment = courier_search_by_id( $shipment_id );
		if ( ! $shipment ) {
			continue;
		}

		$shipments_data[] = $shipment;
	}

	wp_send_json_success( $shipments_data );
}

/**
 * AJAX callback to insert a shipment tracking record.
 */
function ajax_insert_shipment_tracking() {
	// Verify the nonce for security
	check_ajax_referer();

	// Retrieve and sanitize input data
	$shipment_id = isset( $_POST['shipment_id'] ) ? intval( $_POST['shipment_id'] ) : null;
	$employee_id = isset( $_POST['employee_id'] ) ? intval( $_POST['employee_id'] ) : null;
	$status      = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
	$description = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';

	// Validate required fields
	if ( empty( $shipment_id ) || empty( $employee_id ) || empty( $status ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Required fields are missing.', 'coursh' ) ) );
	}

	// Use the helper function to insert the record
	$result = insert_shipment_tracking_record( $shipment_id, $employee_id, $status, $description );

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	}

	wp_send_json_success( array( 'message' => esc_html__( 'Record inserted successfully.', 'coursh' ) ) );
}
