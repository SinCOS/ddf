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

	public function getPay666($request,$response,$args){

		$result = isset($_SESSION['wxpay']) ? $_SESSION['wxpay'] :  null;
		if($result == null){return $this->error('非法访问');}
		if((int)$request->getParam('done') == 1){
			$orderID = $result['orderId'];
			$orderLog = StoreLog::where('uniontid',$orderID)->first();
			if($orderLog){
				$store = Store::find($orderLog->sid);
				if($store->auto_jump == 1){
					return $response->withRedirect($store->jump_url);
				}
				return $response->withRedirect(getenv('RETURN_URL'));
			}
			return $response->withRedirect(getenv('RETURN_URL'));
			
		}
		return $this->view->render($response,'store/pay.twig',['params' => $result,'param' => $result['codeUrl1']]);

	}
	public function getPay($request,$response,$args){
		$sid = (int)$args['id'];
		$tid = (int)$request->getParam('tid');
		$fee = (float)$request->getParam('fee');
		$store = Store::find($sid);
		$order = StoreLog::where('tid',$tid)->first();
		if(!$store){
			return $this->error('店铺不存在');
		}
		$is_sub = empty($store->sub_much_id) ? 0: 1;
		$OrderParam = [
			'tid' => $tid,
			'title' => "{$store->name} -收款",
			'fee' => $fee,
		];
		
		$payType = 'aliPay';
		$openID = null;
		$is_weixin = $this->is_weixin();
		if($is_weixin){
			if(empty($_SESSION['wechat_user'])){
					$_SESSION['target_url'] = (string)$request->getUri();
					$setting = wechatSetting::getSetting();
					$options = [
					'debug' => false,
					'app_id' => $setting->key,
					'secret' => $setting->secret,
			    	'token'  => $setting->token,
			    	 'oauth' => [
			      		'scopes'   => ['snsapi_base'],
			      		'callback' => getenv('WEB_ROOT') . '/oauth_callback',
			  			]
					];
				$app = new Application($options);
				return $app->oauth->redirect()->send();
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
				'openid' => $openID ?: 'alipay',
				'type' => $this->is_weixin() ? 'wechat' :'alipay'
			]);
		}else{
			$order->uniontid = $tempOrderID;
			$order->save();
		}
		
		try {
			Pay::init();
			$result =  Pay::pushOrder($fee * 100,$payType,$tempOrderID,'6666',"支付给{$store->name} $fee 元",$openID);
			$this->log->addInfo('wxpay',$result);
		} catch (Exception $e) {
			return $response->write($e->getMessage());
		}


		if($result['errCode'] == '00' && !$is_weixin){
			return $response->withRedirect($result['codeStr']);
		}

		$_SESSION['wxpay'] = $result;
		return $response->withRedirect('/store/pay/666');


		
	// /return $response->withJson($result);


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
					if($order->status > 0){
						return $response->write('ok');
					}
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
								];
							$app = new Application($options);
							$notice = $app->notice;
							$tpl_id = 'qHVFAIOP0VfUZXyXoEAVCcjQaPcWC7yV-Xfd9i5vBZI';
							
							foreach($fans as $key => $value){
								$tmp = explode('|',$value);
								if(empty($tmp)){continue;}
								try {
									$messageId = $notice->send([
								        'touser' => $tmp[0],
								        'template_id' => $tpl_id,
								        'url' => $store->jump_url ?: getenv('RETURN_URL'),
								        'data' => [
								            'first' => ['value'=> $store->name. ' - 订单支付成功', 'color' => '#173177'],
								            'keyword1' => ['value' => $store->name, 'color' => '#173177'],
								            'keyword2' => ['value' => $order->fee, 'color' => '#173177'],
								            'keyword3' => ['value' => date('Y-m-d H:i:s', time()), 'color' => '#173177'],
								            'keyword4' => ['value' => $order->tid, 'color' => '#173177'],
								        ],
						    		]);
						    	} catch (\EasyWeChat\Core\Exceptions\HttpException $e) {
									$this->log->addInfo('HttpException',[$e->getMessage()]);
								}

							}
							
		
						
						}
						return $response->write('ok');
					}
					return $resposne->write('save failed !!');
				}
				
				

		
		
			return $resposne->write('fail');
		}
		#$result = Pay::parseResult(file_get_contents('php://input'),true);
		return $response->withJson('fail');
	}
}