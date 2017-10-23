<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
* Order Model
*/
class Order extends Model
{
	protected $table = 'order';
	protected $fillable = [
		'id','good_id',
		'uid','price',
		'total',
		'created_at',
		'updated_at',
		'status',
		'goodString'
	];
	protected $dateFormat = 'U';
	public function setUpdated_at(){
		return 0 ;
	}
}