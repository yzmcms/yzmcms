<?php
/**
 * 会员找回密码操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-19
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 

class reset{
	
	
	public function __construct() {
		//设置会员模块模板风格
		set_module_theme('default');
	}

	
	/**
	 * 选择密码找回方式
	 */			
	public function init(){ 
		include template('member', 'reset_type');
	}

	

	/**
	 * 选择邮箱找回密码
	 */			
	public function reset_email(){
		session_start(); 
		$_SESSION['step'] = isset($_SESSION['step']) ? $_SESSION['step'] : 1;

		if($_SESSION['step']==1 && isset($_POST['dosubmit'])){
			
			if(empty($_SESSION['code']) || strtolower($_POST['code']) != $_SESSION['code']){
				$_SESSION['code'] = '';
				showmsg(L('code_error'), '', 1);
			}
			$_SESSION['code'] = '';
			
			$data = $this->_check($_POST['username']);
			if(empty($data['email'])) showmsg('您没有绑定邮箱，请选择其他方式找回密码！', 'stop');
		   
			$email_code = $_SESSION['email_code'] = create_randomstr();
			$message = '您正通过此邮件找回密码，如非本人操作，请忽略！<br>本次验证码为：'.$email_code;
			$res = sendmail($data['email'], '邮箱找回密码验证', $message);
			if(!$res) showmsg('邮件发送失败，请联系网站管理员！');
			
			$_SESSION['email_arr'] = explode('@',$data['email']);
			$_SESSION['userid'] = $data['userid'];
			$_SESSION['emc_times'] = 5;
			$_SESSION['step'] = 2;
			
		}else if($_SESSION['step']==2 && isset($_POST['dosubmit'])){
			
			if($_SESSION['emc_times']=='' || $_SESSION['emc_times']<=0){
				 $_SESSION['step'] = 1;
				 showmsg("验证次数超过5次,请重新获取邮箱验证码！");
			}
			
			if(!empty($_SESSION['email_code']) && strtolower($_POST['email_code']) == strtolower($_SESSION['email_code'])){
				 unset($_SESSION['emc_times']);
				 $_SESSION['step'] = 3;
			}else{
				 $_SESSION['emc_times'] = $_SESSION['emc_times']-1;
				 showmsg('邮箱校验码错误！','',1);
			}
		}else if($_SESSION['step']==3 && isset($_POST['dosubmit'])){
			
			if(!isset($_POST['password']) || !is_password($_POST['password'])) showmsg(L('password_format_error'));
			
			D('member')->update(array('password' => password($_POST['password'])),array('userid'=>$_SESSION['userid']));
			unset($_SESSION['step'], $_SESSION['code'], $_SESSION['email_code'], $_SESSION['email_arr'], $_SESSION['userid']);
			showmsg('更新密码成功！', U('member/index/login'));
			
		}
		
		include template('member', 'reset_email');
	}



	/**
	 * 选择安全问题找回密码 
	 */			
	public function reset_problem(){
		session_start(); 
		$_SESSION['step'] = isset($_SESSION['step']) ? $_SESSION['step'] : 1;

		if($_SESSION['step']==1 && isset($_POST['dosubmit'])){
			
			if(empty($_SESSION['code']) || strtolower($_POST['code']) != $_SESSION['code']){
				$_SESSION['code'] = '';
				showmsg(L('code_error'), '', 1);
			}
			$_SESSION['code'] = '';
			
			$data = $this->_check($_POST['username']);
			if(empty($data['problem']) || empty($data['answer'])) showmsg('您没有设置安全问题，请选择其他方式找回密码！');
		   
			$_SESSION['problem'] = $data['problem'];
			$_SESSION['answer'] = $data['answer'];
			$_SESSION['userid'] = $data['userid'];
			$_SESSION['emc_times'] = 5;
			$_SESSION['step'] = 2;
			
		}else if($_SESSION['step']==2 && isset($_POST['dosubmit'])){
			
			if($_SESSION['emc_times']=='' || $_SESSION['emc_times']<=0){			
				 D('member')->update(array('status' => 2), array('userid'=>$_SESSION['userid']));  //锁定用户
				 unset($_SESSION['step'], $_SESSION['problem'], $_SESSION['answer'], $_SESSION['emc_times'], $_SESSION['userid']);
				 showmsg('验证次数超过5次，您已被锁定，请联系管理员！');
			}
			
			if(!empty($_SESSION['answer']) && $_POST['answer'] == $_SESSION['answer']){
				 unset($_SESSION['emc_times']);
				 $_SESSION['step'] = 3;
			}else{
				 $_SESSION['emc_times'] = $_SESSION['emc_times']-1;
				 showmsg('回答错误！','',1);
			}
		}else if($_SESSION['step']==3 && isset($_POST['dosubmit'])){
			
			if(!isset($_POST['password']) || !is_password($_POST['password'])) showmsg(L('password_format_error'));
			
			D('member')->update(array('password' => password($_POST['password'])),array('userid'=>$_SESSION['userid']));
			unset($_SESSION['step'], $_SESSION['problem'], $_SESSION['answer'], $_SESSION['userid']);
			showmsg('更新密码成功！', U('member/index/login'));
			
		}
		
		include template('member', 'reset_problem');
	}


	
	/**
	 * 检查用户名是否存在
	 */	
	private function _check($username){		
		if(!is_username($username)) showmsg(L('user_name_format_error'));
			
		$data = D('member')->field('userid,status,email,problem,answer')->where(array('username' => $username))->find();
		if(!$data) showmsg(L('user_does_not_exist'));
			
		//判断用户是否被锁定
		if($data['status'] == '2') showmsg('您已被锁定，请联系管理员！', 'stop');
		return $data;
	}
}