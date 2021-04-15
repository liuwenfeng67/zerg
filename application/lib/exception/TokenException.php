<?php
namespace app\lib\exception;

/**
* Token异常类
*/
class TokenException extends BaseException
{
	
	public $code = 401;
	public $msg = 'Token已过期或者无效';
	public $errorCode = 10001;
}