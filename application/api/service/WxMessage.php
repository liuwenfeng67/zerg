<?php
namespace app\api\service;

use think\Exception;
/**
* 发送模板消息基类
*/
class WxMessage
{
	// 发送模板消息 请求地址
	private $sendUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=%s';
	// 接收者（用户）的 openid
	private $touser;
	// 不让子类控制颜色
	private $color = 'black';

	protected $tplID;           //模板消息的id
	protected $page;            //点击模板卡片后的跳转页面
	protected $formID;          //支付场景下，为本次支付的 prepay_id
	protected $data;            //模板内容
	protected $emphasisKeyWord; //模板需要放大的关键词，不填则默认无放大

	function __construct(){
		$accessToken = new AccessToken();
		// 获取access_token
		$token = $accessToken->get();
		// 拼凑发送模板消息请求地址
		$this->sendUrl = sprintf($this->sendUrl,$token['access_token']);
	}

	/**
	 * 发送消息方法 根据订单的用户id获取oppenid
	 * 开发工具中拉起的微信支付prepay_id 是无效的 需要在真机上垃圾支付
	 */
	protected function sendMessage($openID){
		$data = [
			'touser' => $openID,
			'template_id' => $this->tplID,
			'page' => $this->page,
			'form_id' => $this->formID,
			'data' => $this->data,
			'emphasis_keyword' => $this->emphasisKeyWord
		];
		$result = curl_post($this->sendUrl,$data);
		$result = json_decode($result,true);   //转成数组
		if ($result['errcode'] == 0) {
			return true;
		}else{
			throw new Exception('模板消息发送失败，'.$result['errmsg']);
			
		}
	}

}