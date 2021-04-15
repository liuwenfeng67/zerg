<?php
namespace app\api\model;

use app\api\model\BaseModel;
/**
* 商品分类模型
*/
class Category extends BaseModel
{
	protected $hidden = ['delete_time','update_time'];	
	// 一对一关联image模型
	public function img(){
		return $this->belongsTo('Image','topic_img_id','id');
	}
}