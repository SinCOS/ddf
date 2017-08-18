<?php

namespace App\Controllers;

use App\Library\Pay;
use App\Models\Store;
use App\Models\StoreLog;
use App\Library\wechatSetting;
use EasyWeChat\Foundation\Application;
/**
* 
*/
class OrderController extends Controller
{
	public function getPay($request,$response,$args){
		$sid = (int)$args['id'];
		$tid = (int)$request->getParam('tid');
		$fee = (float)$request->getParam('fee');
		$store = Store::find($sid);
		$order = StoreLog::find($tid);
		if(!$store){
			return $this->error('店铺不存在');
		}
		$is_sub = empty($store->sub_much_id) ? 0: 1;
		$OrderParam = [
			'tid' => $tid,
			'title' => "{$store->name} -收款",
			'fee' => $fee,
		];
	Pay::init();
	$payType = 'aliPay';
	$openID = null;
	if($this->is_weixin()){
		if(!isset($_SESSION['wechat_user'])){
				$_SESSION['target_url'] = $request->getUri();
				$setting = wechatSetting::getSetting();
				$options = [
				'debug' => false,
				'app_id' => $setting->key,
				'secret' => $setting->secret,
		    	'token'  => $setting->token,
		    	 'oauth' => [
		      		'scopes'   => ['snsapi_base'],
		      		'callback' => '/oauth_callback',
		  			]
				];
			$app = new Application($options);
			$app->oauth->redirect()->send();
		}
		$openID = $_SESSION['wechat_user']['id'];
		$payType = 'jsPay';
	};
	$tempOrderID = Pay::getMillisecond();
	if(!$order){
		$order = StoreLog::create([
			'tid' => $tid,
			'fee' => $fee,
			'status' => 0,
			'sid' => $sid,
			'remark' => $request->getParam('remark') ?:'',
			'uniacid' => 2,
			'transaction_id' => '',
			'uniontid' => $tempOrderID,
			'openid' => $openid ?: 'alipay',
			'type' => $this->is_weixin() ? 'wechat' :'alipay'
		]);
	}else{
		$order->uniontid = $tempOrderID;
		$order->save();
	}
	
	try {
		$result =  Pay::pushOrder($fee * 100,$payType,$tempOrderID,'6666',"支付给{$store->name} $price 元",$openID);
	} catch (Exception $e) {
		return $response->write($e->getMessage());
	}

	#var_dump($result);

#	return $response;
	#echo $result['codeStr'];
	
	#echo $result['codeStr'];
	#return $response->withJson($result);
	if($result['errCode'] == '00'){
		return $response->withRedirect($result['codeStr']);
	}
	return $response->withJson($result);


	}
	public function postOrderNotify($request,$response){
		$ipaddress = $request->getServerParam('REMOTE_ADDR');
		$params = $request->getParsedBody();
		$this->log->addInfo($ipaddress,$params);
		$this->log->addInfo(file_get_contents('php://input'));
		if($params['respCode'] == '00'){
			#echo base64_decode($params['result_json']);
			$decoded = Pay::decrypt($params['result_json']);
			if($decoded && isset($decoded['errCode']) && $decoded['errCode'] == '00'){
				$orderID = $decoded['orderId'];
				$order = StoreLog::where('uniontid',$orderID)->first();
				$order->param = json_encode($decoded);
				if($order->openid == 'alipay'){
					$order->openid = $decoded['openid'];
				}
				$bankOrderId = $decoded['bankOrderId'];

			}
		
			return $response->write($decoded);
		}
		#$result = Pay::parseResult(file_get_contents('php://input'),true);
		#return $response->withJson($result);
	}
}