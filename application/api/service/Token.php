<?php
namespace app\api\service;

use think\Request;
use think\Cache;
use think\Exception;
use app\lib\exception\TokenException;
use app\lib\exception\ForbiddenException;
use app\lib\enum\ScopeEnum;
/**
* Token基类
*/
class Token
{
	// 生成Token
	public static function generateToken(){
		// 使用三组字符串 md5()加密 生成Token
		// 1、32位无序字符
		$randChars = getRandChar(32);
		// 2、时间戳
		$timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
		// 3、salt 盐
		$salt = config('secure.token_salt');
		return md5($randChars.$timestamp.$salt);
	}

	// 根据参数 获取当前Token的变量信息
	public static function getCurrentTokenVar($key){
		// 规范和约定 所有的用户令牌都必须放在用户的http请求的header头里面来传递，不能放在body里面
		// 获取用户的token 令牌  （Request请求不仅仅只能在控制器里能使用）
		$token = Request::instance()->header('token');
		// 获取缓存
		$vars = Cache::get($token);
		if (!$vars) {
			throw new TokenException();//token过期或者无效
		}else{
			if (!is_array($vars)) {
				// 转换成数组
				$vars = json_decode($vars,true);
			}
			if (array_key_exists($key, $vars)) {
				return $vars[$key];
			}else{
				throw new Exception('尝试获取的Token变量并不存在');			
			}			
		}
	}

	// 获取当前用户的uid
	public static function getCurrentUid(){
		$uid = self::getCurrentTokenVar('uid');
		return $uid;
	}

	// 需要用户和CMS管理员都可以访问的接口权限
	public static function needPrimaryScope(){
		$scope = self::getCurrentTokenVar('scope');
		if ($scope) {
			if ($scope >= ScopeEnum::User) {
				return true;
			}else{
				throw new ForbiddenException();				
			}
		}else{
			throw new TokenException();
		}
	}
	// 只有用户才能访问的接口权限
	public static function needExclusiveScope(){
		$scope = self::getCurrentTokenVar('scope');
		if ($scope) {
			if ($scope == ScopeEnum::User) {
				return true;
			}else{
				throw new ForbiddenException();				
			}
		}else{
			throw new TokenException();
		}
	}

	/**
	 * 用户的操作是否合法   是否是一个合法的操作
	 * 和我们令牌Token 里面的UID  是否是同一个
	 * @param checkedUID  被检测的uid
	 */
	public static function isValidOperate($checkedUID){
		if (!$checkedUID) {
			throw new Exception('检测UID时，必须传入一个被检测的UID');
		}
		// 当前请求用户的UID
		$currentOperateUID = self::getCurrentUid();
		if ($currentOperateUID == $checkedUID) {
			return true;
		}
		return false;
	}

	/**
	 * 客户端令牌检测
	 * @param token 令牌
	 */
	public static function verifyToken($token){
		// 根据token查询缓存数据是否存在
		$exist = Cache::get($token);
		if ($exist) {
			return true;
		}else{
			return false;
		}
	}


}