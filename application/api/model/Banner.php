<?php
namespace app\api\model;

use think\Exception;
use think\Db;
use think\Model;
/**
* Banner 模型
*/
class Banner extends BaseModel
{	
	// 隐藏客户端不需要的字段
	protected $hidden = ['delete_time','update_time'];

	public function items(){
		// hasMany('关联模型名','外键名','主键名',['模型别名定义']);
		return $this->hasMany('BannerItem','banner_id','id');
	}
	// protected $table = 'image';//改表
	public static function getBannerById($id){
		// TODO:根据banner ID号 获取Banner信息
		// get find all select 
		$banner = self::with(['items','items.img'])->find($id);

		// $result = Db::query("select * from banner_item where banner_id =?",[$id]);
		// $result = Db::table('banner_item')->where('banner_id','=',$id)->select();
		// $result = db('banner_item')->where('banner_id','=',$id)->select();
		// where('字段名','表达式','查询条件')
		// 1、表达式  2、数组  3、闭包
		
		// $result = Db::table('banner_item')
		// ->where(function($query) use ($id){
		// 	$query->where('banner_id','=',$id)->where('id','<',3)->where('key_word','=','25');
		// })->select();
		
		// $result = Db::table('banner_item')
		// ->where(function($query) use ($id){
		// 	$query->where('banner_id','=',$id);
		// })->select();
		
		return $banner;
	}
}