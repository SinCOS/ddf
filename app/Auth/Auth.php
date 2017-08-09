<?php

namespace App\Auth;

use App\Models\User;
/**
*
*/
class Auth
{
	protected $user = null;
	public function user()
	{	
		if(!$this->user){
			$this->user = User::find(isset($_SESSION['user']) ? $_SESSION['user'] : 0);
		}
		return $this->user;
	}
	public function id(){
		return isset($_SESSION['user']) ? $_SESSION['user'] : 0;
	}
	public function check()
	{
		return isset($_SESSION['user']);
	}

	public function attempt($email,$password)
	{
		$user = User::where('username',$email)->first();

		if (!$user) {
			return false;
		}
		if (md5($password) == $user->password) {
			$_SESSION['user'] = $user->id;
			return true;
		}

		return false;
	}

	public function logout()
	{
		unset($_SESSION['user']);
	}
}