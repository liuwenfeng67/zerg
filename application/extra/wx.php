<?php
// 微信

return [
	// AppID(小程序ID)
	'app_id' => 'wx70c7aae0900936ae',
	// AppSecret(小程序密钥)
	'app_secret' => 'd6b6caa4c6a60a4eb493c13bd490403b',
	
	// 微信使用code换取用户openid及session_key的url地址
	// 登录凭证校验 通过 wx.login() 接口获得临时登录凭证 code 后传到开发者服务器调用此接口完成登录流程
	// %s 字符串占位符
	'login_url' => 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code',
	// 微信获取access_token的url地址
	'access_token_url' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
];