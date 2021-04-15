<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// return [
//     '__pattern__' => [
//         'name' => '\w+',
//     ],
//     '[hello]'     => [
//         ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
//         ':name' => ['index/hello', ['method' => 'post']],
//     ],

// ];

// 动态配置路由
use think\Route;

// banner 路由
Route::get('api/:version/banner/:id','api/:version.Banner/getBanner');

// theme 专题 主题
Route::get('api/:version/theme','api/:version.Theme/getSimpleList');
// 需要配置路由完整匹配
Route::get('api/:version/theme/:id','api/:version.Theme/getComplexOne');

// 产品
Route::get('api/:version/product/by_category','api/:version.Product/getAllInCategory');
//路由变量规则 ['id'=>'\d+'] 指定id必须为正整数
Route::get('api/:version/product/:id','api/:version.Product/getOne',[],['id'=>'\d+']);
Route::get('api/:version/product/recent','api/:version.Product/getRecent');
// 路由分层
// Route::group('api/:version/product',function(){
// 	Route::get('/by_category','api/:version.Product/getAllInCategory');
// 	Route::get('/:id','api/:version.Product/getOne',[],['id'=>'\d+']);
// 	Route::get('/recent','api/:version.Product/getRecent');
// });

// 分类
Route::get('api/:version/category/all','api/:version.Category/getAllCategories');

// Token
Route::post('api/:version/token/user','api/:version.Token/getToken');
Route::post('api/:version/token/verify','api/:version.Token/verifyToken');
Route::post('api/:version/token/app','api/:version.Token/getAppToken');

// 地址address
Route::post('api/:version/address','api/:version.Address/createOrUpdateAddress');
Route::get('api/:version/address','api/:version.Address/getUserAddress');

//订单
Route::post('api/:version/order','api/:version.Order/placeOrder');
Route::get('api/:version/order/:id','api/:version.Order/getDetail',[],['id'=>'\d+']);
Route::get('api/:version/order/by_user','api/:version.Order/getSummaryByUser');
Route::get('api/:version/order/paginate','api/:version.Order/getSummary');
Route::put('api/:version/order/delivery','api/:version.Order/delivery');

// 支付
Route::post('api/:version/pay/pre_order','api/:version.Pay/getPreOrder'); 
Route::post('api/:version/pay/notify','api/:version.Pay/receiveNotify'); 


// Route::get('api/v1/banner/:id','api/v1.Banner/getBanner');


// Route::rule('路由表达式','路由地址','请求类型','路由参数(数组)','变量规则');
// 请求类型：GET,POST,DELETE,PUT,* (支持所有)

// Route::rule('hello','sample/test/hello','GET',['https'=>false]);
// Route::rule('hello','sample/test/hello','GET|POST',['https'=>false]);

// 简化操作
// Route::get('hello/:id','sample/test/hello');
// Route::post('hello','sample/test/hello');
// Route::post('hello/:id','sample/test/hello');
// any  即* 所有
// Route::any('hello','sample/test/hello');
