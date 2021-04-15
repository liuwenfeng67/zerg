<?php
namespace app\api\service;

use think\Db;
use app\api\model\Product;
use app\api\model\UserAddress;
use app\api\model\OrderProduct;
use app\api\model\Order as OrderModel;
use app\lib\exception\OrderException;
use app\lib\enum\OrderStatusEnum;
use app\api\service\DeliveryMessage;
/**
* 
*/
class Order
{
	// 订单商品列表，也就是客户端传递过来的products参数
	protected $oProducts;
	// 真实商品信息数组  通过订单从数据库查询出来的
	protected $products;
	// 用户id
	protected $uid;

	/**
	 * 下单方法
	 * @param uid 用户id
	 * @param oProducts 订单商品列表
	 * @return 返回订单状态
	 */
	public function place($uid,$oProducts){
		// oProducts 和 products 对比
		// products 是从数据库查询出来
		$this->oProducts = $oProducts;
		// 根据订单信息查询真实的商品信息
		$this->products = $this->getProductsByOrder($oProducts);
		$this->uid = $uid;
		//获取订单状态
		$status = $this->getOrderStatus();
		if (!$status['pass']) {
			$status['order_id'] = -1;
			return $status;
		}
		// 开始创建订单
		// 1、生成订单快照
		$orderSnap = $this->snapOrder($status);
		// 2、创建订单
		$order = $this->createOrder($orderSnap);
		$order['pass'] = true;//添加一个订单检测通过
		return $order;
	}

	// 创建订单 保存到数据库 返回订单号，订单id和创建时间
	private function createOrder($snap){
		// 启动事务
		Db::startTrans();
		try{
			// 生成订单号
			$orderNo = $this->makeOrderNo();
			// 实例化order 订单模型对象
			$order = new \app\api\model\Order();
			$order->order_no = $orderNo;
			$order->user_id = $this->uid;
			$order->total_price = $snap['orderPrice'];
			$order->total_count = $snap['totalCount'];
			$order->snap_img = $snap['snapImg'];
			$order->snap_name = $snap['snapName'];
			$order->snap_address = $snap['snapAddress'];
			$order->snap_items = json_encode($snap['pStatus']);
			// 订单信息保存到order订单表中
			$order->save();

			// 取出order订单的id 需要添加到传过来的oProducts 订单商品中 
			$orderID = $order->id;
			$create_time = $order->create_time;
			foreach ($this->oProducts as &$p) {
				 $p['order_id'] = $orderID;
			}
			// 需要将这些信息(新的oProducts)存到order_products数据表中
			$orderProduct = new OrderProduct();
			$orderProduct->saveAll($this->oProducts);
			// 提交事务
    		Db::commit();
			return [
				'order_no' => $orderNo,
				'order_id' => $orderID,
				'create_time' => $create_time
			];
		}catch(Exception $ex){
			// 回滚事务
			Db::rollback();
			throw $ex;
		}
	}

	//生成订单号
	public static function makeOrderNo(){
		$yCode = array('A','B','C','D','E','F','G','H','I','J');
		// dechex() 函数把十进制转换为十六进制。
		//substr(string,start,length) 函数返回字符串的一部分。start如果是负数 - 在从字符串结尾开始的指定位置开始
		$orderSn = $yCode[intval(date('Y')-2019)]
				.strtoupper(dechex(date('m')))
				.date('d')
				.substr(time(), -5)
				.substr(microtime(), 2, 5)
				.sprintf('%02d',rand(0,99));
		return $orderSn;
	} 

	// 生成订单快照信息
	private function snapOrder($status){
		// 初始化订单快照
		$snap = [
			'orderPrice' => 0,
			'totalCount' => 0,
			'pStatus' => [],
			'snapAddress' => null,
			'snapName' => '',
			'snapImg' => ''
		];

		$snap['orderPrice'] = $status['orderPrice'];
		$snap['totalCount'] = $status['totalCount'];
		$snap['pStatus'] = $status['pStatusArray'];
		$snap['snapAddress'] = json_encode($this->getUserAddress());
		$snap['snapName'] = $this->products[0]['name'];
		$snap['snapImg'] = $this->products[0]['main_img_url'];
		// 如果商品数量大于1种
		if (count($this->products)>0) {
			$snap['snapName'] .= '等';
		}
		return $snap;
	}
	//获取用户地址
	private function getUserAddress(){
		$userAddress =  UserAddress::where('user_id','=',$this->uid)->find();
		if (!$userAddress) {
			throw new UserException([
					'msg' => '用户地址不存在，下单失败',
					'errorCode' => 60001
				]);
			
		}
		return $userAddress->toArray();
	}

