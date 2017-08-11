<?php
use App\Middleware\GuestMiddleware;
use App\Middleware\AuthMiddleware;
use App\Models\Store;
use App\Library\wechatSetting;
use EasyWeChat\Foundation\Application;
use App\Library\Pay;
$app->get('/','HomeController:index')->setName('home');
$app->get('/app','HomeController:start')->setName("start");
$app->group('',function () {
	$this->get('/auth/signup','AuthController:getSignUp')->setName('auth.signup');
	$this->post('/auth/signup','AuthController:postSignUp');

	$this->get('/auth/signin','AuthController:getSignIn')->setName('auth.signin');
	$this->post('/auth/signin','AuthController:postSignIn');
})->add(new GuestMiddleware($container));
function is_weixin(){ 
    if ( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
            return true;
    }   
    return false;
}

$app->get('/pay',function($request,$response){
	Pay::init();
	$payType = 'aliPay';
	if(is_weixin()){
		$payType = 'jsPay';
	}
	$result =  Pay::pushOrder(100,$payType,Pay::getMillisecond(),'6666');
	if($result['errCode'] == '00'){
		return $response->withRedirect($result['codeStr']);
	}


	return $response->withJson($result);
    

});
$app->get('/qrcode',function($request,$response){

	return $this->view->render($response,'qrcode.twig',[
		'src' => $request->getParam('src') ?: '' 
	]);
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
	$this->put('/store_cashier/edit','StoreCashierController:putEdit');
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



