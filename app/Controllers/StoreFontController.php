<?php


namespace App\Controllers;

use App\Models\User;
use App\Models\StoreLog;
use App\Models\Store;
use Illuminate\Database\Capsule\Manager as DB;


class StoreFontController extends Controller
{
	public function getQrcode($request,$response,$args){
		$store = Store::find($args['id']);
		return $this->view->render($response,'qrcode.twig',['args' => $args,'store' => $store]);
	}
	public function getStore($request,$response,$args){
		$storeid = (int)$args['id'];
		$store = Store::find($storeid);
		$store_config = DB::table('czt_store_cashier_price')->where('storeid',$storeid);
		return $this->view->render($response,'store/index.twig',[
			'store_config' => $store_config,
			'store' => $store 
		]);
	}
	
}