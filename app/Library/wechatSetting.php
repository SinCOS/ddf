<?php


namespace App\Library;
use Illuminate\Database\Capsule\Manager as DB;
class wechatSetting {

    public function __construct(){

    }

    public static function getSetting(){
    	return DB::table('account_wechats')->where('acid',2)->first();
    }
}