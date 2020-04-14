<?php
session_start();

class common{
	
	public static $ip;
	
	public function __construct() {
		self::$ip = getip();
		self::check_admin();
		self::check_priv();
		self::check_ip();
		if(get_config('admin_log')) self::manage_log();
	}

	/**
	 * 判断用户是否已经登录
	 */
	final private function check_admin() {
		if(ROUTE_M =='admin' && ROUTE_C =='index' && ROUTE_A =='login') {
			return true;
		} else {
			$adminid = intval(get_cookie('adminid'));
			if(!isset($_SESSION['adminid']) || !isset($_SESSION['roleid']) || !$_SESSION['adminid'] || !$_SESSION['roleid'] || $adminid != $_SESSION['adminid']) {
				echo '<script type="text/javascript"> var url="'.U('admin/index/login').'"; if(top.location !== self.location){ top.location=url; }else{ window.location.href=url; } </script>';
				exit();
			}	
			self::check_referer();
		}
	}

	/**
	 * 权限判断
	 */
	final private function check_priv() {
		if(ROUTE_M =='admin' && ROUTE_C =='index' && in_array(ROUTE_A, array('login', 'init'))) return true;
		if($_SESSION['roleid'] == 1) return true;
		if(strpos(ROUTE_A, 'public_') === 0) return true;
		$r = D('admin_role_priv')->where(array('m'=>ROUTE_M,'c'=>ROUTE_C,'a'=>ROUTE_A,'roleid'=>$_SESSION['roleid']))->find();
		if(!$r) showmsg(L('no_permission_to_access'), 'stop');
	}

	/**
	 * 记录日志 
	 */
	final private function manage_log() {
		if(ROUTE_A == '' || ROUTE_A == 'init' || strpos(ROUTE_A, '_list') || in_array(ROUTE_A, array('login', 'public_home'))) {
			return false;
		}else {
			$adminid = $_SESSION['adminid'];
			$adminname = $_SESSION['adminname'];
			$url = 'm='.ROUTE_M.'&c='.ROUTE_C.'&a='.ROUTE_A;
			D('admin_log')->insert(array('module'=>ROUTE_M,'action'=>ROUTE_C,'adminname'=>$adminname,'adminid'=>$adminid,'querystring'=>$url,'logtime'=>SYS_TIME,'ip'=>self::$ip));
		}		
	}
	
	/**
	 * 后台IP禁止判断
	 */
	final private function check_ip(){
		$admin_prohibit_ip = get_config('admin_prohibit_ip');
		if(!$admin_prohibit_ip) return true;
		$arr = explode(',', $admin_prohibit_ip);
		foreach($arr as $val){
			//是否是IP段
			if(strpos($val,'*')){
				if(strpos(self::$ip, str_replace('.*', '', $val)) !== false) showmsg('你在IP禁止段内,禁止访问！', 'stop');
			}else{
				//不是IP段,用绝对匹配
				if(self::$ip == $val) showmsg('IP地址绝对匹配,禁止访问！', 'stop');
			}
		}
 	}
	
	/**
	 * 检查REFERER
	 */
	final private function check_referer(){
		if(strpos(ROUTE_A, 'public_') === 0) return true;
		if(HTTP_REFERER){
			if(strpos(HTTP_REFERER, SERVER_PORT.HTTP_HOST) === false)  showmsg('非法来源，拒绝访问！', 'stop');
		}
		return true;
 	}
	
	/**
	 * 加载后台模板
	 * @param string $file 文件名
	 * @param string $m 模型名
	 */
	final public static function admin_tpl($file, $m = '') {
		$m = empty($m) ? ROUTE_M : $m;
		if(empty($m)) return false;
		return APP_PATH.$m.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$file.'.html';
	}	
}
?>