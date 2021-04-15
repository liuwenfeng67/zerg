<?php
namespace app\api\controller\v1;

use app\api\model\Category as CategoryModel;
/**
* 分类控制器
*/
class Category
{
	
	public function getAllCategories(){
		$categories = CategoryModel::all([],'img');
		if ($categories->isEmpty()) {
			throw new CategoryException();
		}
		return $categories;
	}
}