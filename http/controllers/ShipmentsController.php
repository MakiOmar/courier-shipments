<?php //phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ShipmentsController {
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
}
