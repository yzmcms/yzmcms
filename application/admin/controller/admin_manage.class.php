<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class admin_manage extends common {

	/**
	 * 管理员管理列表
	 */
	public function init() {
		$roleid = isset($_GET['roleid']) ? intval($_GET['roleid']) : 0;
		if(!$roleid){
			$data = D('admin')->order('adminid ASC')->select();
		}else{
			$data = D('admin')->where(array('roleid' => $roleid))->order('adminid ASC')->select();	
		}
		
		include $this->admin_tpl('admin_list');
	}
	
	
	/**
	 * 删除管理员
	 */
	public function delete() {
		$adminid = intval($_GET['adminid']);
		if($adminid == '1' || $adminid == $_SESSION['adminid']) showmsg('不能删除ID为1的管理员，或不能删除自己！', 'stop');
		D('admin')->delete(array('adminid'=>$adminid));
		showmsg(L('operation_success'));
	}
	
	
	/**
	 * 添加管理员
	 */
	public function add() {
		$admin = D('admin');
		$admin_role = D('admin_role');
		$roles = $admin_role->where(array('disabled'=>'0'))->select();
		if(isset($_POST['dosubmit'])) { 
			if(!check_token($_POST['token'])){
				return_json(array('status'=>0,'message'=>L('lose_parameters')));
			}
			
			if($_POST['roleid'] < $_SESSION['roleid']) return_json(array('status'=>0,'message'=>'您无权添加该角色管理员，请更换角色'));
			
			if(!is_username($_POST["adminname"]))  return_json(array('status'=>0,'message'=>L('user_name_format_error')));
			if(!is_password($_POST["password"])) return_json(array('status'=>0,'message'=>L('password_format_error')));
			if($_POST["email"]!=''){
				if(!is_email($_POST["email"])) return_json(array('status'=>0,'message'=>L('mail_format_error')));
			}
			$res = $admin->where(array('adminname'=>$_POST["adminname"]))->find();
			if($res) return_json(array('status'=>0,'message'=>L('user_already_exists')));
			
			$_POST['password'] = password($_POST['password']);
			$r = $admin_role->field('rolename')->where(array('roleid' => $_POST['roleid']))->find();
			$_POST['rolename'] = $r['rolename'];	
			$_POST['addtime'] = SYS_TIME;	
			$_POST['addpeople'] = $_SESSION['adminname'];	
			$admin->insert($_POST, true);
			return_json(array('status'=>1,'message'=>L('operation_success')));
		} else {
			include $this->admin_tpl('admin_add');
		}
		
	}

	
	/**
	 * 编辑管理员
	 */
	public function edit() {
		$admin = D('admin');
		$admin_role = D('admin_role');
		$roles = $admin_role->where(array('disabled'=>'0'))->select();
		if(isset($_POST['dosubmit'])) {
			if(!check_token($_POST['token'])){
				return_json(array('status'=>0,'message'=>L('lose_parameters')));
			}
			
			if($_POST['roleid'] < $_SESSION['roleid']) return_json(array('status'=>0,'message'=>'您无权添加该角色管理员，请更换角色'));
			
			$adminid = isset($_POST['adminid']) ? intval($_POST['adminid']) : 0;
			unset($_POST["adminname"]);
			if($_POST['password']){
				if(!is_password($_POST["password"])) return_json(array('status'=>0,'message'=>L('password_format_error')));
				$_POST['password'] = password($_POST['password']);
			}else{
				unset($_POST['password']);
			}
			
			if($_POST["email"]!=''){
				if(!is_email($_POST["email"])) return_json(array('status'=>0,'message'=>L('mail_format_error')));
			}

			$r = $admin_role->field('rolename')->where(array('roleid' => $_POST['roleid']))->find();
			$_POST['rolename'] = $r['rolename'];			
			if($admin->update($_POST, array('adminid' => $adminid), true)){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		} else {
			$adminid = isset($_GET['adminid']) ? intval($_GET['adminid']) : 0;
			$data = $admin->where(array('adminid' => $adminid))->find();
			include $this->admin_tpl('admin_edit');
		}
		
	}	

	
	/**
	 * 编辑用户信息
	 */
	public function public_edit_info() {		
		$adminid = $_SESSION['adminid'];
		if(isset($_POST['dosubmit'])) {
			if($_POST['email'] && !is_email($_POST['email'])) return_json(array('status'=>0,'message'=>L('mail_format_error')));
			if(D('admin')->update(array('realname' => $_POST['realname'], 'nickname' => $_POST['nickname'], 'email' => $_POST['email']), array('adminid' => $adminid), true)){
				$res = D('admin')->where(array('adminid' => $adminid))->find();
				$_SESSION['admininfo'] = $res;
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
						
		} else {
			$data = D('admin')->where(array('adminid'=>$adminid))->find();
			include $this->admin_tpl('public_edit_info');			
		}
		
	}
	

	/*
	 * 管理员修改密码
	 */
	public function public_edit_pwd() {		
		$adminid = $_SESSION['adminid'];
		if(isset($_POST['dosubmit'])) {
			if(!$_POST['password']) return_json(array('status'=>0,'message'=>L('data_not_modified')));
			$admin = D('admin');
			if(!$admin->where(array('adminid' => $adminid,'password' => password($_POST['old_password'])))->find()) 
				return_json(array('status'=>0,'message'=>'旧密码不正确！'));

			if($admin->update(array('password' => password($_POST['password'])), array('adminid' => $adminid))){
				session_destroy();
				del_cookie('adminid');
				del_cookie('adminname');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>L('data_not_modified')));
			}
						
		} else {
			$data = D('admin')->where(array('adminid'=>$adminid))->find();
			include $this->admin_tpl('public_edit_pwd');			
		}
		
	}
	
}