<?php
namespace app\api\controller;

use think\Controller;
use app\api\service\Token as TokenService;
/**
* 基础控制器
*/
class BaseController extends Controller
{
	
	// Address前置方法 利用scope验证用户权限
	protected function checkPrimaryScope(){
		// 需要用户和CMS管理员都可以访问的接口权限
		TokenService::needPrimaryScope();
	}

	// Order 的前置操作方法 检测独有的scope 只有用户自己有接口权限
	protected function checkExclusiveScope(){
		// 只有用户才能访问的接口权限
		TokenService::needExclusiveScope();
	}
}