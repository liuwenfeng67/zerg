<?php
namespace app\api\service;

use think\Exception;
/**
* 请求和管理access_token
*/
class AccessToken
{	
	// 获取小程序全局唯一后台接口调用凭据（access_token）请求地址
	private $tokenUrl;
	// 缓存里保存access_token 的键名
	const TOKEN_CACHED_KEY = 'access';
	// access_token 过期时间
	const TOKEN_EXPIRE_IN = 7000;
	
	function __construct(){
		$url = config('wx.access_token_url');
		// 拼凑完整的 access_token请求地址
		$url = sprintf($url,config('wx.app_id'),config('wx.app_secret'));
		$this->tokenUrl = $url;
	}
	/**
	 * 获取access_token
	 * 建议用户规模小时每次直接去微信服务器获取最新的token
	 * 但微信access_token接口获取是有限制的 2000次/天
	 */
	public function get(){
		// 从缓存中获取access_token
		$token = $this->getFromCache();
		if (!$token) {
			// 如果不存在 则从微信服务器获取access_token
			return $this->getFromWxServer();
		}else{
			// 如果存在 则返回access_token
			return $token;
		}
	}

	/**
	 * 从缓存中获取access_token
	 */
	private function getFromCache(){
		// 从缓存中获取access_token
		$token = cache(self::TOKEN_CACHED_KEY);
		if (!$token) {
			return null;
		}
		return $token;
	}

	/**
	 * 微信服务器获取access_token
	 */
	private function getFromWxServer(){
		//curl操作 get请求地址  微信服务器获取access_token
		$token = curl_get($this->tokenUrl);
		// json_decode 生成PHP关联数组  true
		$token = json_decode($token,true);
		if (!$token) {
			throw new Exception('获取access_token异常');
		}
		if (!empty($token['errcode'])) {
			throw new Exception($token['errmsg']);	
		}
		// 保存到缓存中
		$this->saveToCache($token);
		return $token['access_token'];
	}

	/**
	 * 保存到缓存中
	 */
	protected function saveToCache($token){
		cache(self::TOKEN_CACHED_KEY,$token,self::TOKEN_EXPIRE_IN);
	}
}