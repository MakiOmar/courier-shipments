<?php

use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model {

	protected $table      = 'salary_payments'; // Define the table name.
	protected $primaryKey = 'id'; // Primary key.
	public $timestamps    = true; // Enable timestamps.

	protected $fillable = array(
		'salary_id',
		'payment_amount',
		'payment_date',
		'description',
	);

	/**
	 * Relationship: Each payment belongs to a salary.
	 */
	public function salary() {
		return $this->belongsTo( Salary::class, 'salary_id' );
	}
}
