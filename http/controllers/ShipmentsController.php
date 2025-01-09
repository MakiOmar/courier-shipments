<?php //phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ShipmentsController {

	/**
	 * AJAX callback to insert or update a shipment tracking record.
	 *
	 * @return void Outputs JSON results or an error message.
	 */
	public static function ajax_insert_shipment_tracking() {
		// Verify the nonce for security.
		check_ajax_referer();

		// Retrieve and sanitize input data.
		$shipment_id = isset( $_POST['shipment_id'] ) ? intval( $_POST['shipment_id'] ) : null;
		$employee_id = get_current_user_id();
		$status      = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';

		// Validate required fields.
		if ( empty( $shipment_id ) || empty( $employee_id ) || empty( $status ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Required fields are missing.', 'coursh' ) ) );
		}

		try {
			// Check if a record already exists with the given shipment_id and status.
			$existing_record = ShipmentTracking::where( 'shipment_id', $shipment_id )
			->where( 'status', $status )
			->first();

			if ( $existing_record ) {
				// Update the description if the record exists.
				$existing_record->description = $description;
				$existing_record->save();

				wp_send_json_success( array( 'message' => esc_html__( 'Record updated successfully.', 'coursh' ) ) );
			} else {
				// Insert a new record if it doesn't exist.
				ShipmentTracking::create(
					array(
						'shipment_id' => $shipment_id,
						'employee_id' => $employee_id,
						'status'      => $status,
						'description' => $description,
						'created_at'  => now(),
					)
				);

				wp_send_json_success( array( 'message' => esc_html__( 'Record inserted successfully.', 'coursh' ) ) );
			}
		} catch ( Exception $e ) {
			// Handle exceptions gracefully.
			error_log( 'Error in shipment tracking insertion: ' . $e->getMessage() );
			wp_send_json_error( array( 'message' => esc_html__( 'An error occurred while processing your request.', 'coursh' ) ) );
		}
	}

	/**
	 * AJAX callback for searching a tracking number.
	 *
	 * @return void Outputs JSON results or an error message.
	 */
	public static function courier_ajax_search_tracking_number() {
		$req = wp_unslash( $_POST );

		// Validate and sanitize the tracking number.
		$tracking_number = isset( $req['tracking_number'] ) ? sanitize_text_field( $req['tracking_number'] ) : '';

		check_ajax_referer();

		if ( empty( $tracking_number ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Tracking number is required.', 'coursh' ) ) );
		}

		// Search for the shipment.
		$shipment = self::courier_search_tracking_number( $tracking_number );
		if ( $shipment ) {
			wp_send_json_success( $shipment );
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'No shipment found for this tracking number.', 'coursh' ) ) );
		}
	}

	/**
	 * Search for a shipment by tracking number using the Shipment model.
	 *
	 * @param string $tracking_number The tracking number to search for.
	 * @return array|null The matching shipment data, or null if not found.
	 */
	public static function courier_search_tracking_number( $tracking_number ) {
		try {
			// Query the Shipment model by tracking number.
			$shipment = Shipment::where( 'tracking_number', $tracking_number )->first();

			if ( $shipment ) {
				// Get the author's full name.
				$author_name = get_user_full_name( $shipment->cct_author_id );

				// Prepare the result array.
				return array(
					'ID'               => $shipment->_ID,
					'Tracking number'  => $shipment->tracking_number,
					'Client name'      => $author_name,
					'Receiver name'    => $shipment->receivername,
					'Receiver address' => $shipment->receiveraddress,
					'Receiver country' => $shipment->receivercountry,
					'Receiver city'    => $shipment->receivercity,
					'Receiver phone'   => $shipment->receiverphone,
					'Total weight'     => $shipment->totalweight,
					'Unit weight'      => $shipment->unitweight,
					'Description'      => $shipment->contentdescription,
					'Created at'       => $shipment->cct_created,
				);
			}

			return null;
		} catch ( Exception $e ) {
			// Handle exceptions gracefully.
			error_log( 'Error fetching shipment: ' . $e->getMessage() );
			return null;
		}
	}


	/**
	 * Get tracking details using a tracking number.
	 *
	 * @param string $tracking_number The tracking number to search for.
	 * @return object|null The tracking details or null if not found.
	 */
	public static function tracking_details( $tracking_number ) {
		// Ensure the tracking number is provided.
		if ( empty( $tracking_number ) ) {
			return null;
		}

		try {
			// Query the Shipment model using the tracking number.
			$shipment = Shipment::with( 'trackingDetails' )
				->where( 'tracking_number', $tracking_number )
				->first();

			return $shipment;

		} catch ( Exception $e ) {
			// Handle exceptions gracefully.
			error_log( 'Error fetching tracking details: ' . $e->getMessage() );
			return null;
		}
	}

	/**
	 * AJAX handler to get tracking details.
	 *
	 * @return void Outputs JSON results or an error message.
	 */
	public static function get_tracking_details_ajax() {
		// Check for the tracking number in the request.
		$tracking_number = isset( $_POST['tracking_number'] ) ? sanitize_text_field( $_POST['tracking_number'] ) : '';

		if ( empty( $tracking_number ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Tracking number is required.', 'coursh' ) ) );
			wp_die();
		}

		// Call the tracking_details function.
		$shipment = self::tracking_details( $tracking_number );
		if ( $shipment ) {
			if ( ! empty( $shipment->trackingDetails->toArray() ) ) {
				ob_start();
				load_view(
					'tracking',
					array(
						'trackings'       => $shipment->trackingDetails->toArray(),
						'tracking_number' => $tracking_number,
					)
				);
				$html = ob_get_clean();
			} else {
				$html = esc_html__( 'Your shipment item has been created. Please wait for our agent to collect it.' );
			}

			// Send success response.
			wp_send_json_success( array( 'html' => $html ) );
		} else {
			// Send error response.
			wp_send_json_error( array( 'message' => esc_html__( 'No tracking details found for the given tracking number.', 'coursh' ) ) );
		}

		wp_die(); // Ensure the request is terminated properly.
	}

	/**
	 * Handle bulk action with AJAX.
	 *
	 * @return void Outputs JSON results or an error message.
	 */
	public static function coursh_bulk_print_qr() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Forbidden!', 'coursh' ) ) );
			return;
		}

		$shipment_ids = isset( $_POST['shipment_ids'] ) ? array_map( 'intval', $_POST['shipment_ids'] ) : array();

		if ( empty( $shipment_ids ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'No shipment is selected.', 'coursh' ) ) );
		}

		$html = '';
		foreach ( $shipment_ids as $shipment_id ) {
			$shipment = self::courier_search_by_id( $shipment_id );
			if ( ! $shipment ) {
				continue;
			}
            error_log( print_r( $shipment, true ) );
			ob_start();
			load_view( 'print', array( 'shipment' => $shipment ) );
			$html .= ob_get_clean();
		}

		wp_send_json_success( $html );
	}

	/**
	 * Search for a shipment by its ID.
	 *
	 * This function queries the `jet_cct_shipments` table for a shipment
	 * matching the given ID.
	 *
	 * @param int|string $_ID The ID to search for.
	 * @return Shipment|null The matching shipment model instance, or null if not found.
	 */
	public static function courier_search_by_id( $_ID ) {
		// Sanitize the ID to ensure it's a valid integer.
		$_ID = intval( $_ID );

		try {
			// Use the Shipment model to find the shipment by ID.
			$shipment = Shipment::find( $_ID );

			// Return the shipment instance or null if not found.
			return $shipment;
		} catch ( Exception $e ) {
			// Log any errors and return null.
			error_log( 'Error searching for shipment by ID: ' . $e->getMessage() );
			return null;
		}
	}
}
