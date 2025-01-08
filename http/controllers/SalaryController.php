<?php //phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class SalaryController {
	public static function setSalary() {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['set_salary'] ) ) {
			try {
				// Validate nonce
				if ( ! isset( $_POST['set_salary_nonce'] ) || ! wp_verify_nonce( $_POST['set_salary_nonce'], 'set_salary_action' ) ) {
					throw new \Exception( 'Security check failed.' );
				}

				// Get inputs
				$userId     = intval( $_POST['user_id'] );
				$baseSalary = floatval( $_POST['base_salary'] );

				// Use the Salary model to set the salary
				Salary::setSalary( $userId, $baseSalary );

				$message = 'Salary has been set successfully.';
			} catch ( \Exception $e ) {
				$error = $e->getMessage();
			}
		}
	}

	
	public static function deleteSalary() {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['delete_salary'] ) ) {
			try {
				if ( ! isset( $_POST['delete_salary_nonce'] ) || ! wp_verify_nonce( $_POST['delete_salary_nonce'], 'delete_salary_action' ) ) {
					throw new \Exception( 'Security check failed.' );
				}

				$salaryId = intval( $_POST['salary_id'] );
				$salary   = Salary::find( $salaryId );

				if ( $salary ) {
					$salary->delete();
				} else {
					throw new \Exception( 'Salary not found.' );
				}
			} catch ( \Exception $e ) {
				echo $e->getMessage();
			}
		}
	}
}
