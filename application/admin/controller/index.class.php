<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class index extends common {

	/**
	 * 管理员后台
	 */
	public function init() {
		debug();
		$total = D('guestbook')->field('id')->where(array('replyid'=>'0','isread'=>'0'))->total(); //未读消息
		include $this->admin_tpl('index');
	}
	

	/**
	 * 管理员登录
	 */	
	public function login() {
		if(isset($_POST['dosubmit'])) {
			if(empty($_SESSION['code']) || strtolower($_POST['code'])!=$_SESSION['code']){
				$_SESSION['code'] = '';
				showmsg(L('code_error'), '', 1);
			}
			$_SESSION['code'] = '';
			if(!is_username($_POST['username'])) showmsg(L('user_name_format_error'));
			if(!is_password($_POST['password'])) showmsg(L('password_format_error'));
			M('admin')->check_admin($_POST['username'], password($_POST['password']));
		}else{
			$this->_login();
		}
	}
	

	/**
	 * 管理员退出
	 */		
	public function public_logout() {
		$_SESSION = array();
		session_destroy();
		del_cookie('adminid');
		del_cookie('adminname');
		showmsg(L('you_have_safe_exit'), U('login'));
	}
	

	/**
	 * 管理员公共桌面
	 */		
	public function public_home() {
		yzm_base::load_common('lib/update'.EXT, 'admin');
		if(isset($_GET['up'])){update::check();}
		ob_start();	
		include $this->admin_tpl('public_home');
		$data = ob_get_contents();
		ob_end_clean();
		system_information($data);		
	}



	private function _login(){
		ob_start();	
		include $this->admin_tpl('login');
		$data = ob_get_contents();
		ob_end_clean();
		echo $data.base64_decode('PCEtLSBQb3dlcmVkIEJ5IFl6bUNNU+WGheWuueeuoeeQhuezu+e7nyAgLS0+');
	}
}