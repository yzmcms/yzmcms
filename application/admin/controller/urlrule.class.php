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

class urlrule extends common {

	/**
	 * URL规则列表
	 */
	public function init() {
		$urlrule = D('urlrule');
		$total = $urlrule->total();
		$page = new page($total, 15);
		$data = $urlrule->order('listorder ASC,urlruleid ASC')->limit($page->limit())->select();	
		include $this->admin_tpl('urlrule_list');
	}
	
	
	/**
	 * 删除URL规则
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			if(D('urlrule')->delete($_POST['id'], true)){
				$site_mapping = self::$siteid ? 'site_mapping_site_'.self::$siteid : 'site_mapping_index_'.self::$siteid;
				delcache($site_mapping);
				delcache('urlrule');
				showmsg(L('operation_success'), '', 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}
	
	
	/**
	 * 添加URL规则
	 */
	public function add() {		
		$urlrule = D('urlrule');
		if(isset($_POST['dosubmit'])) { 
			if(!preg_match('/^([a-zA-Z0-9]|[\/\(\)\\\+\-~!@_]){0,50}$/', $_POST['urlrule'])) return_json(array('status'=>0,'message'=>'URL规则不符合规范！'));
			$r = $urlrule->field('urlrule')->where(array('urlrule' => $_POST['urlrule']))->find();
			if($r) return_json(array('status'=>0,'message'=>'URL规则已存在！'));
			
			$urlrule->insert($_POST, true);
			$site_mapping = self::$siteid ? 'site_mapping_site_'.self::$siteid : 'site_mapping_index_'.self::$siteid;
			delcache($site_mapping);
			delcache('urlrule');
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			include $this->admin_tpl('urlrule_add');
		}
		
	}
	
	
	/**
	 * 编辑URL规则
	 */
	public function edit() {				
		$urlrule = D('urlrule');
		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			if(!preg_match('/^([a-zA-Z0-9]|[\/\(\)\\\+\-~!@_]){0,50}$/', $_POST['urlrule'])) return_json(array('status'=>0,'message'=>'URL规则不符合规范！'));
			if($urlrule->update($_POST, array('urlruleid' => $id), true)){
				$site_mapping = self::$siteid ? 'site_mapping_site_'.self::$siteid : 'site_mapping_index_'.self::$siteid;
				delcache($site_mapping);
				delcache('urlrule');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = $urlrule->where(array('urlruleid' => $id))->find();
			include $this->admin_tpl('urlrule_edit');
		}
		
	}
	
}