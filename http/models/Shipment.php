<?php

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'jet_cct_shipments';

	/**
	 * The primary key associated with the table.
	 *
	 * @var string
	 */
	protected $primaryKey = '_ID';

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
	 * Use custom timestamps.
	 *
	 * @var bool
	 */
	public $timestamps = true;

	/**
	 * The name of the "created at" column.
	 */
	const CREATED_AT = 'cct_created';

	/**
	 * The name of the "updated at" column.
	 */
	const UPDATED_AT = 'cct_modified';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = array(
		'cct_status',
		'receivername',
		'receiveraddress',
		'receivercountry',
		'receivercity',
		'receiverphone',
		'totalweight',
		'unitweight',
		'contentdescription',
		'terms',
		'tracking_number',
		'cct_author_id',
		'cct_created',
		'cct_modified',
	);

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = array(
		'cct_created'  => 'datetime',
		'cct_modified' => 'datetime',
	);

	/**
	 * Define a relationship to the author/user (if applicable).
	 */
	public function author() {
		return $this->belongsTo( User::class, 'cct_author_id' );
	}
}
