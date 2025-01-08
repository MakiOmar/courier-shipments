<?php
add_action( 'admin_menu', 'add_net_salary_page' );

function add_net_salary_page() {
	add_menu_page(
		'Net Salary',          // Page title
		'Net Salary',          // Menu title
		'manage_options',      // Capability
		'net-salary',          // Menu slug
		'render_net_salary_page', // Callback function
		'dashicons-chart-line', // Icon
		25                     // Position
	);
}

function render_net_salary_page() {
	// Get current month and year
	$current_month = date( 'm' );
	$current_year  = date( 'Y' );

	// Fetch all salaries and their related transactions
	$salaries = Salary::with(
		array(
			'transactions' => function ( $query ) use ( $current_month, $current_year ) {
				$query->whereRaw( 'MONTH(created_at) = ?', array( $current_month ) )
					->whereRaw( 'YEAR(created_at) = ?', array( $current_year ) );
			},
			'user',
		)
	)->get();

	?>
	<div class="wrap">
		<h1>Net Salary for <?php echo date( 'F Y' ); ?></h1>

		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th>User Name</th>
					<th>Email</th>
					<th>Base Salary</th>
					<th>Net Bonuses</th>
					<th>Net Advances</th>
					<th>Net Deductions</th>
					<th>Net Salary</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $salaries->isEmpty() ) : ?>
					<tr>
						<td colspan="7">No salaries found for this month.</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $salaries as $salary ) : ?>
						<?php
						// Filter transactions for this month
						$transactions_this_month = $salary->transactions;

						// Calculate net bonuses, advances, and deductions for this month
						$net_bonuses    = $transactions_this_month->where( 'transaction_type', 'bonus' )->sum( 'amount' );
						$net_advances   = $transactions_this_month->where( 'transaction_type', 'advance' )->sum( 'amount' );
						$net_deductions = $transactions_this_month->where( 'transaction_type', 'deduction' )->sum( 'amount' );

						// Calculate net salary for this month
						$net_salary = $salary->base_salary + $net_bonuses - $net_advances - $net_deductions;
						?>
						<tr>
							<td><?php echo esc_html( $salary->user->display_name ); ?></td>
							<td><?php echo esc_html( $salary->user->user_email ); ?></td>
							<td><?php echo number_format( $salary->base_salary, 2 ); ?></td>
							<td><?php echo number_format( $net_bonuses, 2 ); ?></td>
							<td><?php echo number_format( $net_advances, 2 ); ?></td>
							<td><?php echo number_format( $net_deductions, 2 ); ?></td>
							<td><?php echo number_format( $net_salary, 2 ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

