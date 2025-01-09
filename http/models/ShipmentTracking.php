<?php
use Illuminate\Database\Eloquent\Model;

class ShipmentTracking extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'shipment_tracking';

	/**
	 * The primary key associated with the table.
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * Indicates if the IDs are auto-incrementing.
	 *
	 * @var bool
	 */
	public $incrementing = true;

	/**
	 * The data type of the primary key.
	 *
	 * @var string
	 */
	protected $keyType = 'int';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = true;

	/**
	 * The name of the "created at" column.
	 */
	const CREATED_AT = 'created_at';

	/**
	 * The name of the "updated at" column.
	 */
	const UPDATED_AT = 'updated_at';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'shipment_id',
		'employee_id',
		'status',
		'description',
	);

	/**
	 * Define a relationship to the shipment.
	 */
	public function shipment() {
		return $this->belongsTo( Shipment::class, 'shipment_id', '_ID' );
	}

	/**
	 * Define a relationship to the employee/user (if applicable).
	 */
	public function employee() {
		return $this->belongsTo( User::class, 'employee_id' );
	}
}
