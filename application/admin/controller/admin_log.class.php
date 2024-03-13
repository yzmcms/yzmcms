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
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class admin_log extends common {

	/**
	 * 后台操作日志列表
	 */
	public function init() {
		$admin_log = D('admin_log');
		$total = $admin_log->total();
		$page = new page($total, 15);
		$data = $admin_log->order('id DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('admin_log_list');
	}


	/**
	 * 后台操作日志删除
	 */
	public function del_log() {
		if(isset($_GET['dosubmit'])){	
			if(D('admin_log')->delete(array('logtime<' => strtotime('-1 month')))){	  		
				showmsg(L('operation_success'), '', 1);		 
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
			$type = isset($_GET['type']) ? intval($_GET['type']) : 1;
			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';
			if(isset($_GET['adminname']) && $_GET['adminname']){
				$where['adminname'] = $_GET['adminname'];
			}
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where['logtime>='] = strtotime($_GET['start']);
				$where['logtime<='] = strtotime($_GET['end']);
			}
			if($searinfo){
				if($type == '1')
					$where['module'] = $searinfo;
				elseif($type == '2')
					$where['ip'] = '%'.$searinfo.'%';
				else
					$where['querystring'] = '%'.$searinfo.'%';
			}			
		}
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $admin_log->where($where)->total();
		$page = new page($total, 15);
		$data = $admin_log->where($where)->order('id DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('admin_log_list');
	}
	
	
	/**
	 * 后台登录日志列表
	 */
	public function admin_login_log_list() {
		$where = $_SESSION['roleid']>1 ? array('adminname' => $_SESSION['adminname']) : array();
		$admin_login_log = D('admin_login_log');
		if(isset($_GET['dosubmit'])){	
			$loginresult = isset($_GET['loginresult']) ? intval($_GET['loginresult']) : 99;
			$type = isset($_GET['type']) ? intval($_GET['type']) : 1;
			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';
			if($loginresult != 99){
				$where['loginresult'] = $loginresult;
			}
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where['logintime>='] = strtotime($_GET['start']);
				$where['logintime<='] = strtotime($_GET['end']);
			}
			if($searinfo){
				if($type == '1')
					$where['adminname'] = $_SESSION['roleid']>1 ? $_SESSION['adminname'] : '%'.$searinfo.'%';
				else
					$where['loginip'] = '%'.$searinfo.'%';
			}			
		}
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $admin_login_log->where($where)->total();
		$page = new page($total, 15);
		$data = $admin_login_log->where($where)->order('id DESC')->limit($page->limit())->select();	
		include $this->admin_tpl('admin_login_log_list');
	}


	/**
	 * 后台登录日志删除
	 */
	public function del_login_log() {
		if(isset($_GET['dosubmit'])){	
			$where = array('logintime<' => strtotime('-1 month'));
			if($_SESSION['roleid'] > 1) $where['adminname'] = $_SESSION['adminname'];

			if(D('admin_login_log')->delete($where)){	  		
				showmsg(L('operation_success'), '', 1);		 
			}else{	
				showmsg("没有数据被删除！");				 
			}			
		}
	}
}