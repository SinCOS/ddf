<?php


namespace App\Controllers;

use App\Models\User;

class HomeController extends Controller
{
	public function index($request,$response)
	{
		
		return $this->view->render($response,'home.twig');
	}
	public function shop($request,$response){
		return $this->view->render($response,'shop.twig');
	}
	public function qrcode($request,$resposne){
		
	}
	
}