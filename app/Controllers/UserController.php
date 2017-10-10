<?php 

namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as DB;
class UserController extends Controller
{
	public function modify_Self($request,$response)
	{
		$user = $this->auth->user();
		if($request->isPost() && $request->isXhr() ){	
			$validation = $this->validator->validate($request,[
			'realname' => v::noWhitespace()->notEmpty(),
			'weixin' => v::noWhitespace()->notEmpty(),
			'mobile' => v::noWhitespace()->notEmpty(),
		]);
		if($validation->failed())
		{
			return $response->withJson($validation->errors(),400);
		}
		$user->weixin = $request->getParam('weixin');
		$user->mobile = $request->getParam('mobile');
		$user->realname = $request->getParam('realname');

		if(!$user->save()){return $response->withJson(['code'=>400,'data'=> ['error'=>'保存失败']]);}
		return $response->withJson(['code'=>200,'data'=> ['error'=>'保存成功']]);
		}

		return $this->view->render($response,'user/info.twig',[
			'user' => $user
		]);
	}

	
	public function resetPwd($request,$response){
		if($request->isPost() && $request->isXhr()){
			$validation = $this->validator->validate($request,[
			'password' => v::noWhitespace()->notEmpty(),
			'transpasswd' => v::noWhitespace()->notEmpty(),
			]);

		if($validation->failed())
		{
			return $response->withJson($validation->errors(),400);
		}
		$res = $this->auth->user()->update([
			'id'=> $_SESSION['user'],
			'password' => md5($request->getParam('password')),
			'transpasswd' => md5($request->getParam('transpasswd'))
			]);
			if($res){
				return $response->withJson(['code'=>200,'data'=> ['error'=>'保存成功']]);
			}
			return $response->withJson(['code'=>400,'data'=> ['error'=>'保存失败']]);

		}
		return $this->view->render($response,'user/resetpasswd.twig');
	}
	
}