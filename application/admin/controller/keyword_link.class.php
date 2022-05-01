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

class keyword_link extends common {

	/**
	 * 列表
	 */
	public function init() {
		$keyword_link = D('keyword_link');
		$total = $keyword_link->total();
		$page = new page($total, 15);
		$data = $keyword_link->order('id DESC')->limit($page->limit())->select();	
		include $this->admin_tpl('keyword_link_list');
	}
	
	
	/**
	 * 删除
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			if(D('keyword_link')->delete($_POST['id'], true)){
				delcache('keyword_link');	
				showmsg(L('operation_success'), '', 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}
	
	
	/**
	 * 添加
	 */
	public function add() {		
		$keyword_link = D('keyword_link');
		if(isset($_POST['dosubmit'])) { 
			$r = $keyword_link->field('id')->where(array('keyword' => $_POST['keyword']))->find();
			if($r) return_json(array('status'=>0,'message'=>'关键字已存在！'));
			
			$keyword_link->insert($_POST, true);
			delcache('keyword_link');
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			include $this->admin_tpl('keyword_link_add');
		}
		
	}
	
	
	/**
	 * 编辑
	 */
	public function edit() {				
		$keyword_link = D('keyword_link');
		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			if($keyword_link->update($_POST, array('id' => $id), true)){
				delcache('keyword_link');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = $keyword_link->where(array('id' => $id))->find();
			include $this->admin_tpl('keyword_link_edit');
		}
		
	}
	
}