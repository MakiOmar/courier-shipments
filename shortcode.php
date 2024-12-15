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
			hx-post="<?php echo esc_url( admin_url( 'admin-ajax.php?action=search_tracking_number' ) ); ?>" 
			hx-target="#tracking-result" 
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
		<div id="tracking-result" style="display:none"></div>
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
					hx-post="<?php echo esc_url( admin_url( 'admin-ajax.php?action=search_tracking_number' ) ); ?>"
					hx-target="#tracking-result"
					hx-indicator="#maglev-loading-indicator"
					>
						<div class="mb-3">
							<label for="tracking_number" class="form-label"><?php esc_html_e( 'Tracking number', 'coursh' ); ?></label>
							<input type="text" class="form-control" id="tracking_number" name="tracking_number" required readonly>
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
		<div id="employee-actions"></div>

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


