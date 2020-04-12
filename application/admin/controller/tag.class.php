<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class tag extends common {

	/**
	 * TAG列表
	 */
	public function init() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','total','inputtime')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$tag = D('tag');
		$total = $tag->total();
		$page = new page($total, 15);
		$data = $tag->order("$of $or")->limit($page->limit())->select();		
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
				$val = trim($val);
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
			$total = isset($_POST['total']) ? intval($_POST['total']) : 0;
			$tagv = trim($_POST['tag']);
			$remarks = trim($_POST['remarks']);
			$data = $tag->where(array('id!=' => $id, 'tag' => $tagv))->find();
			if($data) return_json(array('status'=>0,'message'=>'TAG标签重复，请修改名称！'));
			if($tag->update(array('tag' => $tagv, 'total' => $total, 'remarks'=>$remarks), array('id' => $id), true)){
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
			$tag = D('tag'); 
			$tag_content = D('tag_content'); 
			foreach($_POST['id'] as $id){
				$tag->delete(array('id' => $id)); 
				$tag_content->delete(array('tagid'=>$id));
			}
		}
		showmsg(L('operation_success'),'',1);
	}



	/**
	 * TAG标签选择
	 */
	public function public_select() {
		$where = array();
		if(isset($_GET['dosubmit'])){
			$where['tag'] = '%'.$_GET['searinfo'].'%';
		}
		$data = D('tag')->field('id,tag,total')->where($where)->order('id DESC')->select();
		include $this->admin_tpl('tag_select');
	}
	
}