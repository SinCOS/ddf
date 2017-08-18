<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreLog extends Model
{

	protected $table = 'czt_store_cashier_paylog';
	#protected $primaryKey = 'uid';
	const CREATED_AT = 'create_time';
	 #const UPDATED_AT = null;
	public $email;
	protected $guarded = [];
	protected $dateFormat = 'U';
	protected $append = [
		'pay_type'];
	protected $fillable = [
			'sid','fee','status','create_time','openid','type','uniacid','tid',
			'uniontid','transaction_id','remark'
		];
	public function getPayTypeAttribute()
	{
		$typeArr = ['wechat' =>'微信支付','alipay'=>'支付宝'];
		return isset($typeArr[$this->type]) ? $typeArr[$this->type] : '未知' ;

	}

}