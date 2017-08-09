<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class MobileAvailableException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '此手机号已存在',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '此手机号已存在',
        ]
    ];
}