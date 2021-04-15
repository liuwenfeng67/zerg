<?php
namespace app\api\service;

use app\api\model\User;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
/**
* 发送模板消息  参数拼凑和封装
*/
class DeliveryMessage extends WxMessage
{
	// 模板消息的id
	const DELIVERY_MSG_ID = 'ZQJoyAe9NvRbPG3IdsYVvbwv5XtM34vyklqGMP5Wr1c';

	/**
	 * 发送消息的主方法
	 * @param order 订单实体模型 
	 * @param tplJumpPage  点击模板消息跳转到小程序指定的页面
	 */
	public function sendDeliveryMessage($order,$tplJumpPage = ''){
		if (!$order) {
			throw new OrderException();		
		}
		// 模板消息的id
		$this->tplID = self::DELIVERY_MSG_ID;
		// 表单提交场景下，为 submit 事件带上的 formId；支付场景下，为本次支付的 prepay_id
		$this->formID = $order->prepay_id;
		// 点击模板卡片后的跳转页面
		$this->page = $tplJumpPage;
		// 调用方法 准备模板内容，不填则下发空模板。
		$this->prepaayMessageData($order);
		// 模板需要放大的关键词，不填则默认无放大
		$this->emphasisKeyWord = 'keyword2.DATA';
		// 调用父类的发送消息方法   根据订单的用户id获取oppenid
		return parent::sendMessage($this->getUserOpenID($order->user_id));
	}

	/**
	 * 调用方法 准备模板内容 
	 * @param order 订单实体模型 
	 */
	private function prepaayMessageData($order){
		$dt = new \DateTime();
		$data = [
			'keyword1' => [
				'value' => '顺丰速运'
			],
			'keyword2' => [
				'value' => $order->snap_name,
				'color' => '#274088'
			],
			'keyword3' => [
				'value' => $order->order_no
			],
			'keyword4' => [
				'value' => $dt->format("Y-m-d H:i:s")
			]
		];
		// 保存到成员变量
		$this->data = $data;
	}

	/**
	 * 根据订单的用户id获取oppenid
	 */
	private function getUserOpenID($uid){
		$user = User::get($uid);
		if (!$user) {
			throw new UserException();
		}
		return $user->openid;
	}
}