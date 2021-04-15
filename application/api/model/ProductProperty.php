<?php
namespace app\api\model;

use app\api\model\BaseModel;
/**
* 产品属性模型
*/
class ProductProperty extends BaseModel
{
	
	protected $hidden = ['product_id','delete_time','id'];
}