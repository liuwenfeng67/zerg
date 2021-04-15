<?php
namespace app\api\model;
use think\Model;

/**
* 
*/
class Product extends BaseModel
{	
	// 隐藏产品字段
	protected $hidden = ['delete_time','main_img_id','pivot','from','category_id','create_time','update_time'];

	// 获取器
	public function getMainImgUrlAttr($value,$data){
		return $this->prefixImgUrl($value,$data);
	}

	// 产品一对多关联产品图片表
	public function imgs(){
		return $this->hasMany('ProductImage','product_id','id');
	}
	// 产品一对多关联产品属性表 
	public function properties(){
		return $this->hasMany('ProductProperty','product_id','id');
	}
	// 获取最新商品 
	public static function getMostRecent($count){
		$products = self::order('create_time desc')->limit($count)->select();
		return $products;
	}
	// 根据分类ID获取商品
	public static function getProductsByCategoryID($categoryID){
		$products = self::where('category_id','=',$categoryID)->select();
		return $products;
	}
	// 获取商品详情
	public static function getProductDetail($id){
		// 闭包函数构建查询器
		$product = self::with(['imgs'=>function($query){
			$query->with(['imgUrl'])->order('order asc');
		}])
		->with(['properties'])
		->find($id);
		return $product;
	}
}