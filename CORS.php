<?php
namespace app\api\behavior;

/**
* 
*/
class CORS
{
	//行为定义
	public function appInit(&$params){
		// 跨域
		header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: token,Origin, X-Requested-With, Content-Type, Accept");
        header('Access-Control-Allow-Methods: POST,GET,PUT');

        if (request()->isOptions()) {
        	exit();
        }
	}
}