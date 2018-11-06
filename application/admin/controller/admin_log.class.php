<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class admin_log extends common {

	/**
	 * 后台操作日志列表
	 */
	public function init() {
		$admin_log = D('admin_log');
		$total = $admin_log->total();
		$page = new page($total, 10);
		$data = $admin_log->order('id DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('admin_log_list');
	}


	/**
	 * 后台操作日志删除
	 */
	public function del_log() {
		if(isset($_GET['dosubmit'])){	
			if(D('admin_log')->delete(array('logtime<' => strtotime('-1 month')))){	  		
				showmsg(L('operation_success'));		 
			}else{	
				showmsg("没有数据被删除！");				 
			}			
		}
	}

	
	/**
	 * 后台操作日志搜索
	 */
	public function search_log() {
		$admin_log = D('admin_log');
		$where = array();
		if(isset($_GET['dosubmit'])){	
			if(isset($_GET['adminname']) && $_GET['adminname']){
				$where['adminname'] = $_GET['adminname'];
			}
			if(isset($_GET['module']) && $_GET['module']){
				$where['module'] = $_GET['module'];
			}
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where['logtime>='] = strtotime($_GET['start']);
				$where['logtime<='] = strtotime($_GET['end']);
			}			
		}
		$total = $admin_log->where($where)->total();
		$page = new page($total, 10);
		$data = $admin_log->where($where)->order('id DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('admin_log_list');
	}
	
	/**
	 * 后台登录日志列表
	 */
	public function admin_login_log_list() {
		$admin_login_log = D('admin_login_log');
		$total = $admin_login_log->total();
		$page = new page($total, 10);
		$data = $admin_login_log->order('id DESC')->limit($page->limit())->select();	
		include $this->admin_tpl('admin_login_log_list');
	}


	/**
	 * 后台登录日志删除
	 */
	public function del_login_log() {
		if(isset($_GET['dosubmit'])){	
			if(D('admin_login_log')->delete(array('logintime<' => strtotime('-1 month')))){	  		
				showmsg(L('operation_success'));		 
			}else{	
				showmsg("没有数据被删除！");				 
			}			
		}
	}
}