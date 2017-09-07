<?php


namespace App\Controllers;

use App\Models\User;
use App\Models\Store;
use App\Models\StoreLog;
use App\Models\ApplyLog;
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
	public function doExchange($request,$response){
		$todayS = strtotime(date('Y-m-d'));
		$todayF = strtotime(date('Y-m-d') .' 23:59:59');
		$list = StoreLog::where('create_time','<',$todayS)->where('status',1)->where('js',0)->select('sid',DB::raw('sum(fee) as total'))->groupBy('sid')->get();
		if(count($list) ==0 ){
			return $response->write('没有要结算的');
		}
		try {
			DB::beginTransaction();
			foreach($list as $l){
				$store = $l->store;
			
					ApplyLog::insert([
					'sid' => $l->sid,
					'money' => $l->total,
					'pay' => ($l->total  * (1-($store->dicount/10))),
					'status' => 0,
					'uniacid' => 2,
					'openid' => '',
					'createtime' => time(),
					'fl' => $store->dicount ?:0
					]);
			
			
			echo $l->store->name . $l->total . '....' .$l->store->dicount .'</br>';
			}
			DB::commit();
		} catch (\Exception $e) {
			echo "系统错误,数据回滚";
			DB::rollback();
			var_dump($e->getMessage());
			return $resposne->write('666');
		}
		StoreLog::where('create_time','<',$todayS)->where('status',1)->where('js',0)->update(['js'=> 1]);
		return $response->withJson($list->toArray(true),200);
	}
	public function addUser($request,$response,$args){
		$id = (int)$args['id'];
		if($id> 0){
			$user = User::find($id);
		}
		$users = User::get();
		return $this->view->render($response,'admin/store/adduser.twig',['users' => $users,'user'=>$user]);
	}
	public function delUser($request,$response){
		$userID = $request->getParam('id');
		if($userID > 1){
			User::where('id',$userID)->delete();
		}
		return $response->withJson(['message' => 'ok'],200);
	}
	public function updateUser($request,$response,$args){
		$id = (int)$args['id'];
		$data = $request->getParsedBody();
		if($id >0 ){
			$user = User::find($id);
			$user->password = md5($data['passwd']);
			$user->save();
			return $this->success('保存成功');
		}
		$user = User::where('username',$data['name'])->first();
		if($user){ return $this->error('此用户已存在');}
		$user = User::create([
			'username' => $data['name'],
			'password' => md5($data['passwd']),
			'status' => $data['status'] ?: 0
		]);
		return $this->success('添加成功');
	}
	public function getEdit($request,$response,$args){
		$store = Store::find((int)$args['id']);
		$store_list = Store::get(['id','name']);
		$mc_fans = DB::table('mc_mapping_fans')->get(['openid','nickname'])->toArray();
		foreach($mc_fans as $key =>$val){
			$mc_fans[$key]->value = $val->openid .'|'. $val->nickname;
		}
		return $this->view->render($response,'admin/store/edit.twig',[
			'store' => $store,
			'store_list' => $store_list,
			'fans' => $mc_fans
		]);
	}
	public function getApplyLog($request,$response,$args){
		$list = ApplyLog::all();
		$stores = Store::all(['id','name'])->toArray();
		return $this->view->render($response,'admin/store/applylog.twig',[
			'list' => $list,
			'store_list' => array_column($stores,'name','id')
		]);
	}
	public function postEdit($request,$response,$args){
		$store_id = (int)$args['id'];

		$object = $request->getParsedBody();
		if($store_id > 0)
			{$store = Store::find($store_id);}
		else{
			$temp = Store::where('name',$object['name'])->first();
			if($temp) {
				return $this->error('店铺名已存在');
			}
			$store = new Store;
			$store->uniacid = 2;
			$store->auditors = serialize(array());
			$store->boss = serialize(array());
			$store->sub_mch_id = '';
			$store->sub_appid = '';
			$store->paytime = 0;
		}
		if(!$store){
			$this->error('账户不存在');
		}
		$stores = Store::where('account',$object['account'])->get(['id'])->toArray();
		$store->dicount = $object['dicount'] ?:0;
		$store->name = $object['name'];
		$store->logo = $object['logo'] ?:'';
		$store->jump_url = $object['jump_url'] ?:'';
		$store->bank = $object['bank'];
		$store->bankperson = $object['bankperson'];
		$store->banknum = $object['banknum'];
		if($store_id == 0 && count($stores) > 0 ){
			return $this->error('账户已存在');
		}
		elseif(count($stores) == 0 || $stores[0]['id'] == $store->id){
			$store->account = $object['account'];
		}else {
			return $this->error('账户名已存在');
		}
		$store->instruction = $object['instruction'];
		$store->notes = $object['notes'] ?:'';
		$store->instruction = $object['instruction'];
		$store->enable_remark = $object['enable_remark'] ?: 0;
		$store->auto_jump = $object['auto_jump'] ?: 0;
		if(is_array($object['notify_fans'])){
			$store->notify_fans = serialize($object['notify_fans']);
		}else{
			$store->notify_fans = serialize(array());
		}
		if(is_array($object['subid'])){
			$store->subID = serialize($object['subid']);
		}else{
			$store->subID =serialize(array());
		}
		if(isset($object['passwd']) && !empty($object['passwd'])){
			$store->passwd = md5($object['passwd']);
		}
		if($store->save()){
			return $this->success('保存成功');
		}
		
		return $this->error('保存失败');
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
		$isAdmin = $this->auth->isAdmin();
		if($isAdmin){
			$store_list = DB::table('czt_store_cashier_store')->get(['id','name'])->toArray();
		}else{
			$subids = unserialize($this->auth->user()->subID);
			$store_list = DB::table('czt_store_cashier_store')->whereIn('id',$subids)->get(['id','name'])->toArray();
		}
		
		$page = (int)$request->getParam('page');
		$page= $page > 0 ? $page :1 ;
		$skip = ($page-1)*20;
		$store = array_column($store_list,'name','id');
		$query =  StoreLog::orderBy('id','desc')->where('status',1);
		$start  = $request->getParam('startDate');
		$subID = $request->getParam('subId');
		$orderNo = $request->getParam('orderNo');
		$money = StoreLog::where('status',1);
		if($start){
			$query = $query->where('create_time','>',strtotime($start))->where('create_time','<=',strtotime($request->getParam('endDate') .' 23:59:59'));
		}
		if(!empty($orderNo)){
			$query = $query->where("tid",$orderNo);
		}
		if (!empty($subID)) {
			$query = $query->whereIn('sid',[$subID]);

		}elseif(!$this->auth->isAdmin()){
			$subids = unserialize($this->auth->user()->subID);
			if(!empty($subids)){
				$query = $query->whereIn('sid',$subids);
				$money = $money->whereIn('sid',$subids);
			}
			
		}
		$list = $query->paginate(20,['*'],'page',$page);
		$list->setPath($request->getUri());
		#var_dump($list->toArray());
		return $this->view->render($response,'admin/store/logging.twig',[
			'log_list' => $list,
			'store_list' => $store,
			'stores' => $store_list,
			'subId' => $subID,
			'money' => $money->sum('fee')
		]);
	}
	
}