<?php
/**
 * Hooks
 *
 * @package Courier
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_script( 'html5-qrcode', 'https://unpkg.com/html5-qrcode', array(), null, true );
		wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' );
	}
);


add_action(
	'admin_footer',
	function ( $hook ) {

		if ( ! empty( $_GET['page'] && 'jet-cct-shipment' === $_GET['page'] ) ) {
			?>
			<script>
				jQuery(document).ready(function ($) {
					// Check if the bulk action selector exists
					const bulkActionSelector = $('#bulk-action-selector-top');
					
					if (bulkActionSelector.length) {
						// Append the new option
						bulkActionSelector.append('<option value="bulk_print_qr">Bulk Print QR Code</option>');
					}

					$('#doaction, #doaction2').on('click', function(e) {
						var action = $(this).attr('id') === 'doaction' ? $('#bulk-action-selector-top').val() : $('#bulk-action-selector-bottom').val();

						if (action === 'bulk_print_qr') {
							e.preventDefault();

							// Get selected shipment IDs
							var shipmentsIds = [];
							$('tbody th.check-column input[type="checkbox"]:checked').each(function() {
								shipmentsIds.push($(this).val());
							});

							if (shipmentsIds.length === 0) {
								alert('<?php esc_html_e( 'Please select shipment', 'coursh' ) ?>');
								return;
							}

							// AJAX request to fetch shipment details
							$.ajax({
								url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
								type: 'POST',
								data: {
									action: 'coursh_bulk_print_qr',
									shipment_ids: shipmentsIds
								},
								success: function(response) {
									if (response.success) {
										
									} else {
										alert('<?php esc_html_e( 'Faild to fetch shipments details', 'coursh' ) ?>');
									}
								},
								error: function() {
									alert('<?php esc_html_e( 'Something wrong happend', 'coursh' ) ?>');
								}
							});
						}
					});
				});

			</script>
			<?php
		}
	}
);
