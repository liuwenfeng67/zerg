<?php
namespace app\api\service;

use think\Exception;
use app\api\service\Order as OrderService;
use app\api\service\Token;
use app\api\model\Order as OrderModel;
use app\lib\exception\OrderException;
use app\lib\enum\OrderStatusEnum;
use think\Loader;
use think\Log;
// 第三方类库导入
// 微信支付SDK类库 没有使用命名空间
//  extend/WxPay/WxPay.Api.php
Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');


/**
* 
*/
class Pay
{
	// 订单id
	private $orderID;
	// 订单号
	private $orderNO;

	function __construct($orderID)
	{
		if (!$orderID) {
			throw new Exception("订单ID不得为NULL");			
		}
		$this->orderID = $orderID;
	}
	/**
	 * 支付的主方法，
	 */
	public function pay(){
		// 订单号可能根本不存在
		// 订单号确实存在的，但是，订单号和当前用户是不匹配的
		// 订单有可能已经被支付
		$this->checkOrderValid();
		// 进行库存量的检测
		$orderService = new OrderService();
		$status = $orderService->ckeckOrderStock($this->orderID);
		if (!$status['pass']) {
			//返回回去就相当于 支付的相关业务逻辑被中断不会执行接下来的支付代码
			return $status;
		}
		// 生成微信预订单
		return $this->makeWxPreOrder($status['orderPrice']);

	}

	/**
	 * 微信预订单生成
	 */
	private function makeWxPreOrder($totalPrice){
		// 获取openid
		$openid = Token::getCurrentTokenVar('openid');
		if (!$openid) {
			throw new TokenException();
		}
		// 统一下单输入对象  统一下单对象    如果没有命名空间要在类名前加 \
		$wxOrderData = new \WxPayUnifiedOrder();
		// 相关参数封装到对象
		$wxOrderData->SetOut_trade_no($this->orderNO); //设置交易订单号
		$wxOrderData->SetTrade_type('JSAPI');          //交易类型  小程序用JSAPI
		$wxOrderData->SetTotal_fee($totalPrice*100);   //订单总金额  微信是以分为单位，所以*100
		$wxOrderData->SetBody('零食商贩');             //商品简要描述 
		$wxOrderData->SetOpenid($openid);              //商品简要描述
		$wxOrderData->SetNotify_url(config('secure.pay_back_url'));   //url地址用于接收微信回调通知 
		// 把对象发送的微信的预订单接口里面去  返回给我们所要的一个签名的参数结果
		return $this->getPaySignature($wxOrderData);
	}
	/**
	 * 向微信请求订单号并生成签名
	 * 获取支付签名
	 * 把对象发送的微信的预订单接口里面去  返回给我们所要的一个签名的参数结果
	 */
	private function getPaySignature($wxOrderData){
		$config = new \WxPayConfig();
		$wxOrder = \WxPayApi::unifiedOrder($config,$wxOrderData);
		if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
			Log::record($wxOrder,'error');
			Log::record('获取预支付订单失败','error');
		}
		// 把prepay_id 记录到数据库 order表中
		$this->recordPreOrder($wxOrder);
		// 返回签名方法
		$signature = $this->sign($wxOrder);
		return $signature;
	}
	// 签名方法
	private function sign($wxOrder){
		// 提交JSAPI输入对象  生成返回客户端的一系列的参数
		$jsApiPayData = new \WxPayJsApiPay();
		$jsApiPayData->SetAppid(config('wx.app_id'));  //appid
		$jsApiPayData->SetTimeStamp((string)time());   //字符串格式的时间戳
		$rand = md5(time().mt_rand(0,1000));           //mt_rand(min,max) 返回随机整数
		$jsApiPayData->SetNonceStr($rand);             //随机字符串，长度为32个字符以下
		//统一下单接口返回的 prepay_id 参数值，提交格式如：prepay_id=***
		$jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);   
		$jsApiPayData->SetSignType('md5');             //签名算法 md5
		//生成签名
		$sign = $jsApiPayData->MakeSign();
		// 获取设置的值  数组形式
		$rawValues = $jsApiPayData->GetValues();
		$rawValues['paySign'] = $sign;
		// 去除没用使用的appid
		unset($rawValues['appId']);

		return $rawValues;
	}

	// 把prepay_id 记录到数据库 order表中
	private function recordPreOrder($wxOrder){
		OrderModel::where('id','=',$this->orderID)->update(['prepay_id'=>$wxOrder['prepay_id']]);
	}

	/**
	 * 检测订单号
	 */
	private function checkOrderValid(){
		$order = OrderModel::where('id','=',$this->orderID)->find();
		// 检测订单号是否存在
		if (!$order) {
			throw new OrderException();			
		}
		// 检测订单号与当前用户是否匹配
		if (!Token::isValidOperate($order->user_id)) {
			throw new TokenException([
					'msg' => '订单与用户不匹配',
					'errorCode' => 10006
				]);
		}
		// 检测订单有没有被支付 1待支付 2已支付 3已发货 4已支付但是库存不足
		// 使用类似枚举的方式
		if ($order->status != OrderStatusEnum::UNPAID) {
			throw new OrderException([
					'msg' => '订单已支付',
					'errorCode' => 8003,
					'code' => 400
				]);
		}
		// 赋值订单编号
		$this->orderNO = $order->order_no;

		return true;
	}


}