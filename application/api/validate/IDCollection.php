<?php
namespace app\api\validate;
use app\api\validate\BaseValidate;
/**
* ID集
*/
class IDCollection extends BaseValidate
{
	// 规则
	protected $rule = [
		'ids' => 'require|checkIDs'
	];
	// 错误信息
	protected $message = [
		'ids.require' => 'ids必须',
		'ids.checkIDs' => 'ids必须为以逗号分割的多个正整数'
	];
	// 自定义方法验证id集
	protected function checkIDs($value){
		$values = explode(',', $value);//字符串以逗号分割为数组
		if (empty($values)) {
			return false;
		}
		foreach ($values as $id) {
			if (!$this->isPositiveInteger($id)) {
				return false;
			}
		}
		return true;
	}

}