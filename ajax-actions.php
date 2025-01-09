<?php
/**
 * Register custom AJAX actions.
 *
 * This file defines and registers AJAX actions that can be extended
 * using the `maglev_ajax_actions` filter.
 *
 * @package WordPress Maglev
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action(
	'plugins_loaded',
	function () {
		add_filter(
			'maglev_ajax_actions',
			function ( $events ) {
				$events['qr_search_tracking_number'] = array(
					'callback'       => array( 'ShipmentsController', 'courier_ajax_search_tracking_number' ),
					'logged_in_only' => true, // Set to false for both.
				);

				$events['coursh_bulk_print_qr']     = array(
					'callback'       => array( 'ShipmentsController', 'coursh_bulk_print_qr' ),
					'logged_in_only' => true, // Set to false for both.
				);
				$events['insert_shipment_tracking'] = array(
					'callback'       => array( 'ShipmentsController', 'ajax_insert_shipment_tracking' ),
					'logged_in_only' => true, // Set to false for both.
				);

				$events['get_tracking_details'] = array(
					'callback'       => array( 'ShipmentsController', 'get_tracking_details_ajax' ),
					'logged_in_only' => false, // Set to false for both.
				);
				return $events;
			}
		);
	},
	5
);
