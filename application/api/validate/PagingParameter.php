<?php
namespace app\api\validate;

use app\api\validate\BaseValidate;
/**
* 订单分页参数验证器
*/
class PagingParameter extends BaseValidate
{
	
	protected $rule = [
		'page' => 'isPositiveInteger',
		'size' => 'isPositiveInteger',
	];

	protected $message = [
		'page.isPositiveInteger' => '分页参数必须是正整数',
		'size.isPositiveInteger' => '分页参数必须是正整数',
	];
}