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
 * AJAX callback to insert or update a shipment tracking record.
 */
function ajax_insert_shipment_tracking() {
	// Verify the nonce for security.
	check_ajax_referer();

	// Retrieve and sanitize input data.
	$shipment_id = isset( $_POST['shipment_id'] ) ? intval( $_POST['shipment_id'] ) : null;
	$employee_id = get_current_user_id();
	$status      = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
	$description = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';

	// Validate required fields.
	if ( empty( $shipment_id ) || empty( $employee_id ) || empty( $status ) ) {
		wp_send_json_error(
			array( 'message' => esc_html__( 'Required fields are missing.', 'coursh' ) )
		);
	}

	// Check if a record already exists with the given shipment_id and status.
	global $wpdb;
	$table_name = $wpdb->prefix . 'shipment_tracking'; // Replace with your actual table name.

	$existing_record = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT id FROM $table_name WHERE shipment_id = %d AND status = %s",
			$shipment_id,
			$status
		)
	);
	error_log( print_r( $existing_record, true ) );
	if ( $existing_record ) {
		// Update the description if the record exists.
		$updated = $wpdb->update(
			$table_name,
			array( 'description' => $description ), // Fields to update.
			array( 'id' => $existing_record->id ), // WHERE clause.
			array( '%s' ), // Value format.
			array( '%d' ) // Where format.
		);

		if ( false === $updated ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Failed to update the record.', 'coursh' ) )
			);
		}

		wp_send_json_success(
			array( 'message' => esc_html__( 'Record updated successfully.', 'coursh' ) )
		);
	} else {
		// Insert a new record if it doesn't exist.
		$inserted = $wpdb->insert(
			$table_name,
			array(
				'shipment_id' => $shipment_id,
				'employee_id' => $employee_id,
				'status'      => $status,
				'description' => $description,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			wp_send_json_error(
				array( 'message' => esc_html__( 'Failed to insert the record.', 'coursh' ) )
			);
		}

		wp_send_json_success(
			array( 'message' => esc_html__( 'Record inserted successfully.', 'coursh' ) )
		);
	}
}

/**
 * AJAX handler to get tracking details.
 */
function get_tracking_details_ajax() {
	// Check for the tracking number in the request.
	$tracking_number = isset( $_POST['tracking_number'] ) ? sanitize_text_field( $_POST['tracking_number'] ) : '';

	if ( empty( $tracking_number ) ) {
		wp_send_json_error( array( 'message' => 'Tracking number is required.' ) );
		wp_die();
	}

	// Call the tracking_details function.
	$tracking_info = tracking_details( $tracking_number );

	if ( ! empty( $tracking_info ) ) {
		unset( $tracking_info->id );
		// Send success response.
		wp_send_json_success( array( 'tracking_info' => $tracking_info ) );
	} else {
		// Send error response.
		wp_send_json_error( array( 'message' => 'No tracking details found for the given tracking number.' ) );
	}

	die(); // Ensure the request is terminated properly.
}
