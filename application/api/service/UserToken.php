<?php
namespace app\api\service;

use app\lib\exception\WeChatException;
use app\api\model\User as UserModel;
use app\api\service\Token;
use app\lib\enum\ScopeEnum;
/**
* 
*/
class UserToken extends Token
{
	// 通过 wx.login() 接口获得临时登录凭证 code 
	protected $code;
	// AppID(小程序ID)
	protected $wxAppId;
	// AppSecret(小程序密钥)
	protected $wxAppSecret;
	// 登录接口，获取用户的唯一标识（openid）及本次登录的会话密钥（session_key）等
	protected $wxLoginUrl;

	function __construct($code){
		$this->code = $code;
		$this->wxAppId = config('wx.app_id');
		$this->wxAppSecret = config('wx.app_secret');
		// sprintf(format,arg1,arg2,arg++) 函数把格式化的字符串写入变量中
		$this->wxLoginUrl = sprintf(config('wx.login_url'),$this->wxAppId,$this->wxAppSecret,$this->code);
	}
	public function get(){
		$result = curl_get($this->wxLoginUrl);
		$wxResult = json_decode($result,true);
		if (empty($wxResult)) {
			throw new Exception('获取session_key及openid时异常，微信内部错误');		
		}else{
			$loginFial = array_key_exists('errcode', $wxResult);
			// 判断是否存在errcode 
			if ($loginFial) {
				$this->processLoginError($wxResult);
			}else{
				// 授权令牌
				return $this->grantToken($wxResult);
			}
		}
	}

	// 授权令牌方法
	private function grantToken($wxResult){
		// 1、拿到openid
		// 2、数据库看下，这个openid是否已经存在
		// 3、如果存在 则不处理，如果不存在那么新增一条user记录
		// 4、生成令牌，准备缓存数据，写入缓存
		// 5、把令牌放回到客户端去
		// key: 令牌
		// value: wxResult uid scope
		$openid = $wxResult['openid'];
		$user = UserModel::getByOpenID($openid);
		if ($user) {
			$uid = $user->id;
		}else{
			$uid = $this->newUser($openid);

		}
		// 准备缓存值 返回的是数组
		$cachedValue = $this->prepareCachedValue($wxResult,$uid);
		// 写入缓存 返回token令牌
		$token = $this->saveToCache($cachedValue);
		return $token;
	}

	// 写入缓存
	private function saveToCache($cachedValue){
		// 通过generateToken方法获取key
		$key = self::generateToken();
		// 将数组转化成字符串 编码
		$value = json_encode($cachedValue);
		// Token过期时间
		$expire_in = config('setting.token_expire_in');
		// 写入缓存
		$request = cache($key,$value,$expire_in);
		if (!$request) {
			throw new TokenException([
					'msg' => '服务器缓存无效',
					'errorCode' => 10005
				]);
		}
		// 返回key（token）
		return $key;
	}

	// 准备缓存值
	private function prepareCachedValue($wxResult,$uid){
		$cachedValue = $wxResult;
		$cachedValue['uid'] = $uid;
		// scope = 16 代表App用户的权限数值
		// scope = 32 代表CMS(管理员) 用户的权限数值
		$cachedValue['scope'] = 16;

		return $cachedValue;
	}

	// 创建新用户
	private function newUser($openid){
		$user = UserModel::create([
				'openid' => $openid
			]);
		return $user->id;
	}

	// 登录错误异常方法
	private function processLoginError($wxResult){
		throw new WeChatException([
				'msg' => $wxResult['errmsg'],
				'errorCode' => $wxResult['errcode']
			]);
		
	}
}