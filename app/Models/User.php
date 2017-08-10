<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

	protected $table = 'musers';
	#protected $primaryKey = 'uid';
	public $first_name;

	public $last_name;

	public $email;
	protected $guarded = [];
	protected $hidden = [
		'password'
	];
	protected $fillable = [
		'mobile',
		'username',
		'weixin',
		'realname',
		'password',
		'status'
	];
	protected $dateFormat = 'U';

	public function setPassword($password)
	{
		$this->update([
			'password' => md5($password)
		]);
	}
	public function getEmailVariables()
	{
	return [
		'full_name' => $this->getFullName(),
		'email' => $this->getEmail(),
	];
	}
}