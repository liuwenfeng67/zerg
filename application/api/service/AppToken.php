<?php
namespace app\api\service;

use app\api\service\Token;
use app\api\model\ThirdApp;
/**
* 
*/
class AppToken extends Token
{
	/**
	 * 获取token
	 */
	public function get($ac,$se){
		// 与数据库比对用户的账号密码
		$app = ThirdApp::check($ac,$se);
		if (!$app) {
			throw new TokenException([
					'msg' => '授权失败',
					'errorCode' => 10004
				]);
		}else{
			$scope = $app->scope;  //获取权限作用域
			$uid = $app->id;       //管理员用户的id  scope权限区别用户和管理员
			$values = [
				'scope' => $scope,
				'uid' => $uid
			];
			// 写入缓存
			$token = $this->saveToCache($values);
			return $token;
		}
	}

	/**
	 * 写入到缓存中
	 * return token
	 */
	private function saveToCache($values){
		// 生成Token
		$token = self::generateToken();
		// 过期时间
		$expire_in = config('setting.token_expire_in');
		//  写入到缓存中  设置缓存数据 tp5助手函数cache('name', $value, 3600);
		$result = cache($token,json_encode($values),$expire_in);
		if (!$result) {
			throw new TokenException([
				'msg' => '服务器缓存异常',
				'errorCode' => 10005
				]);
		}
		return $token;
	}
}