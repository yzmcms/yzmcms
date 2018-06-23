<?php
/**
 * 会员中心第三方登录
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-19
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 

class other{

	
	public function __construct() {
		//设置会员模块模板风格
		set_module_theme('default');
	}

	
	public function init(){ 

	}


	
	/**
	 * QQ登录
	 */	
	public function qq_login(){
		session_start();
		$appid = get_config('qq_app_id');
		$appkey = get_config('qq_app_key');
		$callback = SERVER_PORT.HTTP_HOST.U('member/other/qq_login'); //回调地址 默认此方法，不能更改
		
		yzm_base::load_sys_class('qqapi', '', 0);
		if($appid=='' || $appkey=='') showmsg('QQ配置项为空，请联系管理员！', 'stop');		
		$qq_info = new qqapi($appid, $appkey, $callback);

		if(!isset($_GET['code'])){
			$qq_info->redirect_to_login();
		}else{
			$code = $_GET['code'];
			$_SESSION['fromlogin'] = 'qq';
			$openid = $_SESSION['openid'] = $qq_info->get_openid($code);
			if(!empty($openid)){
				$data = D('member')->where(array('connectid' => $openid, 'fromlogin' => 'qq'))->find();		
				if(!empty($data)){
					$this->_login($data);	
				}else{	
					$userinfo = $qq_info->get_user_info();
					$_SESSION['userinfo'] = $userinfo;
					$_SESSION['connectid'] = $openid;
					$_SESSION['fromlogin'] = 'qq';
					include template('member', 'bind');
				}
			}else if(isset($_SESSION['userinfo'])){  //防止刷新
				$userinfo = $_SESSION['userinfo'];
				include template('member', 'bind');
			}
		}
	}
	


	/**
	 * 账号绑定
	 */	
	public function bind(){
		session_start();
		
		if(!isset($_SESSION['connectid'])) showmsg(L('illegal_operation'), 'stop');
		
		$member = D('member');
		if($_POST['username'] == '' || $_POST['password'] == '') showmsg('用户名或密码不能为空！');
		$username = isset($_POST['username']) && is_username($_POST['username']) ? trim($_POST['username']) : showmsg(L('user_name_format_error'));
		$password = password($_POST['password']);
		
		$data = $member->where(array('username'=>$username))->find();
		if(!$data) showmsg(L('user_does_not_exist'));
		$data = $member->where(array('username'=>$username, 'password'=>$password))->find();
		if(!$data) showmsg('用户名或密码错误！');
		
		//先绑定第三方账号，可以是QQ、微信、新浪、百度等
		$member->update(array('connectid'=>$_SESSION['connectid'], 'fromlogin'=>$_SESSION['fromlogin']), array('userid'=>$data['userid']));
		unset($_SESSION['userinfo'], $_SESSION['connectid'], $_SESSION['fromlogin']);
		
		$this->_login($data);
	}

	
	
	/**
	 * 执行登录操作
	 */	
	private function _login($data){		
		if($data['status'] == '0') 
			showmsg('用户未通过审核！', 'stop');		
		else if($data['status'] == '2') 
			showmsg('用户已锁定！', 'stop');		
		else if($data['status'] == '3') 
			showmsg('用户已被管理员拒绝！', 'stop');
		
		$_SESSION['_userid'] = $data['userid'];
		$_SESSION['_username'] = $data['username'];
		set_cookie('_userid', $data['userid']);
		set_cookie('_username', $data['username']);
		set_cookie('_nickname', $data['username']);
		set_cookie('_groupid', $data['groupid']);
		
		$where = '';
		if($data['vip'] && $data['overduedate']<SYS_TIME)	$where .= '`vip`=0,';   //如果用户是vip用户，检查vip是否过期
		
		$where .= '`lastip`="'.getip().'",`lastdate`="'.SYS_TIME.'",`loginnum`=`loginnum`+1';
		D('member')->update($where, array('userid'=>$data['userid']));
		showmsg(L('login_success'), U('member/index/init'), 1);
	}
}