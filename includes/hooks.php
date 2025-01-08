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

		if ( isset( $_GET['page'] ) && ! empty( $_GET['page'] && 'jet-cct-shipments' === $_GET['page'] ) ) {
			?>
			<script>
				function printQr(data) {
					let printContent = '';
					data.forEach(function (shipment) {
						let trackingNumber = shipment.tracking_number;
						printContent += `
							<div class="suc-waybill-container" style="border: 2px solid #000; font-size: 14px; padding: 10px; direction: rtl; page-break-after: always;">
								<!-- Header -->
								<div class="suc-waybill-header" style="background-color: #30388d; color: #fff; text-align: center; padding: 10px;">
									<h5 style="margin: 0;">بوليصة الشحن WayBill</h5>
								</div>
								<!-- Subheader with Logo and Barcode -->
								<div class="suc-waybill-subheader" style="background-color: #30388d;display: flex; align-items: center; justify-content: space-between; padding: 10px; border-bottom: 2px solid #000;">
									<div style="text-align: center;">
										<img src="https://courier.lightway-eg.com/wp-content/uploads/2025/01/waybill-logo.png" alt="Logo" style="max-width: 100px;">
									</div>
									<div style="width: 75%;display:flex;justify-content:center">
										<div style="text-align: center; background-color: #fff; border-radius: 5px; color: #000;width: 75%;">
											<img src="https://api.qrserver.com/v1/create-qr-code/?data=${trackingNumber}&size=80x80" alt="QR Code for shipment ID" style="max-height: 80px;">
											<p style="margin: 5px 0;">${trackingNumber}</p>
										</div>
									</div>
									<div style="text-align: center;">
										<img src="https://courier.lightway-eg.com/wp-content/uploads/2025/01/waybill-logo.png" alt="Logo" style="max-width: 100px;">
									</div>
								</div>
								<!-- Sender and Receiver Info -->
								<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
									<thead>
										<tr>
											<th style="background-color: #30388d; color: #fff; font-size: 22px; text-align: center; padding: 10px; border: 1px solid #000;">Sender Info</th>
											<th style="background-color: #30388d; color: #fff; font-size: 22px; text-align: center; padding: 10px; border: 1px solid #000;">Receiver Info</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>اسم المرسل:</strong> شركة الجوهرة</td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>اسم المستلم:</strong> خليفة المهري</td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>عنوان المرسل:</strong></td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>عنوان المستلم:</strong> شارع الإعلامي</td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>الدولة:</strong></td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>الدولة:</strong> قطر</td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>المدينة:</strong></td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>المدينة:</strong> الدوحة</td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>الهاتف:</strong></td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>الهاتف:</strong> +97455515580</td>
										</tr>
									</tbody>
								</table>
								<!-- Shipment and Payment Info -->
								<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
									<thead>
										<tr>
											<th style="background-color: #30388d; color: #fff; font-size: 22px; text-align: center; padding: 10px; border: 1px solid #000;">Shipment Info</th>
											<th style="background-color: #30388d; color: #fff; font-size: 22px; text-align: center; padding: 10px; border: 1px solid #000;">Payment Info</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>تاريخ الشحنة:</strong> 2024/02/14</td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>Bill To:</strong></td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>الوزن الفعلي:</strong> KG 0.7</td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>التكلفة:</strong></td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>الوزن الإجمالي:</strong> KG 0.7</td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>Fees of COD:</strong></td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>عدد القطع:</strong> 1</td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>Other Charges:</strong></td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>المحتويات:</strong></td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>Insurance Charges:</strong></td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"></td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>Customs Charges:</strong></td>
										</tr>
										<tr>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"></td>
											<td style="border: 1px solid #000; text-align: right; padding: 10px; font-size: 17px;"><strong>Total:</strong></td>
										</tr>
									</tbody>
								</table>
								<!-- Footer -->
								<div style="margin-top: 10px; text-align: center;padding-bottom:50px">
									<p style="background-color: #30388d;color:#fff;padding:10px;margin: 10px 0;">الشروط والأحكام Terms & Conditions</p>
									<div style="display: flex; justify-content: space-between; margin-top: 10px;">
										<span>توقيع العميل</span>
										<span>التوقيع</span>
									</div>
								</div>
							</div>`;
					});

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



add_action(
	'admin_init',
	function () {
		SalaryController::setSalary();
		SalaryController::deleteSalary();
		SalaryTransactionsController::create();
	}
);