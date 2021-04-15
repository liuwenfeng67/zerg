<?php
namespace app\api\validate;

/**
* count 数量验证器
*/
class Count extends BaseValidate
{
	protected $rule = [
		'count' => 'isPositiveInteger|between:1,15'
	];

	protected $message = [
		'count.isPositiveInteger' => '数量count必须为正整数',
		'count.between' => '数量count必须在1到15之间'
	];
}