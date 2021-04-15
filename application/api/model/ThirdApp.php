<?php
namespace app\api\model;
use app\api\model\BaseModel;

/**
* 第三方应用 模型  cms管理员模型
*/
class ThirdApp extends BaseModel
{
	// 与数据库比对用户的账号密码
	public static function check($ac,$se){
		//两个where  相当于且的意思
		$app = self::where('app_id','=',$ac)
			->where('app_secret','=',$se)
			->find();
		return $app;
	}
}