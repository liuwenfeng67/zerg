<?php
namespace app\api\validate;
use app\api\validate\BaseValidate;

/**
* cms 获取token  验证器
*/
class AppTokenGet extends BaseValidate
{
	protected $rule = [
		'ac' => 'require|isNotEmpty',
		'se' => 'require|isNotEmpty'
	];
	
}