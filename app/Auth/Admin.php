<?php 
namespace App\Auth;


/**
*  Auth Admin class 
*/
class Admin
{
	
	public static function user(){
		return \App\Model\Admin::find($_SESSION['adm_id'] ?: 0);
	}
	public function check(){
		return isset($_SESSION['adm_id']);
	}
	public function logoff(){
		unset($_SESSION['adm_id']);
		return true;
	}
	public function login($username,$password){
		$bool = \App\Models\Admin::where('username',$username)->where('password',md5($password))->first() ?: false;
		return $bool;
	}

}