<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class UserAvailableException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '用户名 已被使用',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '用户名 已被使用',
        ]
    ];
}