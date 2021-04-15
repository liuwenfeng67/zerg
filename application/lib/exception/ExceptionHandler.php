<?php
namespace app\lib\exception;

use think\Exception;
use think\Request;
use think\Log;
use think\exception\Handle;
use app\lib\exception\BaseException;
/**
* 自定义异常处理类(重写render方法)
*/
class ExceptionHandler extends Handle
{
	// HTTP 状态码
	private $code;
	// 错误具体信息
	private $msg;
	// 自定义错误码
	private $errorCode;
	// 需要返回客户端当前请求的URL地址

	// 全局异常处理方法
	// 所有代码抛出的异常都会通过render方法来渲染，最后来决定返回到客户端的到底是什么形式的错误信息
	public function render(\Exception $e){
		// 判断当前是否是自定义的异常
		if ($e instanceof BaseException) {
			$this->code = $e->code;
			$this->msg = $e->msg;
			$this->errorCode = $e->errorCode;
		}else{
			// 判断是不是调试模式，如果是则执行tp5自带render方法
			// 如果不是，是生产模式的话则返回json格式信息给客户端
			// dump(config('app_debug'));die;
			if (config('app_debug')) {
				return parent::render($e);
			}else{
				$this->code = 500;
				$this->msg = '服务器内部错误，不想告诉你';
				$this->errorCode = 999;

				$this->recordErrorLog($e);
			}
			
		}
		// 获取当前请求的URL地址
		$request = Request::instance();
		
		$result = [
			'msg' => $this->msg,
			'url' => $request->url(),
			'error_code' => $this->errorCode
		];

		return json($result,$this->code);
	}

	// 定义记录错误日志
	private function recordErrorLog(\Exception $e){
		// 初始化配置
		Log::init([
				'type' => 'File',
				'path' => LOG_PATH,
				'level' => ['error']
			]);
		Log::record($e->getMessage(),'error');
	}
	
}