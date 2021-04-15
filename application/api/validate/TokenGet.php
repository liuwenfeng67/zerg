<?php
namespace app\api\validate;

/**
* Token
*/
class TokenGet extends BaseValidate
{
	
	protected $rule = [
		'code' => 'require|isNotEmpty'
	];
	
	protected $message = [
		'code.require' => 'code必须',
		'code.isNotEmpty' => '没有code还想获取Token,做梦哦'
	];
}