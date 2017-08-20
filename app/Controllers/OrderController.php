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
		$result =  Pay::pushOrder($fee * 100,$payType,$tempOrderID,'6666',"支付给{$store->name} $fee 元",$openID);
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
		
		$this->log->addInfo($ipaddress,$params ?: []);
		$this->log->addInfo(file_get_contents('php://input'));
		Pay::init();
		$params = Pay::parseResult(file_get_contents('php://input'),true);
		if(isset($params['errCode']) && $params['errCode'] == '00' && $params['merchantId'] == Pay::$merChantId){
			#echo base64_decode($params['result_json']);
			
				$orderID = $params['orderId'];
				if($params['tradeStatus'] == 3){
					$order = StoreLog::where('uniontid',$orderID)->first();
					if(!$order){
						return $response->write('orderId no found !!');
					}
					// if($order->status > 0){
					// 	return $response->write('status error !!!');
					// }
					$order->param = json_encode($params);
					$openid = $order->openid;
					if($order->openid == 'alipay'){
						$order->openid = $params['openid'];
					}
					$order->status = 1;
					$order->transaction_id = $params['orderNo'];
					if($order->save()){
						$store = Store::find($order->sid);
						$fans = unserialize($store->notify_fans);
						$setting = wechatSetting::getSetting();
						if (!empty($fans)){
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
							$notice = $app->notice;
							$tpl_id = 'qHVFAIOP0VfUZXyXoEAVCcjQaPcWC7yV-Xfd9i5vBZI';
							foreach($fans as $key => $value){
								$tmp = explode('|',$value);
									$messageId = $notice->send([
								        'touser' => $tmp[0],
								        'template_id' => $tpl_id,
								        'url' => 'xxxxx',
								        'data' => [
								            'first' => ['value'=> $store->name. ' - 订单支付成功', 'color' => '#173177'],
								            'keyword1' => ['value' => $store->name, 'color' => '#173177'],
								            'keyword2' => ['value' => $order->fee, 'color' => '#173177'],
								            'keyword3' => ['value' => date('Y-m-d h:i:s', time()), 'color' => '#173177'],
								            'keyword4' => ['value' => $order->tid, 'color' => '#173177'],
								        ],
						    		]);

							}
						
						}
						return $response->withJson($params);
					}
					return $resposne->write('save failed !!');
				}
				
				

		
		
			return $resposne->write('fail');
		}
		#$result = Pay::parseResult(file_get_contents('php://input'),true);
		return $response->withJson($params);
	}
}