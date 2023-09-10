<?php
/**
 * debug.class.php   debug类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-07-06
 */

class debug {

	public static $info = array();
	public static $sqls = array();
	public static $request = array();
	public static $stoptime; 
	public static $msg = array(
						2 => '错误警告',
						8 => '错误通知',
						256 => '自定义错误',
						512 => '自定义警告',
						1024 => '自定义通知',
						2048 => '编码标准化警告',
						8192 => '已弃用警告',
						'Unknown' => '未知错误'
					);
	

	/**
	 * 在脚本结束处调用获取脚本结束时间的微秒值
	 */
	public static function stop(){
		self::$stoptime = microtime(true);  
	}

	
	/**
	 * 返回同一脚本中两次获取时间的差值
	 */
	public static function spent(){
		return round((self::$stoptime - SYS_START_TIME) , 4);  
	}

	
	/**
	 * 错误 handler
	 */
	public static function catcher($errno, $errstr, $errfile, $errline){
		if(APP_DEBUG && !defined('DEBUG_HIDDEN')){
			if(!isset(self::$msg[$errno])) 
				$errno = 'Unknown';

			if($errno==E_NOTICE || $errno==E_USER_NOTICE)
				$color = "#151515";
			else
				$color = "#b90202";

			$mess = '<span style="color:'.$color.'">';
			$mess .= self::$msg[$errno].' [文件 '.$errfile.' 中,第 '.$errline.' 行] ：';
			$mess .= $errstr;
			$mess .= '</span>'; 	
			self::addmsg($mess);			
		}else{
			if($errno == E_NOTICE) return '';
			write_error_log(array('Error', $errno, $errstr, $errfile, $errline));
		}
	}


	/**
	 * 致命错误 fatalerror
	 */
	public static function fatalerror(){
		if ($e = error_get_last()) {
            switch($e['type']){
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:  
                ob_end_clean();
                if(APP_DEBUG && !defined('DEBUG_HIDDEN')){
                	application::fatalerror($e['message'], $e['file'].' on line '.$e['line'], 1);	
           		}else{
           			write_error_log(array('FatalError', $e['message'], $e['file'], $e['line']));
           			application::halt('error message has been saved.', 500);
           		}
                break;
            }
        }
	}
	
	
	/**
	 * 捕获异常
	 * @param	object	$exception
	 */ 
	public static function exception($exception){
		if(APP_DEBUG && !defined('DEBUG_HIDDEN')){
			$mess = '<span style="color:#b90202">';
			$mess .= '系统异常 [文件 '.$exception->getFile().' 中,第 '.$exception->getLine().' 行] ：';
			$mess .= $exception->getMessage();
			$mess .= '</span>'; 		
			self::addmsg($mess);
		}else{
			write_error_log(array('ExceptionError', $exception->getMessage(), $exception->getFile(), $exception->getLine()));
		}
		showmsg($exception->getMessage(), 'stop');
	}

	
	/**
	 * 添加调试消息
	 * @param	string	$msg	调试消息字符串
	 * @param	int	    $type	消息的类型
	 * @param	int	    $start_time	开始时间，用于计算SQL耗时
	 */
	public static function addmsg($msg, $type=0, $start_time=0) {
		switch($type){
			case 0:
				self::$info[] = $msg;
				break;
			case 1:
				self::$sqls[] = htmlspecialchars($msg).'; [ RunTime:'.number_format(microtime(true)-$start_time , 6).'s ]';
				break;
			case 2:
				self::$request[] = $msg;
				break;
		}
	}


	/**
	 * 获取debug信息
	 */
	public static function get_debug() {
		return array(
			'info' => self::$info,
			'sqls' => self::$sqls,
			'request' => self::$request
		);
	}
	
	
	/**
	 * 输出调试消息
	 */
	public static function message(){
		$parameter = $_GET;
		unset($parameter['m'], $parameter['c'], $parameter['a']);
		$parameter = $parameter ? http_build_query($parameter) : '无';
		include(YP_PATH.'core'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR.'debug.tpl');	
	}
}
