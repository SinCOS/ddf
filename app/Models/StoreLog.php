<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreLog extends Model
{

	protected $table = 'czt_store_cashier_paylog';
	#protected $primaryKey = 'uid';

	public $email;
	protected $guarded = [];
	protected $dateFormat = 'U';
	protected $append = [
		'pay_type'];
	public function getPayTypeAttribute()
	{
		$typeArr = ['wechat' =>'微信支付','alipay'=>'支付宝'];
		return isset($typeArr[$this->type]) ? $typeArr[$this->type] : '未知' ;

	}
}