<?php
namespace app\api\controller\v1;

use app\api\service\UserToken;
use app\api\service\AppToken;
use app\api\service\Token as TokenService;
use app\api\validate\TokenGet;
use app\api\validate\AppTokenGet;
use app\lib\exception\ParameterException;
/**
* Token控制器
*/
class Token
{
	/**
	 * 获取Token
	 * @url /token/user
	 */
	public function getToken($code = ''){
		(new TokenGet())->goCheck();
		$ut = new UserToken($code);
		$token = $ut->get();

		// 所有的返回结果都要求是json格式的
		// 改成关联数组的形式，框架会把它默认的序列化成json的格式
		return [
			'token'=>$token
		];
	}
	/**
	 * 	检测token令牌
	 */
	public function verifyToken($token = ''){
		if (!$token) {
			throw new ParameterException([
					'token不允许为空'
				]);
		}
		$valid = TokenService::verifyToken($token);
		return [
			'isValid' => $valid
		];
	}

	/**
	 * 第三方应用获取令牌  cms
	 * @url /app
	 * @POST ac=:ac   se=:secret
	 */
	public function getAppToken($ac='',$se=''){
		(new AppTokenGet())->goCheck();
		$app = new AppToken();
		$token = $app->get($ac,$se);
		return [
			'token' => $token
		];
	}


}