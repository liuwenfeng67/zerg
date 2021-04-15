<?php
namespace app\api\controller\v1;

use app\api\validate\Count;
use app\api\validate\IDMustBePostiveInt;
use app\api\model\Product as ProductModel;
use app\lib\exception\ProductException;

/**
* 产品控制器
*/
class Product
{
	
	/**
	 * 获取最新产品
	 * @url /product/recent?count = 15
	 */
	public function getRecent($count = 15){
		(new Count())->goCheck();
		$products = ProductModel::getMostRecent($count);
		if ($products->isEmpty()) {
			throw new ProductException();
		}
		//数据库配置文件 配置 数据集返回类型  collection数据集
		$products = $products->hidden(['summary']);
		return $products;
	}
	/**
	 * 获取分类商品
	 * @url /product/by_category?id=
	 */
	public function getAllInCategory($id){
		(new IDMustBePostiveInt())->goCheck();
		$products = ProductModel::getProductsByCategoryID($id);
		if ($products->isEmpty()) {
			throw new ProductException();
		}
		$products = $products->hidden(['summary']);
		return $products;
	}

	/**
	 * 获取单个商品
	 * @url /product/:id
	 */
	public function getOne($id){
		(new IDMustBePostiveInt())->goCheck();
		$product = ProductModel::getProductDetail($id);
		if (!$product) {
			throw new ProductException();
			
		}
		return $product;
	}
}