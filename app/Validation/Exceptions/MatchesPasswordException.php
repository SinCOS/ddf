<?php 

namespace App\Validation\Exceptions;


use Respect\Validation\Exceptions\ValidationException;
/**
* 
*/
class MatchesPasswordException extends ValidationException
{
	
	public static $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} 不匹配',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} 不匹配',
        ]
    ];
}