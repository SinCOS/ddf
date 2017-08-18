<?php 

namespace App\Controllers;

use App\Models\User;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as DB;
class UserController extends Controller
{
	public function getList($request,$response)
	{
		$nickname = $request->getParam('nickname');
		$mc_fans = DB::table('mc_mapping_fans')->where('nickname','like',"%{$nickname}%")->get(['openid','nickname'])->toArray();
		foreach($mc_fans as $key =>$val){
			$mc_fans[$key]->value = $val->openid .'|'. $val->nickname;
		}
		return $response->withJson(['data'=> $mc_fans?: []]);
	}
	public function info($request,$response)
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
	public function bank($request,$response){
		$user = $this->auth->user();
		if($request->isPost() && $request->isXhr() ){
		$validation = $this->validator->validate($request,[
			'user' => v::noWhitespace()->notEmpty(),
			'where' => v::noWhitespace()->notEmpty(),
			'code' => v::noWhitespace()->notEmpty(),
		]);
		if ($validation->failed()) {
			return $response->withJson(['code'=>400,'data'=> $validation->errors()]);
		}
		$data['user'] = $request->getParam('user');
		$data['where'] = $request->getParam('where');
		$data['code'] = $request->getParam('code');
		$user->bank = json_encode($data);
		if($user->save()){
			return $response->withJson(['code'=>200,'data'=> ['error'=>'保存成功']]);
		}
			return $response->withJson(['code'=>400,'data'=> ['error'=>'保存失败']]);
		}
		$bank = json_decode($user->bank,true);
		return $this->view->render($response,'user/bank.twig',[
			'user' => $user,
			'bank' => $bank
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