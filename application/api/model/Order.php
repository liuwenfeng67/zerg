<?php
namespace app\api\model;

use app\api\model\BaseModel;

/**
* 订单模型
*/
class Order extends BaseModel
{
	
	protected $hidden = ['user_id','delete_time','update_time'];
	// 设置自动写入数据库时间 create_time update_time delete_time
	protected $autoWriteTimestamp = true;


	// 获取用户订单列表
	public static function getSummaryByUser($uid,$page=1,$size=15){
		$pagingDate = self::where('user_id','=',$uid)->order('create_time desc')->paginate($size,true,['page'=>$page]);
		return $pagingDate;
	}

	// 获取全部订单列表
	public static function getSummaryByPage($page=1,$size=20){
		$pagingDate = self::order('create_time desc')->paginate($size,true,['page'=>$page]);
		return $pagingDate;
	}

	// 获取器（读取器）
	public function getSnapItemsAttr($value){
		if (empty($value)) {
			return null;
		}
		return json_decode($value);
	}
	// 获取器（读取器）
	public function getSnapAddressAttr($value){
		if (empty($value)) {
			return null;
		}
		return json_decode($value);
	}

}