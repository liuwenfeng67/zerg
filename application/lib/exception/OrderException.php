<?php
namespace app\lib\exception;

use app\lib\exception\BaseException;

/**
* 订单异常
*/
class OrderException extends BaseException
{
	
	public $code = 404;
	public $msg = '订单不存在，请检查ID';
	public $errorCode = 80000;
}