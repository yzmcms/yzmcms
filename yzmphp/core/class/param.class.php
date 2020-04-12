<?php
/**
 * param.class.php	 参数处理类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-05-12
 */
 
class param {

	private $route_config = '';  //路由配置
	
	public function __construct() {
		$this->route_config = C('route');
		if(URL_MODEL != 0){
			if(C('set_pathinfo')) $this->set_pathinfo();
			$this->pathinfo_url();
		}
		return true;
	}

	
	/**
	 * 获取模型
	 */
	public function route_m() {
		$m = isset($_GET['m']) && !empty($_GET['m']) ? $_GET['m'] : (isset($_POST['m']) && !empty($_POST['m']) ? $_POST['m'] : '');
		$m = $this->safe_deal($m);
		if (empty($m)) {
			return $this->route_config['m'];
		} else {
			if(is_string($m)) return $m;
		}
	}

	
	/**
	 * 获取控制器
	 */
	public function route_c() {
		$c = isset($_GET['c']) && !empty($_GET['c']) ? $_GET['c'] : (isset($_POST['c']) && !empty($_POST['c']) ? $_POST['c'] : '');
		$c = $this->safe_deal($c);
		if (empty($c)) {
			return $this->route_config['c'];
		} else {
			if(is_string($c)) return $c;
		}
	}

	
	/**
	 * 获取事件
	 */
	public function route_a() {
		$a = isset($_GET['a']) && !empty($_GET['a']) ? $_GET['a'] : (isset($_POST['a']) && !empty($_POST['a']) ? $_POST['a'] : '');
		$a = $this->safe_deal($a);
		if (empty($a)) {
			return $this->route_config['a'];
		} else {
			if(is_string($a)) return $a;
		}
	}


	/**
	 * 安全处理函数
	 * 处理m,a,c
	 */
	private function safe_deal($str) {
		if(!MAGIC_QUOTES_GPC) $str = addslashes($str);
		return str_replace(array('/', '.'), '', $str);
	}
	
	
	/**
	 * 解析PATH_INFO模式
	 */	
	private function pathinfo_url(){
		if(!isset($_GET['s'])) return false;
		$_SERVER['PATH_INFO'] = $_GET['s'];
		unset($_GET['s']);
		if (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])){
			$_SERVER['PATH_INFO'] = str_ireplace(array(C('url_html_suffix'), 'index.php'), '', $_SERVER['PATH_INFO']);
			if(C('route_mapping')) $this->mapping(set_mapping());
			$pathinfo = explode('/', trim($_SERVER['PATH_INFO'], '/'));		
			$_GET['m'] = isset($pathinfo[0]) ? $pathinfo[0] : '';
			$_GET['c'] = isset($pathinfo[1]) ? $pathinfo[1] : '';
			$_GET['a'] = isset($pathinfo[2]) ? $pathinfo[2] : '';

			for($i = 3; $i<count($pathinfo); $i+=2){
				if(isset($pathinfo[$i+1])) $_GET[$pathinfo[$i]] = $pathinfo[$i+1];
			}
		}
		return true;
	}	
	
	
	/**
	 * 路由映射
	 */	
	private function mapping($rules){
		if(!is_array($rules)) return;
		$pathinfo = trim($_SERVER['PATH_INFO'], '/');
		foreach ($rules as $k=>$v) {
			$reg = '/'.$k.'/i';
			if(preg_match($reg, $pathinfo)){
				$res = preg_replace($reg, $v, $pathinfo);
				$_SERVER['PATH_INFO'] = '/'.$res;
				break;
			}
		}
	}
	
	
	/**
	 * 设置PATHINFO
	 */	
	private function set_pathinfo(){
		if(isset($_GET['s']) && !empty($_GET['s'])) return;
		$pathinfo = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']);
		if($pathinfo){
			$pathinfo = urldecode($pathinfo);
			$pos = strpos($pathinfo, '?');
			if($pos !== false) $pathinfo = substr($pathinfo, 0, $pos);
			if($pathinfo) $_GET['s'] = $pathinfo;
		}
	}
}
?>