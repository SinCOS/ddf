<?php

namespace App\Controllers\Auth;

use App\Models\User;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;


class AuthController extends Controller
{
	public function getSignOut($request,$response)
	{
		$this->auth->logout();
		return $response->withRedirect($this->router->pathFor('auth.signin'));
	}

	public function getSignIn($request,$response)
	{
		return $this->view->render($response,'auth/signin.twig');
	}

	public function postSignIn($request,$response)
	{

		$auth = $this->auth->attempt(
			$request->getParam('username'),
			$request->getParam('password'),
			$request->getParam('bus') ?: 'user'
		);
		if (!$auth) {
			$this->flash->addMessage('error','登录失败');
			return $response->withRedirect($this->router->pathFor('auth.signin'));
		}
		#return $response->write($request->getParam('bus'));
		return $response->withRedirect($this->router->pathFor('home'));
	}
	public function getOff($request,$response){
		$this->auth->logout();
		return $response->withRedirect('/auth/signin');
	}
	public function getSignUp($request,$response)
	{
		return $this->view->render($response,'auth/signup.twig');
	}

	public function postSignUp($request,$response)
	{

		$validation = $this->validator->validate($request,[
			'username' => v::noWhitespace()->notEmpty()->UserAvailable(),
			'password' => v::noWhitespace()->notEmpty(),
		],[
			'username' =>[
				'noWhitespace' =>'不能为空'
			]
		]);
		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.signup'));
		}
		$user = User::create([
			'username' => $request->getParam('username'),
			'password' => md5($request->getParam('password'))
		]);
		$this->auth->attempt($user->username,$request->getParam('password'));

		return $response->withRedirect($this->router->pathFor('user.home'));
	}
}