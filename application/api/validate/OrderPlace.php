<?php
namespace app\api\validate;

use app\api\validate\BaseValidate;
use app\lib\exception\ParameterException;

/**
* 订单请求验证器
*/
class OrderPlace extends BaseValidate
{	
	// protected $products = [
	// 	['product_id' => 1,'count' => 2],
	// 	['product_id' => 2,'count' => 5],
	// 	['product_id' => 3,'count' => 6],
	// ];
	// 验证规则，验证订单数组
	protected $rule = [
		'products' => 'checkProducts'
	];
	
	// 自定义验证规则，给BaseValidate验证
	protected $singlerule = [
		'product_id' => 'require|isPositiveInteger',
		'count' => 'require|isPositiveInteger',
	];
	// 自定义验证规则验证订单商品列表
	protected function checkProducts($values){
		if (!is_array($values)) {
			throw new ParameterException([
					'msg' => '商品参数不正确,需数组参数'
				]);		
		}
		if (empty($values)) {
			throw new ParameterException([
					'msg' => '商品列表不得为空'
				]);	
		}
		foreach ($values as $value) {
			$this->checkProduct($value);
		}
		return true;
	}
	protected function checkProduct($value){
		$validate = new BaseValidate($this->singlerule);
		$result = $validate->check($value);
		if (!$result) {
			throw new ParameterException([
					'msg' => '商品参数列表错误'
				]);
		}
	}
}