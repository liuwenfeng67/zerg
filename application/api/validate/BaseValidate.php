<?php
namespace app\api\validate;
use think\Validate;
use think\Exception;
use think\Request;
use app\lib\exception\ParameterException;
/**
* 基础验证器
*/
class BaseValidate extends Validate
{
	
	public function goCheck(){
		// 获取http传入的参数
		// 对这些参数做检验
		$request = Request::instance();
		// 获取当前请求的所有变量（经过过滤）
		$params = $request->param();

		$result = $this->batch()->check($params);
		if (!$result) {
			$e = new ParameterException([
					'msg' => $this->error,
				]);
			// $e->msg = $this->error;
			// $e->errorCode = 1002;
			throw $e;//自定义异常抛出
			// $error = $this->error;
			// throw new Exception($error);//抛出异常
		}else{
			return true;
		}

	}

	/**
	 * 自定义验证规则
	 * $value 验证数据
	 * $rule  验证规则
	 * $data	全部数据（数组）
	 * $field	字段名
	 */
	protected function isPositiveInteger($value,$rule='',$data='',$field=''){
		// is_numeric() 函数用于检测变量是否为数字或数字字符串
		// is_int 是否整型  $value+0 字符串转化数值变量 
		if (is_numeric($value) && is_int($value + 0) && ($value + 0)>0) {
			return true;
		}else{
			return false;
			// return $field.'必须是正整数';
		}
	}

	// 是否不为空
	protected function isNotEmpty($value,$rule='',$data='',$field=''){
		if (empty($value)) {
			return false;
		}else{
			return true;
		}
	}

	// 通过规则获取数据
	public function getDataByRule($arrays){
		//   | or  或
		if (array_key_exists('user_id', $arrays) | array_key_exists('uid', $arrays)) {
			// 不允许包含user_id 或者uid ,防止恶意覆盖user_id外键
			throw new ParameterException([
					'msg' => '参数中有非法的参数名user_id或者uid '
				]);		
		}

		$newArray = [];
		// 只取规则里面的参数
		foreach ($this->rule as $key => $value) {
			$newArray[$key] = $arrays[$key];
		}
		return $newArray;
	}

	// 判断是否为手机号
	public function isMobile($value){
		$rule = '/^1[3456789]\d{9}$/';
		// preg_match — 执行匹配正则表达式
		$result = preg_match($rule, $value);
		if ($result) {
			return true;
		}else{
			return false;
		}
	}

}