<?php
namespace app\api\controller\v1;

use app\api\validate\AddressNew;
use app\api\model\User as UserModel;
use app\api\model\UserAddress;
use app\api\service\Token as TokenService;
use app\lib\exception\UserException;
use app\lib\exception\ForbiddenException;
use app\lib\exception\SuccessMessage;
use app\lib\enum\ScopeEnum;
use app\api\controller\BaseController;

/**
* 地址管理控制器
*/
class Address extends BaseController
{
	// 执行前置方法
	protected $beforeActionList = [
		'checkPrimaryScope' => ['only' => 'createorupdateaddress,getUserAddress']
	];
	
	/**
	 * 根据当前用户id获取用户地址
	 */
	public function getUserAddress(){
		$uid = TokenService::getCurrentUid();
		$userAddress = UserAddress::where('user_id',$uid)->find();
		if (!$userAddress) {
			throw new UserException([
				'msg' => '用户地址不存在',
				'errorCode' => 60001
			]);		
		}
		return $userAddress;
	}

	/**
	 * 创建或者更新地址
	 */
	public function createOrUpdateAddress(){
		$validate = new AddressNew();
		$validate->goCheck();
		// 根据Token来获取uid
		// 根据uid来查找用户数据，判断用户是否存在，如果不存在则抛出异常
		// 获取用户从客户端传递过来的地址信息
		// 根据用户地址信息是否存在，从而判断是添加地址还是更新地址
		$uid = TokenService::getCurrentUid();
		$user = UserModel::get($uid);
		if (!$user) {
			throw new UserException();			
		}
		$dataArray = $validate->getDataByRule(input('post.'));

		$userAddress = $user->address;
		if (!$userAddress) {
			// 关联新增 // 如果还没有关联数据 则进行新增
			$user->address()->save($dataArray);
		}else{
			// 关联更新 更新和新增一样使用save方法进行更新关联数据。
			$user->address->save($dataArray);
		}
		return new SuccessMessage();
	}
}