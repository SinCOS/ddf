<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class tjrAvailableException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '推荐人不存在',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '推荐人不存在',
        ]
    ];
}