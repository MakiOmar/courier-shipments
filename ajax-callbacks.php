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


// Handle bulk action with AJAX

function coursh_bulk_print_qr() {
	if ( ! current_user_can( 'administrator' ) ) {
		wp_send_json_error( array( 'message' => esc_html( 'Forbidden!', 'coursh' ) ) );
		return;
	}
	$shipment_ids = isset( $_POST['shipment_ids'] ) ? array_map( 'intval', $_POST['shipment_ids'] ) : array();

	if ( empty( $shipment_ids ) ) {
		wp_send_json_error( array( 'message' => 'لم يتم تحديد أي طلبات.' ) );
	}

	$shipments_data = array();
	foreach ( $shipment_ids as $shipment_id ) {
		$shipment = courier_search_by_id( $shipment_id );
		if ( ! $shipment ) {
			continue;
		}

		$shipments_data[] = array();
	}

	wp_send_json_success( $orders_data );
}
