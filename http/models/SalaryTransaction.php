<?php

use Illuminate\Database\Eloquent\Model;

class SalaryTransaction extends Model {

	protected $table      = 'salary_transactions'; // Define the table name.
	protected $primaryKey = 'id'; // Primary key.
	public $timestamps    = true; // Enable timestamps.

	protected $fillable = array(
		'salary_id',
		'transaction_type',
		'amount',
		'description',
	);

	/**
	 * Relationship: Each transaction belongs to a salary.
	 */
	public function salary() {
		return $this->belongsTo( Salary::class, 'salary_id' );
	}
}
