<?php
/**
 * 管理员后台会员组别操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-15
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class member_group extends common{

	
	/**
	 * 组别列表
	 */	
	public function init(){ 
		$member_group = D('member_group');
		$data = $member_group->order('groupid ASC')->select();			
		include $this->admin_tpl('member_group_list');
	}
	

	
	/**
	 * 添加组别
	 */	
	public function add(){ 
 		if(isset($_POST['dosubmit'])) {
			$_POST['experience'] = isset($_POST['experience']) ? intval($_POST['experience']) : return_json(array('status'=>0, 'message'=>'ERROR'));
			if(isset($_POST['authority'])){
				$_POST['authority'] = join(',',$_POST['authority']);
			}else{
				$_POST['authority'] = '';
			}
			$_POST['is_system'] = 0;
			D('member_group')->insert($_POST, true);
			delcache('member_group');
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}
		include $this->admin_tpl('member_group_add');
	}

	

	
	/**
	 * 修改组别
	 */	
	public function edit(){ 
 		if(isset($_POST['dosubmit'])) {
			$groupid = isset($_POST['groupid']) ? intval($_POST['groupid']) : 0;
			$_POST['experience'] = isset($_POST['experience']) ? intval($_POST['experience']) : return_json(array('status'=>0, 'message'=>'ERROR'));
			if(isset($_POST['authority'])){
				$_POST['authority'] = join(',',$_POST['authority']);
			}else{
				$_POST['authority'] = '';
			}
			unset($_POST['is_system']);
			if(D('member_group')->update($_POST, array('groupid' => $groupid), true)){
				delcache('member_group');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}
		$groupid = isset($_GET['groupid']) ? intval($_GET['groupid']) : 0;
		$data = D('member_group')->where(array('groupid' => $groupid))->find();
		include $this->admin_tpl('member_group_edit');
	}
	
	
	
	/**
	 * 删除组别
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			if(D('member_group')->delete($_POST['id'], true)){
				delcache('member_group');
				showmsg(L('operation_success'), '', 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}
}