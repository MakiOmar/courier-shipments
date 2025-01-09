<?php
/**
 * Shortcodes
 *
 * @package Courier shipment
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Shortcode to display the tracking search form.
 *
 * Generates an input field, a button using htmx, and a container for results.
 *
 * @return string The HTML output for the tracking search form.
 */

add_shortcode(
	'courier_tracking_search',
	function () {
		ob_start(); // Start output buffering to capture the HTML.
		?>
	<div id="tracking-container" style="display:flex;justify-content:center">
		<!-- HTMX Form -->
		<form
			hx-post="<?php echo esc_url( admin_url( 'admin-ajax.php?action=get_tracking_details' ) ); ?>" 
			hx-target="#client-tracking-details" 
			hx-indicator="#maglev-loading-indicator" 
			style="display: flex; justify-content: center; align-items: center;"
		>
			<!-- Input Field for Tracking Number -->
			<input 
				type="text" 
				id="tracking-number" 
				name="tracking_number" 
				placeholder="Enter tracking number" 
				style="padding:10px; margin: 0 5px;" 
			/>
			
			<!-- WP Nonce Field -->
			<?php wp_nonce_field(); ?>

			<!-- Submit Button -->
			<button 
				type="submit" 
				style="background-color: #f15f22; color: #fff; border: 1px solid #f15f22; border-radius: 3px; padding: 10px; width: 100px;"
			>
				<?php esc_html_e( 'Search', 'coursh' ); ?>
			</button>
		</form>

		<!-- Result Container -->
		<div id="client-tracking-details" style="display:none" hx-no-swal="true"></div>
	</div>

		<?php
		return ob_get_clean(); // Return the captured HTML as the shortcode output.
	}
);
add_shortcode(
	'courier_qr',
	function () {
		ob_start();
		?>
		<div class="container mt-5 d-flex justify-content-center">
			<div class="card overflow-hidden" style="border: 2px solid gold; border-radius: 15px;">
				<h4 class="card-title text-center mb-4 p-2" style="background-color: #f15f22;color: #fff;text-align:center"><?php esc_html_e( 'Shipment details', 'coursh' ); ?></h4>
				<div class="card-body">
					<form
					id="shipment-tracking-form"
					hx-post="<?php echo esc_url( admin_url( 'admin-ajax.php?action=qr_search_tracking_number' ) ); ?>"
					hx-target="#tracking-result"
					hx-indicator="#maglev-loading-indicator"
					>
						<div class="mb-3">
							<label for="tracking_number" class="form-label"><?php esc_html_e( 'Tracking number', 'coursh' ); ?></label>
							<input type="text" class="form-control" id="tracking_number" name="tracking_number" required>
							<button type="button" class="btn btn-secondary mb-3" onclick="startQrCodeScanner()"><?php esc_html_e( 'Scan QR', 'coursh' ); ?></button>
							<div id="qr-reader" style="width: 100%; display: none;"></div>
						</div>
						<!-- WP Nonce Field -->
						<?php wp_nonce_field(); ?>
						<div class="text-center">
							<button type="submit" class="btn" style="background-color:#361347;color:#fff"><?php esc_html_e( 'Get details', 'coursh' ); ?></button>
						</div>
					</form>
					<!-- Result Container -->
					<div id="tracking-result" style="display:none"></div>
				</div>
			</div>
		</div>
		<form
			id="employee-actions-form"
			hx-post="<?php echo esc_url( admin_url( 'admin-ajax.php?action=insert_shipment_tracking' ) ); ?>" 
			hx-trigger="submit" 
			hx-target="#employee-actions" 
			hx-indicator="#maglev-loading-indicator"
			hx-swap="innerHTML"
			style="display:none"
		>
			<!-- WP Nonce Field -->
			<?php wp_nonce_field(); ?>

			<div class="mb-3">
				<!-- Shipment ID -->
				<input type="number" id="shipment_id" name="shipment_id" class="form-control d-none" required>
			</div>
			<div class="mb-3">
				<!-- Status -->
				<label for="status" class="form-label"><?php esc_html_e( 'Status:', 'coursh' ); ?></label>
				<select id="status" name="status" class="form-select" required>
					<option value="collected"><?php esc_html_e( 'Collected', 'coursh' ); ?></option>
					<option value="packaged"><?php esc_html_e( 'Packaged', 'coursh' ); ?></option>
					<option value="processing"><?php esc_html_e( 'Processing', 'coursh' ); ?></option>
					<option value="shipped"><?php esc_html_e( 'Shipped', 'coursh' ); ?></option>
					<option value="delivered"><?php esc_html_e( 'Delivered', 'coursh' ); ?></option>
				</select>
			</div>

			<div class="mb-3">
				<!-- Description -->
				<label for="description" class="form-label"><?php esc_html_e( 'Notes:', 'coursh' ); ?></label>
				<textarea id="description" name="description" class="form-control" rows="4"></textarea>
			</div>

			<div class="text-end">
				<!-- Submit Button -->
				<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Submit', 'coursh' ); ?></button>
			</div>
		</form>


		<!-- Response Container -->
		<div id="employee-actions" style="display:none"></div>

		<script>
			function startQrCodeScanner() {
				document.getElementById("qr-reader").style.display = "block";
				const qrCodeReader = new Html5Qrcode("qr-reader");
				qrCodeReader.start(
					{ facingMode: "environment" },
					{ fps: 10, qrbox: 150 },
					(decodedText) => {
						document.getElementById("tracking_number").value = decodedText;
						qrCodeReader.stop();
						document.getElementById("qr-reader").style.display = "none";
					},
					(errorMessage) => {
						console.log(errorMessage);
					}
				).catch((err) => {
					console.log(err);
				});
			}
		</script>
		<?php
		return ob_get_clean();
	}
);
/**
 * Shortcode to search for a shipment by tracking number.
 *
 * Usage: [track_shipment tracking_number="your_tracking_number"]
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output of the shipment data or an error message.
 */
function courier_search_shortcode( $atts ) {
	// Extract the shortcode attributes.
	$atts = shortcode_atts(
		array(
			'tracking_number' => '',
		),
		$atts,
		'track_shipment'
	);

	// Get the tracking number from the shortcode attributes.
	$tracking_number = $atts['tracking_number'];

	if ( empty( $tracking_number ) && empty( $_GET['number'] ) ) {
		return '<p>Please provide a tracking number.</p>';
	}

	if ( empty( $tracking_number ) && ! empty( $_GET['number'] ) ) {
		$tracking_number = $_GET['number'];
	}

	$trackings = tracking_details( $tracking_number );

	// Check if a shipment was found.
	if ( $trackings ) {
		ob_start();
		load_view(
			'tracking',
			array(
				'trackings'       => $trackings,
				'tracking_number' => $tracking_number,
			)
		);
		return ob_get_clean();
	}

	return '<p>No tracking details found for the given tracking number.</p>';
}
add_shortcode( 'track_shipment', 'courier_search_shortcode' );
