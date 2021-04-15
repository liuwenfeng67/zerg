<?php
namespace app\api\controller\v1;

use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\BannerMissException;
use app\api\model\Banner as BannerModel;
use think\Exception;
/**
* 
*/
class Banner
{
	/**
	 * 获取指定id的banner信息
	 * @url /banner/:id    接口的访问路径
	 * @http GET   请求方式
	 * @id  banner的id号
	 */
	public function getBanner($id){
		// AOP面向切面编程  validate验证层和excepton异常处理层是AOP的很好示例应用
		// 尤其是全局异常处理层，是面向切面编程的一个非常典型的应用
		// 通俗的解释 用抽象的方式 统一的，总体的 处理某一个问题
		
		// 先验证
		(new IDMustBePostiveInt())->goCheck();
		// 再经过Model模型查询信息
		$banner = BannerModel::getBannerById($id);
		
		if (!$banner) {
			throw new BannerMissException();	
		}

		// 返回数据信息
		return $banner;
	}


}