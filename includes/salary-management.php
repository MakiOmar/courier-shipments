<?php


add_action( 'admin_menu', 'add_salary_management_page' );

function add_salary_management_page() {
	add_menu_page(
		'Salary Management',          // Page title
		'Salary Management',          // Menu title
		'manage_options',             // Capability
		'salary-management',          // Menu slug
		'render_salary_management_page', // Callback function
		'dashicons-money-alt',        // Icon
		25                            // Position
	);
}

function render_salary_management_page() {
	global $wpdb;
	$users = get_users(); // Fetch all WordPress users
	// Fetch all users who have a salary
	$salaries = Salary::with( 'user' )->get(); // Fetch salaries with user relationships
	?>
	<div class="wrap">
		<h1>Salary Management</h1>

		<?php if ( isset( $message ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo $message; ?></p>
			</div>
		<?php elseif ( isset( $error ) ) : ?>
			<div class="notice notice-error is-dismissible">
				<p><?php echo $error; ?></p>
			</div>
		<?php endif; ?>

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

		<!-- Salary Table -->
		<h2>Users with Salaries</h2>
		<table class="wp-list-table widefat fixed striped table-view-list">
			<thead>
				<tr>
					<th>User Name</th>
					<th>Email</th>
					<th>Base Salary</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $salaries->isEmpty() ) : ?>
					<tr>
						<td colspan="4">No salaries found.</td>
					</tr>
				<?php else : ?>
					<?php foreach ( $salaries as $salary ) : ?>
						<tr>
							<td><?php echo esc_html( $salary->user->display_name ); ?></td>
							<td><?php echo esc_html( $salary->user->user_email ); ?></td>
							<td><?php echo number_format( $salary->base_salary, 2 ); ?></td>
							<td>
								<form method="POST" action="">
									<?php wp_nonce_field( 'delete_salary_action', 'delete_salary_nonce' ); ?>
									<input type="hidden" name="salary_id" value="<?php echo $salary->id; ?>">
									<button type="submit" name="delete_salary" class="button button-secondary">Delete</button>
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

