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

		if ( $existing_record ) {
			// Update the description if the record exists.
			$updated = $wpdb->update(
				$table_name,
				array( 'description' => $description ), // Fields to update.
				array( 'id' => $existing_record->id ),  // WHERE clause.
				array( '%s' ), // Value format.
				array( '%d' )  // Where format.
			);

			if ( false === $updated ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Failed to update the record.', 'coursh' ) ) );
			}

			wp_send_json_success( array( 'message' => esc_html__( 'Record updated successfully.', 'coursh' ) ) );
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
				wp_send_json_error( array( 'message' => esc_html__( 'Failed to insert the record.', 'coursh' ) ) );
			}

			wp_send_json_success( array( 'message' => esc_html__( 'Record inserted successfully.', 'coursh' ) ) );
		}
	}
	/**
	 * AJAX callback for searching a tracking number.
	 *
	 * @return void Outputs JSON results or an error message.
	 */
	public function courier_ajax_search_tracking_number() {
		$req = wp_unslash( $_POST );

		// Validate and sanitize the tracking number.
		$tracking_number = isset( $req['tracking_number'] ) ? sanitize_text_field( $req['tracking_number'] ) : '';

		check_ajax_referer();

		if ( empty( $tracking_number ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Tracking number is required.', 'coursh' ) ) );
		}

		// Search for the shipment.
		$shipment = $this->courier_search_tracking_number( $tracking_number );
		if ( $shipment ) {
			wp_send_json_success( $shipment );
		} else {
			wp_send_json_error( array( 'message' => esc_html__( 'No shipment found for this tracking number.', 'coursh' ) ) );
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
	public function courier_search_tracking_number( $tracking_number ) {
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
		$tracking_info = tracking_details( $tracking_number );
		if ( ! empty( $tracking_info ) ) {
			ob_start();
			load_view(
				'tracking',
				array(
					'trackings'       => $tracking_info,
					'tracking_number' => $tracking_number,
				)
			);
			$html = ob_get_clean();
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
}
