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

	// Fetch all salary transactions using the SalaryTransaction model
	$transactions = SalaryTransaction::with( 'salary.user' )->orderBy( 'created_at', 'desc' )->get();

	?>
	<div class="wrap">
		<h1>Salary Transactions</h1>

		<?php if ( isset( $message ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
		<?php elseif ( isset( $error ) ) : ?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $error; ?></p>
			</div>
		<?php endif; ?>

		<!-- Add Transaction Form -->
		<h2>Add Transaction</h2>
		<form method="POST" action="">
			<?php wp_nonce_field( 'add_transaction_action' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="user_id">User</label></th>
					<td>
						<select name="user_id" id="user_id" required>
							<option value="">Select a User</option>
							<?php foreach ( get_users() as $user ) : ?>
								<option value="<?php echo $user->ID; ?>">
									<?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="transaction_type">Transaction Type</label></th>
					<td>
						<select name="transaction_type" id="transaction_type" required>
							<option value="">Select a Type</option>
							<option value="bonus">Bonus</option>
							<option value="advance">Advance</option>
							<option value="deduction">Deduction</option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="amount">Amount</label></th>
					<td>
						<input type="number" step="0.01" name="amount" id="amount" required>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="description">Description</label></th>
					<td>
						<textarea name="description" id="description" rows="3"></textarea>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" name="add_transaction" class="button button-primary">Add Transaction</button>
			</p>
		</form>

		<!-- Transactions Table -->
		<h2>Existing Transactions</h2>
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th>User</th>
					<th>Transaction Type</th>
					<th>Amount</th>
					<th>Description</th>
					<th>Date</th>
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
							<td>
								<?php echo esc_html( $transaction->salary->user->display_name ); ?>
							</td>
							<td><?php echo ucfirst( $transaction->transaction_type ); ?></td>
							<td><?php echo number_format( $transaction->amount, 2 ); ?></td>
							<td><?php echo esc_html( $transaction->description ); ?></td>
							<td><?php echo esc_html( $transaction->created_at ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}
