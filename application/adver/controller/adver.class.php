<?php
/**
 * 广告管理模块
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-01-18
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);
yzm_base::load_sys_class('form','',0);

class adver extends common {
	
	
	/**
	 * 列表
	 */
	public function init() {
		$adver = D('adver');
		$total = $adver->total();
		$page = new page($total, 5);
		$data = $adver->order('id DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('adver_list');
	}	
	


	/**
	 * 添加
	 */
	public function add() {

		if(isset($_POST['dosubmit'])) {
			$_POST['title'] = htmlspecialchars($_POST['title']);
			$_POST['inputtime'] = SYS_TIME;
			$_POST['code'] = $this->get_code($_POST);
			$_POST['start_time'] = !empty($_POST['start_time']) ? strtotime($_POST['start_time']) : 0;
			$_POST['end_time'] = !empty($_POST['end_time']) ? strtotime($_POST['end_time']) : 0;
			if(D('adver')->insert($_POST)){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>L('operation_failure')));
			}
		}else{
			include $this->admin_tpl('adver_add');
		}
	}


	
	/**
	 * 编辑
	 */
	public function edit() {
		
		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$_POST['title'] = htmlspecialchars($_POST['title']);
			$_POST['code'] = $this->get_code($_POST);
			$_POST['start_time'] = !empty($_POST['start_time']) ? strtotime($_POST['start_time']) : 0;
			$_POST['end_time'] = !empty($_POST['end_time']) ? strtotime($_POST['end_time']) : 0;
			if(D('adver')->update($_POST, array('id'=>$id))){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = D('adver')->where(array('id'=>$id))->find();
			include $this->admin_tpl('adver_edit');			
		}
	}

	
	/**
	 * 删除
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			if(D('adver')->delete($_POST['id'], true)){
				showmsg(L('operation_success'), '', 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}
	
	
	/**
	 * 组合广告值
	 */
	private function get_code($data) {
		if($data['type'] == 1){
			return '<a href="'.$data['url'].'" target="_blank" title="'.$data['title'].'">'.strip_tags($data['text']).'</a>';
		}
		
		if($data['type'] == 2){
			return $data['text'];
		}		
			
		return '<a href="'.$data['url'].'" target="_blank" title="'.$data['title'].'"><img src="'.safe_replace($data['img']).'"></a>';
	}

}