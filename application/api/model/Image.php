<?php
namespace app\api\model;
use think\Model;
// use app\api\model\BaseModel;
/**
* 
*/
class Image extends BaseModel
{
	// 隐藏字段
	protected $hidden = ['id','from','delete_time','update_time'];

	// 读取器 获取器的作用是在获取数据的字段值后自动进行处理
	public function getUrlAttr($value,$data){
		return $this->prefixImgUrl($value,$data);
	}
}