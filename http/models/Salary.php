<?php

use Illuminate\Database\Eloquent\Model;

class Salary extends Model {

	protected $table      = 'salaries'; // Define the table name
	protected $primaryKey = 'id'; // Primary key
	public $timestamps    = true; // Enable timestamps

	protected $fillable = array(
		'user_id',
		'base_salary',
	);

	/**
	 * Relationship: Each salary belongs to a user.
	 */
	public function user() {
		return $this->belongsTo( User::class, 'user_id' );
	}

	/**
	 * Relationship: Each salary has many transactions.
	 */
	public function transactions() {
		return $this->hasMany( SalaryTransaction::class, 'salary_id' );
	}

	/**
	 * Relationship: Each salary has many payments.
	 */
	public function payments() {
		return $this->hasMany( SalaryPayment::class, 'salary_id' );
	}

	/**
	 * Calculate the net salary dynamically.
	 *
	 * @return float
	 */
	public function getNetSalaryAttribute() {
		$transactions = $this->transactions;

		$bonuses    = $transactions->where( 'transaction_type', 'bonus' )->sum( 'amount' );
		$advances   = $transactions->where( 'transaction_type', 'advance' )->sum( 'amount' );
		$deductions = $transactions->where( 'transaction_type', 'deduction' )->sum( 'amount' );

		return $this->base_salary + $bonuses - $advances - $deductions;
	}

	/**
	 * Create or update a user's salary.
	 *
	 * @param int   $userId
	 * @param float $baseSalary
	 * @return Salary
	 */
	public static function setSalary( $userId, $baseSalary ) {
		// Validate inputs
		if ( ! $userId || $baseSalary < 0 ) {
			throw new \InvalidArgumentException( 'Invalid user ID or base salary.' );
		}

		// Check if the salary exists
		$salary = self::where( 'user_id', $userId )->first();

		if ( $salary ) {
			// Update existing salary
			$salary->update( array( 'base_salary' => $baseSalary ) );
		} else {
			// Create a new salary
			$salary = self::create(
				array(
					'user_id'     => $userId,
					'base_salary' => $baseSalary,
				)
			);
		}

		return $salary;
	}
}
