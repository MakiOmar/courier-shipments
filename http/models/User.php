<?php

use Illuminate\Database\Eloquent\Model;

class User extends Model {

	protected $table      = 'users'; // WordPress users table.
	protected $primaryKey = 'ID'; // Primary key.

	/**
	 * Relationship: Each user has one or more salaries.
	 */
	public function salaries() {
		return $this->hasMany( Salary::class, 'user_id' );
	}
}
