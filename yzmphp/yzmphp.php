<?php 
/**
 * YZMPHP框架入口文件   
 * 
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-09-19
 */

//设置系统的输出字符为utf-8
header('Content-Type:text/html;charset=utf-8');  

//设置时区（中国）
date_default_timezone_set('PRC');    		   

defined('YZMPHP_PATH') or exit('Access Denied.'); 

define('IN_YZMPHP', true);

//YZMPHP框架路径
define('YP_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
//YZMPHP框架版本信息
define('YZMPHP_VERSION', '2.0');
//YZMPHP应用目录
define('APP_PATH', YZMPHP_PATH.'application'.DIRECTORY_SEPARATOR);

//系统开始时间
define('SYS_START_TIME', microtime(true));
//系统时间
define('SYS_TIME', time());

//加载全局函数库
yzm_base::load_sys_func('global');

//主机协议
define('SERVER_PORT', is_ssl() ? 'https://' : 'http://');
//当前访问的主机名
define('HTTP_HOST', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));
//来源
define('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
//类文件后缀
define('EXT', '.class.php'); 
                  
//IS_CGI
define('IS_CGI', (0 === strpos(PHP_SAPI,'cgi') || false !== strpos(PHP_SAPI,'fcgi')) ? 1 : 0 );

//当前文件名
if(IS_CGI) {
	//CGI/FASTCGI模式下
	$_temp  = explode('.php', $_SERVER['SCRIPT_NAME']);
	define('PHP_FILE', rtrim(str_replace($_SERVER['HTTP_HOST'], '', $_temp[0].'.php'), '/'));
}else {
	define('PHP_FILE', rtrim($_SERVER['SCRIPT_NAME'], '/'));
}

//程序安装路径
define('SITE_PATH', str_replace('index.php', '', PHP_FILE));
//程序URL地址
define('SITE_URL', SERVER_PORT.HTTP_HOST.SITE_PATH);
//JS,IMG,CSS等URL地址
define('STATIC_URL', SITE_URL.'common/static/');

if(version_compare(PHP_VERSION,'5.4.0','<')) {
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
}else{
    define('MAGIC_QUOTES_GPC', false);
}

//加载公用文件
yzm_base::load_common('function/system.func.php');
yzm_base::load_common('function/extention.func.php');
yzm_base::load_common('data/version.php'); 
defined('YZMCMS_SOFTNAME') or define('YZMCMS_SOFTNAME', base64_decode('WXptQ01T5YaF5a65566h55CG57O757uf'));

class yzm_base {
		
	/**
	 * 初始化应用程序
	 */
	public static function creat_app() {
		return self::load_sys_class('application');
	}
		
	/**
	 * 加载系统类方法
	 * @param string $classname 类名
	 * @param string $path 扩展地址
	 * @param intger $initialize 是否初始化
	 * @return object or true
	 */
	public static function load_sys_class($classname, $path = '', $initialize = 1) {
		return self::_load_class($classname, $path, $initialize);
	}

	
	/**
	 * 加载类文件函数
	 * @param string $classname 类名
	 * @param string $path 扩展地址
	 * @param intger $initialize 是否初始化
	 */
	private static function _load_class($classname, $path = '', $initialize = 1) {
		static $classes = array();
		if (empty($path)) $path = YZMPHP_PATH.'yzmphp'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'class';

		$key = md5($path.$classname);
		if (isset($classes[$key])) {
			return $initialize&&!is_object($classes[$key]) ? new $classname : $classes[$key];
		}
		if (!is_file($path.DIRECTORY_SEPARATOR.$classname.EXT)) {
			debug::addmsg($path.DIRECTORY_SEPARATOR.$classname.EXT.L('does_not_exist'));
			return false;
		}
		
		include $path.DIRECTORY_SEPARATOR.$classname.EXT; 
		if ($initialize) {
			$classes[$key] = new $classname;
		} else {
			$classes[$key] = true;
		}
		return $classes[$key];
	}
	
	/**
	 * 加载系统的函数库
	 * @param string $func 函数库名
	 */
	public static function load_sys_func($func) {
		if (is_file(YZMPHP_PATH.'yzmphp'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.$func.'.func.php')) {
			include YZMPHP_PATH.'yzmphp'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.$func.'.func.php';
		}
	}
	
	
	/**
	 * 加载common目录下的文件
	 * @param string $path 文件地址（包括文件全称）
	 * @param string $m 模块(如果模块名为空，则加载根目录下的common)
	 */
	public static function load_common($path, $m = '') {
		if(!$m){
			if (is_file(YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.$path)) {
				return include YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.$path;
			}else{
				debug::addmsg(YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.$path.L('does_not_exist'));
			}			
		}else{
			if (is_file(APP_PATH.$m.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.$path)) {
				return include APP_PATH.$m.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.$path;
			}else{
				debug::addmsg(APP_PATH.$m.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.$path.L('does_not_exist'));
			}			
		}
	}
	
	
	/**
	 * 加载控制器
	 * @param string $c 控制器名
	 * @param string $m 模块
	 * @param intger $initialize 是否初始化
	 * @return object or true
	 */
	public static function load_controller($c, $m = '', $initialize = 1) {
		$m = empty($m) ? ROUTE_M : $m;
		if (empty($m)) return false;
		return self::_load_class($c, APP_PATH.$m.DIRECTORY_SEPARATOR.'controller', $initialize);
	}
	
	
	/**
	 * 加载模型
	 * @param string $classname 模型名
	 * @param string $m 模块
	 * @param intger $initialize 是否初始化
	 * @return object or true
	 */
	public static function load_model($classname, $m = '', $initialize = 1) {
		$m = empty($m) ? ROUTE_M : $m;
		if (empty($m)) return false;
		return self::_load_class($classname, APP_PATH.$m.DIRECTORY_SEPARATOR.'model', $initialize);
	}

}