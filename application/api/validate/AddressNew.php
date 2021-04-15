<?php
namespace app\api\validate;
use app\api\validate\BaseValidate;
/**
* 
*/
class AddressNew extends BaseValidate
{
	
	protected $rule = [
		'name' => 'require|isNotEmpty',
		// 'mobile' => 'require|isMobile',
		'mobile' => 'require',
		'province' => 'require|isNotEmpty',
		'city' => 'require|isNotEmpty',
		'country' => 'require|isNotEmpty',
		'detail' => 'require|isNotEmpty'
	];

	protected $message = [
		'name.require' => 'name 必须',
		'name.isNotEmpty' => 'name 不得为空',
		'mobile.require' => 'mobile 必须',
		'mobile.isMobile' => 'mobile 必须为手机号格式'
	];
}