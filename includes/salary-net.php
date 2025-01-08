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
	// Get filter values from the request or default to empty (show all)
	$selected_month = isset( $_GET['month'] ) ? intval( $_GET['month'] ) : '';
	$selected_year  = isset( $_GET['year'] ) ? intval( $_GET['year'] ) : '';
	$selected_user  = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : '';

	// Fetch all WordPress users for the user dropdown
	$users = get_users();

	// Fetch unique years from transactions for the year dropdown
	$years = SalaryTransaction::selectRaw( 'YEAR(created_at) as year' )
		->groupBy( 'year' )
		->pluck( 'year' );

	// Fetch salaries with transactions filtered by the selected user, year, and month
	$salaries = Salary::with(
		array(
			'transactions' => function ( $query ) use ( $selected_month, $selected_year ) {
				if ( $selected_month ) {
					$query->whereRaw( 'MONTH(created_at) = ?', array( $selected_month ) );
				}
				if ( $selected_year ) {
					$query->whereRaw( 'YEAR(created_at) = ?', array( $selected_year ) );
				}
			},
			'user',
		)
	)
	->when(
		$selected_user,
		function ( $query ) use ( $selected_user ) {
			$query->where( 'user_id', $selected_user );
		}
	)
	->get();

	// Group transactions by user, month, and year
	$grouped_salaries = array();
	foreach ( $salaries as $salary ) {
		foreach ( $salary->transactions as $transaction ) {
			$month = date( 'm', strtotime( $transaction->created_at ) );
			$year  = date( 'Y', strtotime( $transaction->created_at ) );
			$key   = $salary->user->ID . '_' . $year . '_' . $month;

			if ( ! isset( $grouped_salaries[ $key ] ) ) {
				$grouped_salaries[ $key ] = array(
					'user_id'        => $salary->user->ID,
					'user_name'      => $salary->user->display_name,
					'user_email'     => $salary->user->user_email,
					'base_salary'    => $salary->base_salary,
					'net_bonuses'    => 0,
					'net_advances'   => 0,
					'net_deductions' => 0,
					'month'          => $month,
					'year'           => $year,
				);
			}

			// Update totals for each transaction type
			switch ( $transaction->transaction_type ) {
				case 'bonus':
					$grouped_salaries[ $key ]['net_bonuses'] += $transaction->amount;
					break;
				case 'advance':
					$grouped_salaries[ $key ]['net_advances'] += $transaction->amount;
					break;
				case 'deduction':
					$grouped_salaries[ $key ]['net_deductions'] += $transaction->amount;
					break;
			}
		}
	}

	// Calculate net salary for each grouped record
	foreach ( $grouped_salaries as &$salary_group ) {
		$salary_group['net_salary'] =
			$salary_group['base_salary']
			+ $salary_group['net_bonuses']
			- $salary_group['net_advances']
			- $salary_group['net_deductions'];
	}

	?>
	<div class="wrap">
		<h1>Net Salary Report</h1>

		<!-- Filter Form -->
		<form method="GET" action="">
			<input type="hidden" name="page" value="net-salary">

			<table class="form-table">
				<tr>
					<th><label for="user_id">User</label></th>
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
			</table>

			<p class="submit">
				<button type="submit" class="button button-primary">Filter</button>
			</p>
		</form>

		<!-- Net Salary Table -->
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
					<th>Month-Year</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $grouped_salaries ) ) : ?>
					<tr>
						<td colspan="8">No salary records found for the selected filters.</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $grouped_salaries as $group ) : ?>
						<tr>
							<td><?php echo esc_html( $group['user_name'] ); ?></td>
							<td><?php echo esc_html( $group['user_email'] ); ?></td>
							<td><?php echo number_format( $group['base_salary'], 2 ); ?></td>
							<td><?php echo number_format( $group['net_bonuses'], 2 ); ?></td>
							<td><?php echo number_format( $group['net_advances'], 2 ); ?></td>
							<td><?php echo number_format( $group['net_deductions'], 2 ); ?></td>
							<td><?php echo number_format( $group['net_salary'], 2 ); ?></td>
							<td><?php echo esc_html( date( 'F Y', mktime( 0, 0, 0, $group['month'], 1, $group['year'] ) ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

