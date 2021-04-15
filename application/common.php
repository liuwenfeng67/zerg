<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件 

/**
 * @param string $url post请求地址
 * @param array $params
 * @return mixed
 */
function curl_post($url, array $params = array())
{
    $data_string = json_encode($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt(
        $ch, CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json'
        )
    );
    $data = curl_exec($ch);
    curl_close($ch);
    return ($data);
}

/**
 * @param string $url get请求地址
 * @param int $httpCode 返回状态码
 */
function curl_get($url, &$httpCode = 0){
	$curl = curl_init();
	// 使用curl_setopt()设置要获取的URL地址
	curl_setopt($curl, CURLOPT_URL, $url);
	// 设置是否输出header
	curl_setopt($curl, CURLOPT_HEADER, false);
	// 设置是否输出结果
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	// 设置是否检查服务器端的证书
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	// 使用curl_exec()将CURL返回的结果转换成正常数据并保存到一个变量
	$file_contents = curl_exec($curl);
	$httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
	// 使用curl_close() 关闭CURL 会话
	curl_close($curl);
	return $file_contents;
}

// 字符串获取指定长度的随机字符串
function getRandChar($length){
	$str = '';
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789abcdefghijklmnopqrstuvwxyz";
	$max = strlen($chars)-1;
	for ($i=0; $i < $length; $i++) { 
		$str .= substr($chars, rand(0,$max),1);
	}
	return $str;
}