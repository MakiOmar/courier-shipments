<?php
add_action( 'admin_menu', 'add_transaction_history_page' );

function add_transaction_history_page() {
	add_submenu_page(
		null, // No visible menu item
		'Transaction History',
		'Transaction History',
		'manage_options',
		'transaction-history',
		'render_transaction_history_page'
	);
}
function render_transaction_history_page() {
	if ( ! isset( $_GET['user_id'] ) || ! is_numeric( $_GET['user_id'] ) ) {
		wp_die( 'Invalid User ID.' );
	}

	$user_id = intval( $_GET['user_id'] );

	// Fetch filter values
	$selected_month = isset( $_GET['month'] ) ? intval( $_GET['month'] ) : '';
	$selected_year  = isset( $_GET['year'] ) ? intval( $_GET['year'] ) : '';
	$selected_type  = isset( $_GET['type'] ) ? sanitize_text_field( $_GET['type'] ) : '';

	// Fetch unique years from transactions for dropdown
	$years = SalaryTransaction::selectRaw( 'YEAR(created_at) as year' )
		->groupBy( 'year' )
		->pluck( 'year' );

	// Filtered query
	$query = SalaryTransaction::with( 'salary.user' )
		->whereHas(
			'salary',
			function ( $query ) use ( $user_id ) {
				$query->where( 'user_id', $user_id );
			}
		);

	// Apply filters
	if ( $selected_month ) {
		$query->whereRaw( 'MONTH(created_at) = ?', array( $selected_month ) );
	}
	if ( $selected_year ) {
		$query->whereRaw( 'YEAR(created_at) = ?', array( $selected_year ) );
	}
	if ( $selected_type ) {
		$query->where( 'transaction_type', $selected_type );
	}

	// Get filtered transactions
	$transactions = $query->orderBy( 'created_at', 'desc' )->get();

	?>
	<div class="wrap">
		<h1>Transaction History for User ID: <?php echo $user_id; ?></h1>

		<!-- Filter Form -->
		<form method="GET" action="">
			<input type="hidden" name="page" value="transaction-history">
			<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

			<table class="form-table">
				<tr>
					<th><label for="month">Month</label></th>
					<td>
						<select name="month" id="month">
							<option value="">All Months</option>
							<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
								<option value="<?php echo $i; ?>" <?php selected( $selected_month, $i ); ?>>
									<?php echo date( 'F', mktime( 0, 0, 0, $i, 1 ) ); ?>
								</option>
							<?php endfor; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="year">Year</label></th>
					<td>
						<select name="year" id="year">
							<option value="">All Years</option>
							<?php foreach ( $years as $year ) : ?>
								<option value="<?php echo $year; ?>" <?php selected( $selected_year, $year ); ?>>
									<?php echo $year; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="type">Transaction Type</label></th>
					<td>
						<select name="type" id="type">
							<option value="">All Types</option>
							<option value="bonus" <?php selected( $selected_type, 'bonus' ); ?>>Bonus</option>
							<option value="advance" <?php selected( $selected_type, 'advance' ); ?>>Advance</option>
							<option value="deduction" <?php selected( $selected_type, 'deduction' ); ?>>Deduction</option>
						</select>
					</td>
				</tr>
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary">Filter</button>
			</p>
		</form>

		<!-- Transactions Table -->
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th>Transaction Type</th>
					<th>Amount</th>
					<th>Description</th>
					<th>Date</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $transactions->isEmpty() ) : ?>
					<tr>
						<td colspan="5">No transactions found.</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $transactions as $transaction ) : ?>
						<tr>
							<td><?php echo ucfirst( $transaction->transaction_type ); ?></td>
							<td><?php echo number_format( $transaction->amount, 2 ); ?></td>
							<td><?php echo esc_html( $transaction->description ); ?></td>
							<td><?php echo esc_html( $transaction->created_at ); ?></td>
							<td>
								<!-- Button for Deleting Transaction -->
								<form method="POST" action="" style="display:inline;">
									<?php wp_nonce_field( 'delete_salary_action', 'delete_salary_nonce' ); ?>
									<input type="hidden" name="transaction_id" value="<?php echo $transaction->id; ?>">
									<button type="submit" name="delete_transaction" class="button button-secondary">Delete</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

