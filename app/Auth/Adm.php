<?php 
namespace App\Auth;


/**
* 
*/
class Adm
{
	
	public function user(){
		return Admin::find($_SESSION['adm'] ?:0);
	}
	public function check(){
		return isset($_SESSION['adm']);
	}
	public function logoff(){
		return unset($_SESSION['adm']);
	}

}