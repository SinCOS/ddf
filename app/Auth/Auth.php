<?php

namespace App\Auth;

use App\Models\User;

use App\Models\Store;
/**
*
*/
class Auth
{
	protected $user = null;
	protected $type = 'user';
	public function user()
	{	
	
		
			if($this->isAdmin()){
				$this->user = User::find(isset($_SESSION['user']) ? $_SESSION['user'] : 0);
			}else{
				$this->user = Store::find(isset($_SESSION['user']) ? $_SESSION['user'] : 0);
			}
			
		
		return $this->user;
	}
	public function isAdmin(){
		return !isset($_SESSION['sub']);
	}
	public function id(){
		return isset($_SESSION['user']) ? $_SESSION['user'] : 0;
	}
	public function check()
	{
		return isset($_SESSION['user']);
	}

	public function attempt($email,$password,$type = 'user')
	{
		if($type=='user'){
			$user = User::where('username',$email)->first();
		}else{
			$user = Store::where('account',$email)->first();
		}
		echo $user->account;

		if (!$user) {
			return false;
		}
		if($type == 'user'){
			if (md5($password) == $user->password) {
			$_SESSION['user'] = $user->id;
			return true;
			}
		}else{
				if (md5($password) == $user->passwd) {
			$_SESSION['user'] = $user->id;
			$_SESSION['sub'] = $user->account;
			return true;
			}
		}
		

		return false;
	}

	public function logout()
	{
		unset($_SESSION['user']);
		if(isset($_SESSION['sub'])){
			unset($_SESSION['sub']);
		}
	}
}