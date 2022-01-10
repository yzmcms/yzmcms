<?php
/**
 * 会员中心第三方登录
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-10-21
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
new_session_start();

class other{

	
	public function __construct() {
		//设置会员模块模板风格
		set_module_theme(get_config('member_theme'));
	}

	
	public function init(){ 

	}


	
	/**
	 * QQ登录
	 */	
	public function qq_login(){
		$appid = get_config('qq_app_id');
		$appkey = get_config('qq_app_key');
		$callback = U('member/other/qq_login'); //回调地址 默认此方法，不能更改
		
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
				$userid = D('member_authorization')->field('userid')->where(array('authname' => 'qq', 'token' => $openid ))->order('id DESC')->one();		
				if($userid){
					$this->_login($userid);	
				}else{	
					$userinfo = $qq_info->get_user_info();
					$userinfo = array(
						'nickname' => $userinfo['nickname'],
						'userpic' => $userinfo['figureurl_qq_2'],
						'address' => $userinfo['province'].$userinfo['city']
					);
					$_SESSION['userinfo'] = $userinfo;
					$_SESSION['connectid'] = $openid;
					$_SESSION['fromlogin'] = 'qq';
					$_SESSION['fromname'] = 'QQ';
					include template('member', 'bind');
				}
			}else if(isset($_SESSION['userinfo'])){  //防止刷新
				$userinfo = $_SESSION['userinfo'];
				include template('member', 'bind');
			}
		}
	}


	/**
	 * 微博登录
	 */	
	public function weibo_login(){
		$appid = get_config('weibo_key');
		$appkey = get_config('weibo_secret');
		$callback = U('member/other/weibo_login'); //回调地址 默认此方法，不能更改
		
		yzm_base::load_common('saetv2.ex.class.php', 'member');
		if($appid=='' || $appkey=='') showmsg('微博配置项为空，请联系管理员！', 'stop');		
		$obj = new SaeTOAuthV2( $appid , $appkey );

		if(!isset($_GET['code'])){
			$redirect = $obj->getAuthorizeURL( $callback );
			header("Location:$redirect");
		}else{
			$keys = array();
			$keys['code'] = $_REQUEST['code'];
			$keys['redirect_uri'] = $callback;
			try {
				$token = $obj->getAccessToken( 'code', $keys ) ;
			} catch (OAuthException $e) {
			}
			
			if($token) $_SESSION['token'] = $token;
			
			$sa = new SaeTClientV2($appid, $appkey, $_SESSION['token']['access_token'] );
			$uid_get = $sa->get_uid();
			$uid = $uid_get['uid'];
			$info = $sa->show_user_by_id($uid);//根据ID获取用户等基本信息
			if(empty($info['id'])){
				showmsg('获取用户信息失败！', U('member/index/login'));
			}  

			$userid = D('member_authorization')->field('userid')->where(array('authname' => 'weibo', 'token' => $info['id'] ))->order('id DESC')->one();
			if($userid){
				$this->_login($userid);	
			}else{	
				$userinfo = array(
					'nickname' => $info['name'],
					'userpic' => $info['avatar_large'],
					'address' => $info['location']
				);
				$_SESSION['userinfo'] = $userinfo;
				$_SESSION['connectid'] = $info['id'];
				$_SESSION['fromlogin'] = 'weibo';
				$_SESSION['fromname'] = '微博';
				include template('member', 'bind');
			}
		}
	}
	


	/**
	 * 账号绑定
	 */	
	public function bind(){
		
		if(!isset($_SESSION['connectid']) || !isset($_SESSION['fromlogin'])) showmsg(L('illegal_operation'), 'stop');
		
		$member = D('member');
		if(empty($_POST['username']) ||  empty($_POST['password'])) showmsg('用户名或密码不能为空！');
		$username = isset($_POST['username']) && is_username($_POST['username']) ? trim($_POST['username']) : showmsg(L('user_name_format_error'));
		$password = password($_POST['password']);
		
		$data = $member->field('userid,password')->where(array('username'=>$username))->find();
		if(!$data) showmsg(L('user_does_not_exist'));
		if($data['password'] != $password) showmsg(L('password_error'));
		
		$this->_add_auth($data['userid'], $_SESSION['fromlogin'], $_SESSION['connectid'], $_SESSION['userinfo']);
		unset($_SESSION['userinfo'], $_SESSION['fromlogin'], $_SESSION['connectid'], $_SESSION['fromname']);
		
		$this->_login($data['userid']);
	}

	
	
	/**
	 * 执行登录操作
	 */	
	private function _login($userid){
		$data = D('member')->field('userid,username,status,groupid,vip,overduedate')->where(array('userid'=>$userid))->find();
		if($data['status'] == '0') 
			showmsg('用户未通过审核！', 'stop');		
		else if($data['status'] == '2') 
			showmsg('用户已锁定！', 'stop');		
		else if($data['status'] == '3') 
			showmsg('用户已被管理员拒绝！', 'stop');
		
		$_SESSION['_userid'] = $data['userid'];
		$_SESSION['_username'] = $data['username'];
		set_cookie('_userid', $data['userid'], 0, true);
		set_cookie('_username', $data['username'], 0, true);
		set_cookie('_groupid', $data['groupid'], 0, true);
		set_cookie('_nickname', $data['username']);
		
		$where = '';
		if($data['vip'] && $data['overduedate']<SYS_TIME)	$where .= '`vip`=0,';   //如果用户是vip用户，检查vip是否过期
		
		$where .= '`lastip`="'.getip().'",`lastdate`="'.SYS_TIME.'",`loginnum`=`loginnum`+1';
		D('member')->update($where, array('userid'=>$data['userid']));
		showmsg(L('login_success'), U('member/index/init'), 1);
	}


	/**
	 * 添加第三方登录
	 */	
	private function _add_auth($userid, $authname, $token, $userinfo = array()){
		$data = array(
			'userid' => $userid,
			'authname' => $authname,
			'token' => $token,
			'userinfo' => json_encode($userinfo),
			'inputtime' => SYS_TIME
		);
		return D('member_authorization')->insert($data);
	}

}