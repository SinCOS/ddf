<?php
use App\Middleware\GuestMiddleware;
use App\Middleware\AuthMiddleware;
use App\Models\Store;
use App\Library\wechatSetting;
use EasyWeChat\Foundation\Application;
use App\Library\Pay;
$app->get('/ddf','HomeController:index')->setName('home');
$app->get('/app','HomeController:start')->setName("start");
$app->group('',function () {
	$this->get('/auth/signup','AuthController:getSignUp')->setName('auth.signup');
	$this->post('/auth/signup','AuthController:postSignUp');

	$this->get('/auth/signin','AuthController:getSignIn')->setName('auth.signin');
	$this->post('/auth/signin','AuthController:postSignIn');
})->add(new GuestMiddleware($container));

$app->post('/order/notify','\App\Controllers\OrderController:postOrderNotify');
$app->get('/user/weixin','\App\Controllers\UserController:getList');
$app->get('/pay/{id:[0-9]+}','\App\Controllers\OrderController:getPay');
$app->get('/qrcode',function($request,$response){
		return $this->view->render($response,'qrcode.twig',[
		'src' => $request->getParam('src') ?: getenv('WEB_ROOT')
	]);
});
$app->get('/oauth_callback',function($request,$response){
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
	$oauth = $app->oauth;
	$user = $oauth->user();
	$_SESSION['wechat_user'] = $user->toArray();
	$targetUrl = empty($_SESSION['target_url']) ? '/' : $_SESSION['target_url'];
	return $response->withRedirect($target_url);
});
$app->get('/666',function($request,$response){
	if(!isset($_SESSION['wechat_user'])){
		 $_SESSION['target_url'] = '/666';

	}
	var_dump($_SESSION['wechat_user']);
	echo "string";
	return $response->write('666');
});
$app->get('/test',function($request,$response){
	$setting = wechatSetting::getSetting();
	$options = [
		'debug' => false,
		'app_id' => $setting->key,
		'secret' => $setting->secret,
    	'token'  => $setting->token,
	];
	$app = new Application($options);
	$user = $app->user;
	return $response->withJson($user->lists());
});
$app->group('/admin',function()use($app){
	$this->get('/store_cashier/list','StoreCashierController:getList')->setName('admin.store.list');
	$this->get('/store_cashier/edit/{id:[0-9]+}','StoreCashierController:getEdit')->setName('admin.store.edit');
	$this->post('/store_cashier/edit/{id:[0-9]+}','StoreCashierController:postEdit');
	$this->get('/store_cashier/logging','StoreCashierController:getLogging')->setName('admin.store.logging');
	$this->get('/store_cashier/logging/{id:[0-9]+}','StoreCashierController:getDetail')->setName('admin.store.logging.detail');

})->add(new AuthMiddleware($container));
$app->group('',function(){
	$this->get('/store/{id:[0-9]+}/qrcode','StoreFontController:getQrcode')->setName('store.qrcode');
	$this->get('/store/{id:[0-9]+}','StoreFontController:getStore');
});
$app->group('',function () {
	$this->get('/auth/signout','AuthController:getSignOut')->setName('auth.signout');

	$this->get('/auth/password/change','PasswordController:getChangePassword')->setName('auth.password.change');
	$this->post('/auth/password/change','PasswordController:postChangePassword');
})->add(new AuthMiddleware($container));



