<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class EmailAvailableException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} 已存在',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} 已存在',
        ]
    ];
}