<?php 


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
* 
*/
class Admin extends Model
{
	
	protected $table = 'admin_users';
	protected $fillable = [
		'id','username','password','login_ip','login_time','created_at','updated_at','status','email'
	];
	
}