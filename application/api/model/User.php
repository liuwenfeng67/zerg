<?php
namespace app\api\model;

use app\api\model\BaseModel;
/**
* 
*/
class User extends BaseModel
{
	// 用户关联用户地址  一对一
	// 
	public function address(){
		return $this->hasOne('UserAddress','user_id','id');
	}
	// 根据opendID 获取user 用户
	public static function getByOpenID($openid){
		$user = self::where('openid','=',$openid)->find();
		return $user;
	}

}