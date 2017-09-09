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
	$this->get('/register','AuthController:getSignUp')->setName('auth.signup');
	$this->post('/register','AuthController:postSignUp');

	$this->get('/login','AuthController:getSignIn')->setName('auth.signin');
	$this->post('/login','AuthController:postSignIn');
})->add(new GuestMiddleware($container));
$app->get('/pay/666','\App\Controllers\OrderController:getPay666');
$app->post('/order/notify','\App\Controllers\OrderController:postOrderNotify');
$app->get('/user/weixin','\App\Controllers\UserController:getList');
$app->get('/pay/{id:[0-9]+}.html','\App\Controllers\OrderController:getPay');
$app->get('/qrcode',function($request,$response){
		return $this->view->render($response,'qrcode.twig',[
		'src' => $request->getParam('src') ?: getenv('WEB_ROOT')
	]);
});

$app->get('/666',function($request,$response){
	session_destroy();
	return $response->write('666');
});
$app->group('/admin',function()use($app){
	$this->get('/store_cashier/list','StoreCashierController:getList')->setName('admin.store.list');
	$this->get('/store_cashier/edit/{id:[0-9]+}','StoreCashierController:getEdit')->setName('admin.store.edit');
	$this->post('/store_cashier/edit/{id:[0-9]+}','StoreCashierController:postEdit');
	$this->get('/store_cashier/logging','StoreCashierController:getLogging')->setName('admin.store.logging');
	$this->get('/store_cashier/logging/{id:[0-9]+}','StoreCashierController:getDetail')->setName('admin.store.logging.detail');
	$this->get('/user/edit/{id:[0-9]+}','StoreCashierController:addUser')->setName('admin.store.adduser');
	$this->post('/user/edit/{id:[0-9]+}','StoreCashierController:updateUser');
	$this->post('/user/del','StoreCashierController:delUser')->setName('admin.store.deluser');
	$this->get('/do/exchange','StoreCashierController:doExchange')->setName("tixian");
	$this->get('/applylog/list','StoreCashierController:getApplylog')->setName('admin.store.applylog.list');
})->add(new AuthMiddleware($container));
$app->group('',function(){
	$this->get('/store/{id:[0-9]+}/qrcode','StoreFontController:getQrcode')->setName('store.qrcode');
	$this->get('/{id:[0-9]+}','StoreFontController:getStore');
});
$app->group('/auth',function () {
	$this->get('/signout','AuthController:getSignOut')->setName('auth.signout');

	$this->get('/password/change','PasswordController:getChangePassword')->setName('auth.password.change');
	$this->post('/password/change','PasswordController:postChangePassword');
})->add(new AuthMiddleware($container));
$app->post('/upload',function($request,$response){
	$files = $request->getUploadedFiles();
	if(empty($files['newfile'])){
		return $response->withJson(['message'=>'文件不存在'],400);
	}
	$newfile = $files['newfile'];
	if($newfile->getError() === UPLOAD_ERR_OK){
		$uploadFilename = $newfile->getClientFilename();
		$extension = pathinfo($uploadFilename,PATHINFO_EXTENSION);
		$basename = bin2hex(random_bytes(9));
		$filename = sprintf('%s.%0.8s',$basename,$extension);
		$dir = "public/upload/images/" . $filename;
		//return $response->withJson(['message'=>'ok','data' => __DIR__.'/../public/upload/images/'] ,200);
		$newfile->moveTo(__DIR__.'/../public/upload/images/' . $filename);
		return $response->withJson(['message'=>'ok','url' => getenv('WEB_ROOT') ."/$dir"],200);
	}
	return $response->withJson(['message' =>'error'],400);

});


