<?php
// +----------------------------------------------------------------------
// | Site:  [ http://www.yzmcms.com]
// +----------------------------------------------------------------------
// | Copyright: 袁志蒙工作室，并保留所有权利
// +----------------------------------------------------------------------
// | Author: YuanZhiMeng <214243830@qq.com>
// +---------------------------------------------------------------------- 
// | Explain: 这不是一个自由软件,您只能在不用于商业目的的前提下对程序代码进行修改和使用，不允许对程序代码以任何形式任何目的的再发布！
// +----------------------------------------------------------------------

defined('IN_YZMPHP') or exit('Access Denied');
new_session_start();

class common{
	
	public static $ip;
	
	public function __construct() {
		self::$ip = getip();
		self::check_admin();
		self::check_priv();
		self::check_ip();
		self::check_token();
		self::lock_screen();
		if(ROUTE_A == 'init') $_GET = array_map('htmlspecialchars', $_GET);
		if(get_config('admin_log')) self::manage_log();
	}


	/**
	 * 判断用户是否已经登录
	 */
	private function check_admin() {
		if(ROUTE_M =='admin' && ROUTE_C =='index' && ROUTE_A =='login') {
			return true;
		} else {
			$adminid = intval(get_cookie('adminid'));
			if(!isset($_SESSION['adminid']) || !isset($_SESSION['roleid']) || !$_SESSION['adminid'] || !$_SESSION['roleid'] || $adminid != $_SESSION['adminid']) {
				echo '<script type="text/javascript"> var url="'.U('admin/index/login').'"; if(top.location !== self.location){ top.location=url; }else{ window.location.href=url; } </script>';
				exit();
			}	
		}
	}


	/**
	 * 权限判断
	 */
	private function check_priv() {
		if(ROUTE_M =='admin' && ROUTE_C =='index' && in_array(ROUTE_A, array('login', 'init'))) return true;
		if($_SESSION['roleid'] == 1) return true;
		if(strpos(ROUTE_A, 'public_') === 0) return true;
		$r = D('admin_role_priv')->where(array('m'=>ROUTE_M,'c'=>ROUTE_C,'a'=>ROUTE_A,'roleid'=>$_SESSION['roleid']))->find();
		if(!$r) {
			!is_ajax() ? showmsg(L('no_permission_to_access'), 'stop') : return_json(array('status'=>0, 'message'=>L('no_permission_to_access')));
		}
	}


	/**
	 * 记录日志 
	 */
	private function manage_log() {
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
	private function check_ip(){
		$admin_prohibit_ip = get_config('admin_prohibit_ip');
		if(!$admin_prohibit_ip) return true;
		$arr = explode(',', $admin_prohibit_ip);
		foreach($arr as $val){
			//是否是IP段
			if(strpos($val,'*') !== false){ 
				if(strpos(self::$ip, str_replace('.*', '', $val)) !== false) showmsg('你在IP禁止段内,禁止访问！', 'stop');
			}else{
				//不是IP段,用绝对匹配
				if(self::$ip == $val) showmsg('IP地址绝对匹配,禁止访问！', 'stop');
			}
		}
 	}


 	/**
 	 * 检查锁屏状态
 	 */
	private function lock_screen() {
		if(isset($_SESSION['yzm_lock_screen']) && $_SESSION['yzm_lock_screen']==1) {
			if(strpos(ROUTE_A, 'public_') === 0 || ROUTE_A == 'login') return true;
			include $this->admin_tpl('index');exit();
		}
		return true;
	}
	
	
	/**
	 * 检查REFERER
	 */
	private function check_referer(){
		if(strpos(ROUTE_A, 'public_') === 0) return true;
		if(HTTP_REFERER && strpos(HTTP_REFERER, SERVER_PORT.HTTP_HOST) !== 0){
			$arr = explode(':', HTTP_HOST);
			if(strpos(HTTP_REFERER, SERVER_PORT.$arr[0]) !== 0) showmsg('非法来源，拒绝访问！', 'stop');
		}
		return true;
 	}


	/**
	 * 检查TOKEN
	 */
	private function check_token(){
		if(!is_post()) return true;
		if(strpos(ROUTE_A, 'public_') === 0 || (ROUTE_C =='index' && ROUTE_A =='login')) return true;
		if(isset($_POST['yzm_csrf_token']) && $_SESSION['yzm_csrf_token']!='' && ($_SESSION['yzm_csrf_token'] == $_POST['yzm_csrf_token']))  return true;
		is_ajax() ? return_json(array('status'=>0, 'message'=>L('token_error'))) : showmsg(L('token_error'), 'stop');
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