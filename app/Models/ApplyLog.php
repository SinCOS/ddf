<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Auth\Auth;
class ApplyLog extends Model
{

	protected $table = 'czt_store_cashier_applylog6';
	#protected $primaryKey = 'uid';
	const CREATED_AT = 'createtime';
	const UPDATED_AT = false;
	protected $guarded = [];
	protected $dateFormat = 'U';

	protected $append = [];
	protected $fillable = [
			'sid','pay','status','money','sid','status','uniacid','fl','openid'
		];



}