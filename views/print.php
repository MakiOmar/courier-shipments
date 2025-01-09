<div class="suc-waybill-container" style="border: 2px solid #000; font-size: 14px; padding: 10px; direction: <?php echo is_rtl() ? 'rtl' : 'ltr' ?>; page-break-after: always;">
	<!-- Header -->
	<div class="suc-waybill-header" style="background-color: #30388d; color: #fff; text-align: center; padding: 10px;">
		<h5 style="margin: 0;"><?php echo esc_html__( 'WayBill', 'coursh' ); ?></h5>
	</div>
	<!-- Subheader with Logo and Barcode -->
	<div class="suc-waybill-subheader" style="background-color: #30388d; display: flex; align-items: center; justify-content: space-between; padding: 10px; border-bottom: 2px solid #000;">
		<div style="text-align: center;">
			<img src="/wp-content/uploads/2025/01/waybill-logo.png" alt="<?php echo esc_html__( 'Logo', 'coursh' ); ?>" style="max-width: 100px;">
		</div>
		<div style="width: 75%; display: flex; justify-content: center;">
			<div style="text-align: center; background-color: #fff; border-radius: 5px; color: #000; width: 75%;">
				<img src="https://api.qrserver.com/v1/create-qr-code/?data=<?php echo esc_html( $shipment->tracking_number ); ?>&size=80x80" alt="<?php echo esc_html__( 'QR Code for shipment ID', 'coursh' ); ?>" style="max-height: 80px;">
				<p style="margin: 5px 0;"><?php echo esc_html( $shipment->tracking_number ); ?></p>
			</div>
		</div>
		<div style="text-align: center;">
			<img src="/wp-content/uploads/2025/01/waybill-logo.png" alt="<?php echo esc_html__( 'Logo', 'coursh' ); ?>" style="max-width: 100px;">
		</div>
	</div>
	<!-- Sender and Receiver Info -->
	<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
		<thead>
			<tr>
				<th style="background-color: #30388d; color: #fff; font-size: 22px; text-align: center; padding: 10px; border: 1px solid #000;"><?php echo esc_html__( 'Sender Info', 'coursh' ); ?></th>
				<th style="background-color: #30388d; color: #fff; font-size: 22px; text-align: center; padding: 10px; border: 1px solid #000;"><?php echo esc_html__( 'Receiver Info', 'coursh' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Sender Name:', 'coursh' ); ?></strong> </td>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Receiver Name:', 'coursh' ); ?></strong> <?php echo esc_html( $shipment->receivername ); ?></td>
			</tr>
			<tr>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Sender Address:', 'coursh' ); ?></strong></td>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Receiver Address:', 'coursh' ); ?></strong> <?php echo esc_html( $shipment->receiveraddress ); ?></td>
			</tr>
			<tr>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Country:', 'coursh' ); ?></strong></td>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Country:', 'coursh' ); ?></strong> <?php echo esc_html( $shipment->receivercountry ); ?></td>
			</tr>
			<tr>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'City:', 'coursh' ); ?></strong></td>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'City:', 'coursh' ); ?></strong> <?php echo esc_html( $shipment->receivercity ); ?></td>
			</tr>
			<tr>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Phone:', 'coursh' ); ?></strong></td>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Phone:', 'coursh' ); ?></strong> <?php echo esc_html( $shipment->receiverphone ); ?></td>
			</tr>
		</tbody>
	</table>
	<!-- Shipment and Payment Info -->
	<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
		<thead>
			<tr>
				<th style="background-color: #30388d; color: #fff; font-size: 22px; text-align: center; padding: 10px; border: 1px solid #000;"><?php echo esc_html__( 'Shipment Info', 'coursh' ); ?></th>
				<th style="background-color: #30388d; color: #fff; font-size: 22px; text-align: center; padding: 10px; border: 1px solid #000;"><?php echo esc_html__( 'Payment Info', 'coursh' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Shipment Date:', 'coursh' ); ?></strong> <?php echo esc_html( $shipment->cct_created->format( 'Y/m/d' ) ); ?></td>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Bill To:', 'coursh' ); ?></strong></td>
			</tr>
			<tr>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Actual Weight:', 'coursh' ); ?></strong> KG <?php echo esc_html( $shipment->totalweight / 1000 ); ?></td>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Cost:', 'coursh' ); ?></strong></td>
			</tr>
			<tr>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Contents:', 'coursh' ); ?></strong> <?php echo esc_html( $shipment->contentdescription ); ?></td>
				<td style="border: 1px solid #000; text-align: <?php echo is_rtl() ? 'right' : 'left'; ?>; padding: 10px; font-size: 17px;"><strong><?php echo esc_html__( 'Total:', 'coursh' ); ?></strong></td>
			</tr>
		</tbody>
	</table>
	<!-- Footer -->
	<div style="margin-top: 10px; text-align: center; padding-bottom: 50px;">
		<p style="background-color: #30388d; color: #fff; padding: 10px; margin: 10px 0;"><?php echo esc_html__( 'Terms & Conditions', 'coursh' ); ?></p>
		<div style="display: flex; justify-content: space-between; margin-top: 10px;">
			<span><?php echo esc_html__( 'Customer Signature', 'coursh' ); ?></span>
			<span><?php echo esc_html__( 'Signature', 'coursh' ); ?></span>
		</div>
	</div>
</div>
