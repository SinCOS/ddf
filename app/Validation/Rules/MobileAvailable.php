<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;
/**
* 
*/
class MobileAvailable extends AbstractRule
{
	
	public function validate($input)
	{
		return User::where('mobile',$input)->count() === 0; 

	}
}