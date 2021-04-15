<?php
namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Order as OrderModel;
use app\api\validate\OrderPlace;
use app\api\validate\PagingParameter;
use app\api\validate\IDMustBePostiveInt;
use app\api\service\Token as TokenService;
use app\api\service\Order as OrderServer;
use app\lib\exception\TokenException;
use app\lib\exception\ForbiddenException;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;
use app\lib\enum\ScopeEnum;
/**
* 订单
*/
class Order extends BaseController
{
	// 下单支付流程
	// 用户在选择商品后，向API提交包含所选商品的相关信息
	// API在接收到信息之后，需要检查订单相关商品的库存量
	// 有库存，把订单数据存入数据库 == 下单成功，返回客户端消息，告诉客户端可以支付了
	// 调用我们的支付接口，进行支付
	// 还需要再次进行库存量的检测
	// 服务器这边就可以调用微信支付接口进行支付
	// 小程序根据服务器返回的结果拉起微信支付
	// 微信会返回给我们一个支付的结果（异步）
	// 成功：也需要进行库存量的检查
	// 成功：进行库存量的扣除

	// beforeActionList属性 指定某个方法为其他方法的前置操作 
	// checkExclusiveScope 用户独有权限
	// checkPrimaryScope 用户和CMS管理员拥有的权限
	protected $beforeActionList = [
		'checkExclusiveScope' => ['only' => 'placeorder'],
		'checkPrimaryScope' => ['only' => 'getDetail,getSummaryByUser']
	];
	
	// 请求订单
	public function placeOrder(){
		(new OrderPlace())->goCheck();
		// 获取所有商品  如果你要获取的数据为数组，请一定注意要加上 /a 修饰符才能正确获取到
		$products = input('post.products/a');
		// 获取用户id
		$uid = TokenService::getCurrentUid();
		// 请求订单
		$order = new OrderServer();
		$status = $order->place($uid,$products);
		return $status;
	}

	/**
	 * 获取订单列表 含分页
	 * @param int $page 当前页
	 * @param int $size 每页显示的数量
	 * @return array
	 */
	public function getSummaryByUser($page=1,$size=15){
		// 验证分页参数
		(new PagingParameter())->goCheck();
		// 获取用户id
		$uid = TokenService::getCurrentUid();
		$pagingOrders = OrderModel::getSummaryByUser($uid,$page,$size);
		if ($pagingOrders->isEmpty()) {
			return [
				'data' => [],
				'current_page' => $pagingOrders->getCurrentPage()
			];
		}
		$data = $pagingOrders->hidden(['snap_items','snap_address','prepay_id'])->toArray();
		return [
			'data' => $data,
			'current_page' => $pagingOrders->getCurrentPage()
		];
	}

	/**
	 * 获取全部订单简要信息（分页） 包含所有用户
	 * @param int $page 当前页
	 * @param int $size 每页显示的数量
	 * @return array
	 * @throws \app\lib\exception\ParameterException
	 */
	public function getSummary($page=1,$size=15){
        
		// 验证分页参数
		(new PagingParameter())->goCheck();
		// 分页获取订单
		$pagingOrders = OrderModel::getSummaryByPage($page,$size);
		if ($pagingOrders->isEmpty()) {
			return [
				'current_page' => $pagingOrders->currentPage(),
				'data' => []
			];
		}
		$data = $pagingOrders->hidden(['snap_items','snap_address'])->toArray();
		return [
			'current_page' => $pagingOrders->currentPage(),
			'data' => $data
		];

	}
	
	/**
	 * 获取订单详情
	 * @param id  订单id
	 */
	public function getDetail($id){
		(new IDMustBePostiveInt())->goCheck();
		$orderDetail = OrderModel::get($id);
		if (!$orderDetail) {
			throw new OrderException();			
		}
		return $orderDetail->hidden(['prepay_id']);
	}

	/**
	 * 发送发货模板消息
	 * @param id  订单id
	 */
	public function delivery($id){
		(new IDMustBePostiveInt())->goCheck();
		$order = new OrderServer();
		$success = $order->delivery($id);
		if ($success) {
			return new SuccessMessage();
		}
	}
}