<?php

add_action( 'admin_menu', 'add_salary_transactions_page' );

function add_salary_transactions_page() {
	add_submenu_page(
		'salary-management',                // Parent menu slug
		'Salary Transactions',              // Page title
		'Salary Transactions',              // Menu title
		'manage_options',                   // Capability required
		'salary-transactions',              // Menu slug
		'render_salary_transactions_page'   // Callback function
	);
}

function render_salary_transactions_page() {
	$users = get_users(); // Fetch all WordPress users

	// Fetch only the latest transaction for each user with a salary
	$latestTransactions = SalaryTransaction::with( array( 'salary.user' ) )
		->select( 'salary_id', 'transaction_type', 'amount', 'description', 'created_at' )
		->orderBy( 'created_at', 'desc' )
		->get()
		->unique( 'salary_id' ); // Ensures we only keep the latest transaction per user

	?>
	<div class="wrap">
		<h1>Salary Management</h1>

		<!-- Form to Add/Set Salary -->
		<form method="POST" action="">
			<?php wp_nonce_field( 'set_salary_action', 'set_salary_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="user_id">Select User</label></th>
					<td>
						<select name="user_id" id="user_id" required>
							<option value="">Select a User</option>
							<?php foreach ( $users as $user ) : ?>
								<option value="<?php echo $user->ID; ?>">
									<?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="base_salary">Base Salary</label></th>
					<td>
						<input type="number" step="0.01" name="base_salary" id="base_salary" required>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" name="set_salary" class="button button-primary">Set Salary</button>
			</p>
		</form>

		<!-- Latest Salary Transactions Table -->
		<h2>Users with Latest Transactions</h2>
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th>User Name</th>
					<th>Email</th>
					<th>Transaction Type</th>
					<th>Amount</th>
					<th>Description</th>
					<th>Date</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $latestTransactions->isEmpty() ) : ?>
					<tr>
						<td colspan="7">No transactions found.</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $latestTransactions as $transaction ) : ?>
						<tr>
							<td><?php echo esc_html( $transaction->salary->user->display_name ); ?></td>
							<td><?php echo esc_html( $transaction->salary->user->user_email ); ?></td>
							<td><?php echo ucfirst( $transaction->transaction_type ); ?></td>
							<td><?php echo number_format( $transaction->amount, 2 ); ?></td>
							<td><?php echo esc_html( $transaction->description ); ?></td>
							<td><?php echo esc_html( $transaction->created_at ); ?></td>
							<td>
								<!-- Button for Transaction History -->
								<a href="<?php echo admin_url( 'admin.php?page=transaction-history&user_id=' . $transaction->salary->user->ID ); ?>" class="button">Transaction History</a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
