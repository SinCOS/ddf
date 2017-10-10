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
		return unset($_SESSION['adm_id']);
	}

}