	// 根据订单id 定义一个对外的库存量检测方法
	public function ckeckOrderStock($orderID){
		$oProducts = OrderProduct::where('order_id','=',$orderID)->select();
		$this->oProducts = $oProducts;
		$this->products = $this->getProductsByOrder($oProducts);
		$status = $this->getOrderStatus();

		return $status;
	}

	//获取订单状态
	private function getOrderStatus(){
		// 初始化订单状态
		$status = [
			'pass' => true,        //订单库存量是否检查通过
			'orderPrice' => 0,     //所有商品价格的总和
			'totalCount' => 0,     //所有商品总数
			'pStatusArray' => [],  //保存所有商品的状态详细信息
		];
		// 订单信息oProducts 和 商品信息products 对比
		// 遍历订单
		foreach ($this->oProducts as $oProduct) {
			// 获取订单具体某个商品的状态  和数据库数据对比
			$pStatus = $this->getProductStatus($oProduct['product_id'],$oProduct['count'],$this->products);
			if (!$pStatus['haveStock']) {
				$status['pass'] = false;
			}
			$status['orderPrice'] += $pStatus['totalPrice'];
			$status['totalCount'] += $pStatus['counts'];
			array_push($status['pStatusArray'], $pStatus);
		}
		return $status;
	}
	/**
	 * 获取订单具体某个商品的状态 和数据库数据对比
	 * @param oPID 订单当前商品id
	 * @param oCount 订单当前商品数量
	 * @param products 通过订单商品信息查询出来的真实商品
	 * @return pStatus  订单当前商品的状态信息
	 */
	private function getProductStatus($oPID,$oCount,$products){	
		$pIndex = -1;
		// 初始化商品状态
		$pStatus = [
			'id' => null,
			'haveStock' => false,  //商品是否有库存
			'count' => 0,          //商品数量
			'price' => 0,          //商品的单价
			'name' => '',          //商品名称
			'totalPrice' => 0,     //单个商品价格总和
			'main_img_url' => null
		];
		// 遍历从数据库查询出来的商品 和 订单进行对比
		for ($i=0; $i < count($products); $i++) { 
			// 订单商品id 和数据库查询出来的商品id进行对比
			if ($oPID == $products[$i]['id']) {
				$pIndex = $i;
			}
		}		
		if ($pIndex == -1) {
			// 客户端传递的product_id 有可能根本不存在
			throw new OrderException([
					'msg' => 'id为'.$oPID.'的商品不存在，创建订单失败'
				]);
		}else{
			$product = $products[$pIndex];
			$pStatus['id'] = $product['id'];
			$pStatus['name'] = $product['name'];
			$pStatus['count'] = $oCount;
			$pStatus['price'] = $product['price'];
			$pStatus['main_img_url'] = $product['main_img_url'];
			$pStatus['totalPrice'] = $product['price'] * $oCount;
			// 库存量
			if ($product['stock'] - $oCount >= 0 ) {
				$pStatus['haveStock'] = true;
			}		
		}
		return $pStatus;
	}

	// 根据订单信息查询真实的商品信息
	private function getProductsByOrder($oProducts){
		// 获取订单信息里的所有商品id
		$oPIDs = [];
		foreach ($oProducts as $item) {
			array_push($oPIDs, $item['product_id']);
		}
		// 根据所有商品id查询数据库获得所有商品
		$products = Product::all($oPIDs)
		//设置可见参数
		->visible(['id','name','price','stock','main_img_url'])
		//因为设置了从数据库查询的结果为结果集，要跟客户端接收的商品数组对比，所以设置为数组结果
		->toArray();
		return $products;
	}

	/**
	 * 发送发货模板消息
	 * @param orderId  订单id
	 * @param jumpPage	跳转页面的url
	 */
	public function delivery($orderID,$jumpPage=''){
		// 获取订单模型
		$order = OrderModel::where('id','=',$orderID)->find();
		if (!$order) {
			throw new OrderException();
		}
		// 订单状态  PAID 已支付 = 2
		if ($order->status != OrderStatusEnum::PAID) {
			throw new OrderException([
					'msg' => '还没付款呢，想干嘛？或者你已经更新过订单了，不要再刷了',
					'errorCode' => 8002,
					'code' => 403
				]);
		}
		// 更新订单状态 为已发货
		$order->status = OrderStatusEnum::DELIVERED;
		$order->save();
		//发送模板消息的主方法
		$message = new DeliveryMessage();
		return $message->sendDeliveryMessage($order,$jumpPage);
	}
}