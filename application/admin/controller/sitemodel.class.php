<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_common('lib/sql'.EXT, 'admin');

class sitemodel extends common {

	/**
	 * 模型列表
	 */
	public function init() {
		$data = D('model')->where(array('type' => 0))->order('modelid ASC')->select();
		include $this->admin_tpl('model_list');
	}
	
	
	/**
	 * 删除模型
	 */
	public function delete() {
		$modelid = intval($_GET['modelid']);
		if(in_array($modelid, array(1, 2, 3))) showmsg('不能删除系统模型！');		
		$model = D('model');		
		$r = $model->field('tablename')->where(array('modelid'=>$modelid))->find();
		if($r) sql::sql_delete($r['tablename']); 
		$model->delete(array('modelid'=>$modelid)); //删除model信息
		D('model_field')->delete(array('modelid'=>$modelid)); //删除字段
		delcache('modelinfo');
	
		showmsg(L('operation_success'));
	}
	
	/**
	 * 添加模型
	 */
	public function add() {		
		
		if(isset($_POST['dosubmit'])) { 
			if(!$_POST['name']) return_json(array('status'=>0,'message'=>'模型名称不能为空！'));
			$tablename = isset($_POST['tablename']) ? strip_tags($_POST['tablename']) : '';
			if(!$tablename) return_json(array('status'=>0,'message'=>'表名称不能为空！'));			
			$model = D('model');
			if($model->table_exists(C('db_prefix').$tablename)) return_json(array('status'=>0,'message'=>'表名已存在！'));	
			$_POST['issystem'] = $_POST['type'] = 0;
			$_POST['inputtime'] = SYS_TIME;
			$model->insert($_POST, true);
			sql::sql_create($tablename);
			delcache('modelinfo');
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{			
			include $this->admin_tpl('model_add');
		}
		
	}
	
	/**
	 * 编辑模型
	 */
	public function edit() {				
		$model = D('model');
		if(isset($_POST['dosubmit'])) {
			$modelid = isset($_POST['modelid']) ? intval($_POST['modelid']) : 0;
			if($model->update(array('name'=>$_POST["name"],'description'=>$_POST["description"],'disabled'=>$_POST["disabled"]), array('modelid' => $modelid))){
				delcache('modelinfo');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
			$data = $model->where(array('modelid' => $modelid))->find();
			include $this->admin_tpl('model_edit');
		}
		
	}	
	
}