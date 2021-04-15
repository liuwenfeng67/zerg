<?php
namespace app\api\validate;

use app\api\validate\BaseValidate;
/**
* ID验证器
*/
class IDMustBePostiveInt extends BaseValidate
{
	
	// 验证规则
	protected $rule = [
		'id' => 'require|isPositiveInteger',
		// 'num' => 'in:1,2,3',   //必须在 1,2,3 范围内
	];
	// 验证信息
	protected $message = [
		'id.require' => 'id必须',
		'id.isPositiveInteger' => 'id必须为正整数'
	];
	
}