<?php
namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\model\Product;
use app\api\service\Order as OrderService;
use app\lib\enum\OrderStatusEnum;
use think\Exception;
use think\Log;
use think\Db;
use think\Loader;
Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');
/**
* 微信回调类
*/
class WxNotify extends \WxPayNotify
{
	
	/**
	 * 
	 * 回调方法入口，子类重写该方法
	 	//TODO 1、进行参数校验
		//TODO 2、进行签名验证
		//TODO 3、处理业务逻辑
	 * 注意：
	 * 1、微信回调超时时间为2s，建议用户使用异步处理流程，确认成功之后立刻回复微信服务器
	 * 2、微信服务器在调用失败或者接到回包为非确认包的时候，会发起重试，需确保你的回调是可以重入
	 * @param WxPayNotifyResults $objData 回调解释出的参数
	 * @param WxPayConfigInterface $config
	 * @param string $msg 如果回调处理失败，可以将错误信息输出到该方法
	 * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
	 */
	public function NotifyProcess($objData, $config, &$msg)
	{
		//TODO 用户基础该类之后需要重写该方法，成功的时候返回true，失败返回false
		
		if ($objData['result_code'] == 'SUCCESS') {
			$orderNo = $objData['out_trade_no'];   //订单号
			// 启动事务   事务与锁防止多次减库存
			Db::startTrans();
			try{
				$order = OrderModel::where('order_no','=',$orderNo)->lock(ture)->find();
				// 判断订单状态是否已支付
				if ($order->status==1) {
					// 根据订单id获取订单状态 即库存量检测
					$service = new OrderService();
					$orderStatus = $service->ckeckOrderStock($order->id);
					// 库存量检测
					if ($orderStatus['pass']) {
						// 更新支付成功状态
						$this->updateOrderStatus($order->id,ture);
						// 减库存
						$this->reduceStock($orderStatus);
					}else{
						// 更新支付成功 但是库存量不足状态
						$this->updateOrderStatus($order->id,false);
						// 不用减库存
					}
				}
				// 提交事务
    			Db::commit();
				return ture;
			}catch(Exception $ex){
				// 回滚事务
    			Db::rollback();
				Log::error($ex);
				return false;
			}

		}else{
			// 已经知道未支付成功，所以通知微信服务器不需要继续回调
			return ture;
		}
	}

	/**
	 * 减库存量
	 */
	private function reduceStock($orderStatus){
		// 要对每一个商品依次减去库存
		foreach ($orderStatus['pStatusArray'] as $singlePStatus) {
			Product::where('id','=',$singlePStatus['id'])->setDec('stock',$singlePStatus['count']);
		}
	}
	/**
	 * 更新支付状态
	 */
	private function updateOrderStatus($orderID,$success){
		// PAID=2：已支付   PAID_BUT_OUT_OF=4：已支付但是库存不足
		$status = $success ? OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_OUT_OF;
		// 更新状态
		OrderModel::where('id','=',$orderID)->update(['status'=>$status]);
	}


}