<?php
namespace app\api\model;
use think\Model;

/**
* 专题模型
*/
class Theme extends BaseModel
{
	// 需要隐藏的的字段
	protected $hidden = ['delete_time','update_time','topic_img_id','head_img_id'];
	// 一对一关联
	public function topicImg(){
		// hasOne('关联模型名','外键名','主键名',['模型别名定义'],'join类型');
		// belongsTo('关联模型名','外键名','关联表主键名',['模型别名定义'],'join类型');
		return $this->belongsTo('Image','topic_img_id','id');
	}
	public function headImg(){
		return $this->belongsTo('Image','head_img_id','id');
	}

	// 多对多关联  belongsToMany('关联模型名','中间表名称','关联外键','关联模型主键','别名定义')
	public function products(){
		return $this->belongsToMany('Product','theme_product','product_id','theme_id');
	}

	//获取专题关联产品
	public static function getThemesWithProducts($id){
		$theme = self::with('products,topicImg,headImg')->find($id);
		return $theme;
	}
}