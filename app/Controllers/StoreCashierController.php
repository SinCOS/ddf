<?php


namespace App\Controllers;

use App\Models\User;
use App\Models\Store;
use App\Models\StoreLog;
use Illuminate\Database\Capsule\Manager as DB;
class StoreCashierController extends Controller
{
	public function getList($request,$response)
	{
		$list = DB::table('czt_store_cashier_store')->take(20)->get()->toArray();
		return $this->view->render($response,'admin/store/list.twig',[
			'store_list' => $list
		]);
	}
	public function getEdit($request,$response,$args){
		$store = Store::find((int)$args['id']);
		return $this->view->render($response,'admin/store/edit.twig',[
			'store' => $store 
		]);
	}
	public function shop($request,$response){
		return $this->view->render($response,'shop.twig');
	}
	public function qrcode($request,$resposne){
		
	}
	public function getDetail($request,$response,$args){
		$detail = StoreLog::find($args['id']);
		return $this->view->render($response,'admin/store/logging_detail.twig',[
			'detail' => $detail ?:[]
		]);
	}
	public function getLogging($request,$response){
		$store_list = DB::table('czt_store_cashier_store')->get(['id','name'])->toArray();
		$store = array_column($store_list,'name','id');
		$list = StoreLog::orderBy('id','desc')->where('status',1)->take(20)->get();
		return $this->view->render($response,'admin/store/logging.twig',[
			'log_list' => $list,
			'store_list' => $store
		]);
	}
	
}