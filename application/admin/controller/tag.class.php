<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class tag extends common {

	/**
	 * TAG列表
	 */
	public function init() {
		$tag = D('tag');
		$total = $tag->total();
		$page = new page($total, 10);
		$data = $tag->order('id DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('tag_list');
	}


	/**
	 * 添加TAG
	 */
	public function add() {
 		if(isset($_POST['dosubmit'])) {
			$tag = D('tag');
			$_POST['tags'] = str_replace('，', ',', strip_tags($_POST['tags']));
			$arr = array_unique(explode(',', $_POST['tags']));
			foreach($arr as $val){
				$tagid = $tag->where(array('tag' => $val))->find();
				if(!$tagid){
					$tag->insert(array('tag' => $val, 'total'=>0, 'inputtime'=>SYS_TIME), true);
				}
			}
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			include $this->admin_tpl('tag_add');
		}
	}
	
	
	/**
	 * 编辑TAG
	 */
 	public function edit() {
		$tag = D('tag');
		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		
			if($tag->update($_POST, array('id' => $id))){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = $tag->where(array('id' => $id))->find();
			include $this->admin_tpl('tag_edit');
		}

	}

	
	/**
	 * 删除多个TAG
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			if(D('tag')->delete($_POST['id'], true)){
				showmsg(L('operation_success'));
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}



	/**
	 * TAG标签选择
	 */
	public function select() {
		$where = array();
		if(isset($_GET['dosubmit'])){
			$where['tag'] = '%'.$_GET['searinfo'].'%';
		}
		$data = D('tag')->field('id,tag,total')->where($where)->order('id DESC')->select();
		include $this->admin_tpl('tag_select');
	}
	
}