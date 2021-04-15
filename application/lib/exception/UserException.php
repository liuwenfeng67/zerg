<?php
namespace app\lib\exception;

use app\lib\exception\BaseException;
/**
* 用户异常类
*/
class UserException extends BaseException
{
	
	protected $code = 404;
	protected $msg = '用户不存在';
	protected $errorCode = 60000;
}