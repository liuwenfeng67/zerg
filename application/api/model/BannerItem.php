<?php
namespace app\api\model;

use think\Model;
/**
* 
*/
class BannerItem extends BaseModel
{
	protected $hidden = ['id','img_id','delete_time','banner_id','update_time'];
	public function img(){
		// belongsTo('关联模型名','外键名','关联表主键名',['模型别名定义'],'join类型');
		return $this->belongsTo('Image','img_id','id');
	}
}