<?php
add_action( 'admin_menu', 'add_shipment_management_page' );

function add_shipment_management_page() {
	add_menu_page(
		'Shipment Management',   // Page title
		'Shipments',             // Menu title
		'manage_options',        // Capability
		'shipment-management',   // Menu slug
		'render_shipment_management_page', // Callback function
		'dashicons-location',    // Icon
		25                       // Position
	);
}
function render_shipment_management_page() {
	// Fetch filter values
	$selected_user = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : '';
	$from_date     = isset( $_GET['from_date'] ) ? sanitize_text_field( $_GET['from_date'] ) : '';
	$to_date       = isset( $_GET['to_date'] ) ? sanitize_text_field( $_GET['to_date'] ) : '';
	$search_query  = isset( $_GET['search_query'] ) ? sanitize_text_field( $_GET['search_query'] ) : '';

	// Fetch users for the user dropdown
	$users = get_users();

	// Base query for shipments
	$query = Shipment::query()->with( 'author' );

	// Apply filters
	if ( $selected_user ) {
		$query->where( 'cct_author_id', $selected_user );
	}
	if ( $from_date ) {
		$query->where( 'cct_created', '>=', $from_date );
	}
	if ( $to_date ) {
		$query->where( 'cct_created', '<=', $to_date );
	}
	if ( $search_query ) {
		$query->where(
			function ( $q ) use ( $search_query ) {
				$q->Where( 'receivername', 'LIKE', "%$search_query%" )
				->orWhere( 'receiveraddress', 'LIKE', "%$search_query%" )
				->orWhere( 'receivercountry', 'LIKE', "%$search_query%" )
				->orWhere( 'receivercity', 'LIKE', "%$search_query%" )
				->orWhere( 'receiverphone', 'LIKE', "%$search_query%" )
				->orWhere( 'contentdescription', 'LIKE', "%$search_query%" )
				->orWhere( 'tracking_number', 'LIKE', "%$search_query%" );
			}
		);
	}

	// Fetch shipments
	$shipments = $query->orderBy( 'cct_created', 'desc' )->get();

	?>
	<div class="wrap" id="shipments-management">
		<h1>Shipment Management</h1>

		<!-- Filter Form -->
		<form method="GET" action="">
			<input type="hidden" name="page" value="shipment-management">

			<table class="form-table">
				<tr>
					<th><label for="user_id">Filter by User</label></th>
					<td>
						<select name="user_id" id="user_id">
							<option value="">All Users</option>
							<?php foreach ( $users as $user ) : ?>
								<option value="<?php echo $user->ID; ?>" <?php selected( $selected_user, $user->ID ); ?>>
									<?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="from_date">From Date</label></th>
					<td>
						<input type="date" name="from_date" id="from_date" value="<?php echo esc_attr( $from_date ); ?>">
					</td>
				</tr>
				<tr>
					<th><label for="to_date">To Date</label></th>
					<td>
						<input type="date" name="to_date" id="to_date" value="<?php echo esc_attr( $to_date ); ?>">
					</td>
				</tr>
				<tr>
					<th><label for="search_query">Search</label></th>
					<td>
						<input type="text" name="search_query" id="search_query" placeholder="Search..." value="<?php echo esc_attr( $search_query ); ?>">
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary">Filter</button>
			</p>
		</form>
        <a href="#" id="print-waybill" class="button btn btn-primary">طباعة البوليصة</a>
		<!-- Shipments Table -->
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th><input type="checkbox" id="checkall-shipments"></th>
					<th>Tracking number</th>
					<th>Receiver Name</th>
					<th>Receiver Address</th>
					<th>Receiver Country</th>
					<th>Receiver City</th>
					<th>Receiver Phone</th>
					<th>Total Weight</th>
					<th>Author Name</th>
					<th>Created Date</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $shipments->isEmpty() ) : ?>
					<tr>
						<td colspan="10">No shipments found.</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $shipments as $shipment ) : ?>
						<tr>
							<td><input type="checkbox" class="shipment-item" name="item_id[]" value="<?php echo esc_attr( $shipment->_ID ); ?>"></td>
							<td><?php echo esc_html( $shipment->tracking_number ); ?></td>
							<td><?php echo esc_html( $shipment->receivername ); ?></td>
							<td><?php echo esc_html( $shipment->receiveraddress ); ?></td>
							<td><?php echo esc_html( $shipment->receivercountry ); ?></td>
							<td><?php echo esc_html( $shipment->receivercity ); ?></td>
							<td><?php echo esc_html( $shipment->receiverphone ); ?></td>
							<td><?php echo esc_html( $shipment->totalweight ); ?></td>
							<td><?php echo esc_html( $shipment->author->display_name ?? 'N/A' ); ?></td>
							<td><?php echo esc_html( $shipment->cct_created->format( 'Y-m-d H:i:s' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
