<?php
namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\validate\IDMustBePostiveInt;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify;
use think\Loader;
Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');
/**
* 支付控制器
*/
class Pay extends BaseController
{
	
	protected $beforeActionList = [
		'checkExclusiveScope' => ['only' => 'getpreorder']
	];
	/**
	 * 请求预订单信息 API要到微信服务器生成一个 微信服务器要求的订单
	 * @param id 数据库中order表中的订单id
	 */
	public function getPreOrder($id){
		(new IDMustBePostiveInt())->goCheck();
		$pay = new PayService($id);
		return $pay->pay();

	}
	/**
	 * 接收微信的通知
	 * 微信会返回给我们一个支付的结果 (异步)
	 */
	public function receiveNotify(){
		// 通知频率15/15/30/180/1800/1800/1800/1800/3600 单位秒 微信没有收到正确的响应消息会一直调用
		// 1、检查库存量，超卖
		// 2、更新订单的状态 待支付->已支付
		// 3、减库存
		// 如果成功处理，我们返回微信成功处理的信息。否则，我们需要返回没有成功处理
		// 特点：post提交   微信xml格式返回数据  定义回调路由不会携带参数
		$config = new \WxPayConfig();
		$notify = new WxNotify();
		$notify->Handle($config);
	}
}