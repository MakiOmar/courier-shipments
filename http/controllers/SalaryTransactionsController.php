<?php //phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class SalaryTransactionsController {
	public static function create() {

		// Handle form submissions for adding transactions
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['add_transaction'] ) ) {
			try {
				// Validate nonce
				if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'add_transaction_action' ) ) {
					throw new \Exception( 'Security check failed.' );
				}

				// Get inputs
				$user_id          = intval( $_POST['user_id'] );
				$transaction_type = sanitize_text_field( $_POST['transaction_type'] );
				$amount           = floatval( $_POST['amount'] );
				$description      = sanitize_textarea_field( $_POST['description'] );
				
				// Validate inputs
				if ( ! $user_id || ! $transaction_type || $amount <= 0 ) {
					throw new \Exception( 'Invalid input data.' );
				}

				// Get the salary_id for the user
				$salary = Salary::where( 'user_id', $user_id )->first();
				if ( ! $salary ) {
					throw new \Exception( 'Salary record not found for the selected user.' );
				}

				// Insert the transaction using the SalaryTransaction model
				$created = SalaryTransaction::create(
					array(
						'salary_id'        => $salary->id, // Use fetched salary_id
						'transaction_type' => $transaction_type,
						'amount'           => $amount,
						'description'      => $description,
					)
				);

				$message = 'Transaction added successfully.';
			} catch ( \Exception $e ) {
				error_log( $e->getMessage() );
				$error = $e->getMessage();
			}
		}
	}
}
