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
				function printQr( data ) {
					// Generate print content
					var printContent = '<div style="font-family: Arial; padding: 20px;">';
					data.forEach(function(shipment) {
						let trackingNumber = shipment.tracking_number;
						console.log(trackingNumber);
						printContent += '<div style="page-break-after: always;display:flex;flex-direction:column; align-items: center; justify-content:center; direction: rtl; margin: 0 auto;">' +
							'<img src="https://oud.azureedge.net/cdn/2024/11/sticker-logo.png" alt="Logo" style="max-width: 150px; margin-bottom: 10px; display: inline-block;">' +
							'<table style="width: auto; margin: 0 auto; border-collapse: collapse;font-size:15px">' +
								'<tr><td style="text-align: right;padding: 10px 0;min-width:75px"><strong>' + '<?php esc_html__('Description', 'coursh'); ?>' + ': </strong></td><td style="text-align: right;">' + shipment.billing_name + '</td></tr>' +
							'</table>' +
							'<div style="margin-top: 10px;">' +
								'<img src="https://api.qrserver.com/v1/create-qr-code/?data=' + trackingNumber + '&size=80x80" alt="QR Code for shipment ID" style="display: block; max-width: 150px; margin: 0 auto;">' +
							'</div>';
					});

					printContent += '</div>';

					// Open print window
					var printWindow = window.open('', '_blank');
					printWindow.document.write(`
						<html>
							<head>
								<title>` + `<?php esc_html__('Print', 'coursh'); ?>` + `</title>
								<style>
									@media print {
										/* Set custom page size to 10x15 cm */
										@page {
											size: 10cm 15cm; /* Width x Height in cm */
											margin: 2mm; /* Adjust margin as needed */
										}

										/* Ensure each shipment starts on a new page */
										div { page-break-after: always; }

										/* Ensure images display correctly */
										img { display: block !important; max-width: 100% !important; }

										/* Adjust body styling for compact size */
										body {
											font-family: Arial, sans-serif;
											font-size: 12px; /* Adjust for smaller page */
										}
									}
								</style>
							</head>
							<body>${printContent}</body>
						</html>
					`);

					printWindow.document.close();

					// Wait for images to load before printing
					printWindow.onload = function () {
						printWindow.print();
					};

				}
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
								alert('<?php esc_html_e( 'Please select shipment', 'coursh' ); ?>');
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
										printQr( response.data );
									} else {
										alert('<?php esc_html_e( 'Faild to fetch shipments details', 'coursh' ); ?>');
									}
								},
								error: function() {
									alert('<?php esc_html_e( 'Something wrong happend', 'coursh' ); ?>');
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
