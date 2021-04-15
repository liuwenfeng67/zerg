<?php
namespace app\lib\exception;
use app\lib\exception\BaseException;
/**
* 禁止访问
*/
class ForbiddenException extends BaseException
{
	
	public $code = 403;
	public $msg = '权限不够,禁止访问';
	public $errorCode = 10001;
}