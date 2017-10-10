<?php

namespace App\Controllers;

use App\Library\Pay;
use App\Models\Order;
use App\Models\StoreLog;
use App\Models\Good;
use App\Library\wechatSetting;


use EasyWeChat\Foundation\Application;
/**
* 
*/
class OrderController extends Controller
{
	
	public function getList($request,$response,$args){
		return $response->withJson(['data'=> $list ?:[]],200);
	}
	public function addOrder($request,$response,$args){
		$good_id = (int)$args['id'];
		$amount = (int)$args['amount'];
		$amount = $amount > 0 ? $amount : 1;
		$result = false;
		$good = Good::find($good_id);
		if(!$good){
			return $this->error("未能找到该商品");
		}
		try{
		
			DB::beginTransaction();
			try{
				$order = Order::create([
					'orderID' => Pay::getMillisecond(),
					'uid' => $this->auth->id,
					'good_id' => $good->id,
					'status' => 0,
					'price' => $good->price,
					'amount' => $amount,
					'total' => $amount * $good->price,
					'goodString' => $good->toJson(),
					'status' => 0
				]);
				
				DB::commit();
			}catch(\Exception $e){
				DB::rollback();
			}
			
		}catch(\Exception $e){
			$this->logger->addInfo(__FUNCTION__,[$e->getMessage()]);
			$result  = false;
		}
		$good = Good::find($good_id);
		return $response->withJson(['status'=>'ok'], $reuslt == false ? 400 : 200);
	}
	public function getPay($request,$response,$args){
		$orderId = (int)$args['orderId'];
		$order = Order::where('orderID',$orderId)->first();
		if(!$order){
			return $this->error('订单不存在');
		}
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
		
		try {
			Pay::init();
			$result =  Pay::pushOrder($fee * 100,$payType,$tempOrderID,'6666',"支付给{$store->name} $fee 元",$openID);
			$this->log->addInfo('wxpay',$result);
		} catch (\Exception $e) {
			$this->log->addDebug($e->getMessage);
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