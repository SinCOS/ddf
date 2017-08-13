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
		$list = Store::all()->toArray();
		return $this->view->render($response,'admin/store/list.twig',[
			'store_list' => $list
		]);
	}
	public function getEdit($request,$response,$args){
		$store = Store::find((int)$args['id']);
		$store_list = Store::get(['id','name']);
		return $this->view->render($response,'admin/store/edit.twig',[
			'store' => $store,
			'store_list' => $store_list
		]);
	}
	public function postEdit($request,$response,$args){
		$store_id = (int)$args['id'];
		$object = $request->getParsedBody();
		$store = Store::find($store_id);
		$store->name = $object['name'];
		$store->logo = $object['logo'];
		$store->jump_url = $object['jump_url'];
		$store->bank = $object['bank'];
		$store->bankperson = $object['bankperson'];
		$store->banknum = $object['banknum'];
		$store->account = $object['account'];
		$store->instruction = $object['instruction'];
		$store->remark = $object['remark'];
		$store->instruction = $object['instruction'];
		$store->enable_remark = $object['enable_remark'];
		$store->auto_jump = $object['auto_jump'];
		var_dump($object);
		if(isset($object['passwd']) && !empty($object['passwd'])){
			$store->passwd = md5($object['passwd']);
		}
		// if($store->save()){
		// 	return $resposne->withJson(['message'=>'success']);
		// }
		return $response;
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
		$page = (int)$request->getParam('page');
		$page= $page > 0 ? $page :1 ;
		$skip = ($page-1)*20;
		$store = array_column($store_list,'name','id');
		$list = StoreLog::orderBy('id','desc')->where('status',1)->paginate(20,['*'],'page',$page);
		$list->setPath($request->getBasePath());
		return $this->view->render($response,'admin/store/logging.twig',[
			'log_list' => $list,
			'store_list' => $store
		]);
	}
	
}