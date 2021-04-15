<?php
namespace app\lib\exception;

use app\lib\exception\BaseException;
/**
* 专题异常类
*/
class ThemeException extends BaseException
{
	public $code = 400;
	public $msg = '请求的主题不存在,请检查主题ID';
	public $errorCode = 30000;
	
}