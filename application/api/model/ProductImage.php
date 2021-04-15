<?php
namespace app\api\model;

use app\api\model\BaseModel;
/**
* 产品详情图片模型
*/
class ProductImage extends BaseModel
{
	protected $hidden = ['img_id','delete_time','product_id'];
	// 产品图片关联图片表 一对一
	public function imgUrl(){
		return $this->belongsTo('Image','img_id','id');
	}
}