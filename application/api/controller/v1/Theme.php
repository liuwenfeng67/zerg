<?php
namespace app\api\controller\v1;

use app\api\validate\IDCollection;
use app\api\validate\IDMustBePostiveInt;
use app\api\model\Theme as ThemeModel;
use app\lib\exception\ThemeException;

/**
* 专题控制器
*/
class Theme
{
	/**
	 * @url /theme?ids=id1,id2,id3...
	 * @return 一组theme模型
	 */
	public function getSimpleList($ids=''){
		(new IDCollection())->goCheck();
		$ids = explode(',', $ids);
		$result = ThemeModel::with('topicImg,headImg')->select($ids);
		if ($result->isEmpty()) {
			throw new ThemeException();	
		}
		return $result;
	}

	/**
	 * 获取一组专题
	 * @url 
	 */
	public function getComplexOne($id){
		(new IDMustBePostiveInt())->goCheck();
		$theme = ThemeModel::getThemesWithProducts($id);
		if (!$theme) {
			throw new ThemeException();
		}
		return $theme;
	}

}