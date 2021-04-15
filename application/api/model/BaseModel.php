<?php
namespace app\api\model;
use think\Model;

/**
* 基类模型
*/
class BaseModel extends Model
{
	
	// 自定义方法 实现读取器（获取器）功能
	public function prefixImgUrl($value,$data){
		$finalUrl = $value;
		if ($data['from'] == 1) {
			$finalUrl = config('setting.img_prefix').$value;
		}
		return $finalUrl;
	}
}