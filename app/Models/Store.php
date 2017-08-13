<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{

	protected $table = 'czt_store_cashier_store';
	#protected $primaryKey = 'uid';

	public $email;
	protected $guarded = [];
	protected $dateFormat = 'U';
	protected $append = [
		'pay_type','nfy_fans','child_id'];

	public function getPayTypeAttribute()
	{
		$typeArr = ['wechat' =>'微信支付','alipay'=>'支付宝'];
		return isset($typeArr[$this->type]) ? $typeArr[$this->type] : '未知' ;

	}
	public function getNfyFansAttribute()
	{
		$temp = unserialize($this->notify_fans);
		// if(is_array($temp)){
		// 	foreach ($temp as $key => $value) {
		// 		$temp[$key] = explode('|',$value);
		// 	}
		// }

		return  $temp;

	}
	public function getChildIdAttribute(){
		return unserialize($this->subID);
	}
}