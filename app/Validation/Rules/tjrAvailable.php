<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;
/**
* 
*/
class tjrAvailable extends AbstractRule
{
	
	public function validate($input)  
	{
		return User::where('username',$input)->count() === 1; 

	}
}