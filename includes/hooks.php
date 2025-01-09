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
	}
);


add_action(
	'admin_footer',
	function () {

		if ( isset( $_GET['page'] ) && ! empty( $_GET['page'] && 'shipment-management' === $_GET['page'] ) ) {
			?>
			<script>
				function printQr(data) {
					console.log(data);
					let printContent = data;
					let printWindow = window.open('', '_blank');
					printWindow.document.write(`
						<html>
							<head>
								<style>
									@media print {
										body {
											-webkit-print-color-adjust: exact;
											print-color-adjust: exact;
										}
									}
								</style>
							</head>
							<body>${printContent}</body>
						</html>
					`);

					printWindow.document.close();
					printWindow.onload = function () {
						printWindow.print();
					};
				}


				jQuery(document).ready(function ($) {
					$('#checkall-shipments').on('change', function() {
						// Check or uncheck all checkboxes based on the state of #checkall-shipments
						$('.shipment-item').prop('checked', $(this).is(':checked'));
					});

					$('#print-waybill').on(
						'click',
						function( e ) {
							e.preventDefault();

							// Get selected shipment IDs
							var shipmentsIds = [];
							$('#shipments-management .shipment-item:checked').each(function() {
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
					);
				});

			</script>
			<?php
		}
	}
);



add_action(
	'admin_init',
	function () {
		SalaryController::setSalary();
		SalaryController::deleteSalary();
		SalaryTransactionsController::create();
	}
);