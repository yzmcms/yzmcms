<?php
/**
 * 后台管理中心 - 自定义表单管理
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-01-13
 */
 
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_common('lib/sql'.EXT, ROUTE_M);

class diyform extends common{
	
	
	/**
	 * 自定义表单列表
	 */	
	public function init(){	
		yzm_base::load_sys_class('page','',0);
		$model = D('model');
		$total = $model->where(array('siteid'=>self::$siteid,'type'=>1))->total();
		$page = new page($total, 15);
		$data = $model->where(array('siteid'=>self::$siteid,'type'=>1))->order('modelid DESC')->limit($page->limit())->select();
		$tables = $model->list_tables();
		foreach($data as $key => $val){
			$val['lasttime'] = in_array(C('db_prefix').$val['tablename'], $tables) ? D($val['tablename'])->field('inputtime')->order('id DESC')->one() : 0;
			$data[$key] = $val;
		}
		include $this->admin_tpl('diyform_list');
	}
	
	
	
	/**
	 * 添加自定义表单
	 */
 	public function add() {
 		if(isset($_POST['dosubmit'])) {
			if(!$_POST['name']) return_json(array('status'=>0,'message'=>'表单名称不能为空！'));
			$tablename = isset($_POST['tablename']) ? strip_tags($_POST['tablename']) : '';
			if(!$tablename) return_json(array('status'=>0,'message'=>'表名称不能为空！'));
			if(!preg_match('/^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,29}$/', $tablename)) return_json(array('status'=>0,'message'=>'表名格式不正确！'));
			$model = D('model');
			if($model->table_exists($tablename)) return_json(array('status'=>0,'message'=>'表名已存在！'));	
			$_POST = new_html_special_chars($_POST);
			$_POST['setting'] = json_encode(array('show_template'=>$_POST['show_template'], 
			'check_code'=> intval($_POST['check_code']), 'sendmail'=> intval($_POST['sendmail']), 'allowvisitor'=>intval($_POST['allowvisitor'])));
			$_POST['type'] = 1;
			$_POST['inputtime'] = SYS_TIME;
			$_POST['siteid'] = self::$siteid;
			$model->insert($_POST);
			sql::sql_create($tablename);
			$this->_del_cache();
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			$show_temp = $this->select_template('show_temp', 'show_');
			$data = array('show_template'=>'show_diyform');
			include $this->admin_tpl('diyform_add');
		}

	}
	
	
	/**
	 * 编辑自定义表单
	 */
 	public function edit() {
		$model = D('model');
		if(isset($_POST['dosubmit'])) {
			$modelid = isset($_POST['modelid']) ? intval($_POST['modelid']) : 0;
			$_POST = new_html_special_chars($_POST);
			$data = array('name'=>$_POST["name"],
				'description'=>$_POST["description"],
				'disabled'=>$_POST["disabled"],
				'setting'=>json_encode(array('show_template'=>$_POST['show_template'], 'check_code'=> intval($_POST['check_code']), 'sendmail'=> intval($_POST['sendmail']), 'allowvisitor'=>intval($_POST['allowvisitor'])))
			);
			if($model->update($data, array('modelid' => $modelid))){
				$this->_del_cache($modelid);
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
			$data = $model->where(array('modelid' => $modelid))->find();
			$setting = json_decode($data['setting'], true);
			$data = array_merge($data, $setting);
			$show_temp = $this->select_template('show_temp', 'show_');
			include $this->admin_tpl('diyform_edit');
		}

	}

	
	/**
	 * 删除自定义表单
	 */
	public function del() {
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
		
		$model = D('model');
		$r = $model->field('tablename,issystem')->where(array('modelid'=>$modelid))->find();
		if(!$r) return_json(array('status'=>0,'message'=>'该模型不存在！'));
		if($r['issystem']) return_json(array('status'=>0,'message'=>'不能删除系统模型！'));
		sql::sql_delete($r['tablename']); 
		
		$model->delete(array('modelid'=>$modelid)); 			//删除model信息
		D('model_field')->delete(array('modelid'=>$modelid)); 	//删除字段
		$this->_del_cache($modelid);
	
		return_json(array('status'=>1,'message'=>L('operation_success')));
	}


	/*
	 * 自定义表单导出
	 */
	public function export() {
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
		$data = D('model')->where(array('modelid' => $modelid))->find();
		if(!$data) showmsg('参数错误！', 'stop');
		$arr['model_data'] = $data;
		$field_data = D('model_field')->where(array('modelid' => $modelid))->select();
		$arr['model_field_data'] = $field_data;
		$res = array2string($arr);
		header('Content-Disposition: attachment; filename="yzm_'.$data['tablename'].'.diyform"');
		echo $res;exit;
	}


	/*
	 * 自定义表单导入
	 */
	public function import() {
		if(isset($_POST['dosubmit'])) {
			$data_import = $_FILES['data_import']['tmp_name'];
			if(empty($data_import)) return_json(array('status'=>0,'message'=>'请上传文件！'));
			if(fileext($_FILES['data_import']['name']) != 'diyform') return_json(array('status'=>0,'message'=>'文件类型需为【diyform】'));
			$data_import = @file_get_contents($data_import);
			if(empty($data_import)) return_json(array('status'=>0,'message'=>'上传文件数据为空！'));
			$model_import_data = string2array($data_import);
			if(!is_array($model_import_data))  return_json(array('status'=>0,'message'=>'解析文件数据错误！'));
			$model_data = $model_import_data['model_data'];

			$modelid = D('model')->field('modelid')->where(array('tablename' => $model_data['tablename']))->one();
			$model_arr = array();
			$model_arr['siteid'] = self::$siteid;
			$model_arr['name'] = htmlspecialchars($model_data['name']);
			$model_arr['alias'] = htmlspecialchars($model_data['alias']);
			$model_arr['description'] = htmlspecialchars($model_data['description']);
			$model_arr['setting'] = $model_data['setting'];
			$model_arr['inputtime'] = intval($model_data['inputtime']);
			$model_arr['disabled'] = intval($model_data['disabled']);
			$model_arr['type'] = intval($model_data['type']);
			$model_arr['sort'] = intval($model_data['sort']);
			$model_arr['issystem'] = intval($model_data['issystem']);	
			$model_arr['isdefault'] = 0;	

			//更新模型
			if($modelid){
				D('model')->update($model_arr, array('modelid' => $modelid));
			}else{
				$model_arr['tablename'] = htmlspecialchars($model_data['tablename']);
				sql::sql_create($model_data['tablename']);
				$modelid = D('model')->insert($model_arr);
			}
			
			//更新模型字段
			$model_field = D('model_field');
			$model_field_data = $model_import_data['model_field_data'];
			foreach($model_field_data as $val){
				$fieldid = $model_field->field('fieldid')->where(array('modelid' => $modelid, 'field' => $val['field']))->one();
				$arr = array();
				$arr['modelid'] = $modelid;
				$arr['name'] = htmlspecialchars($val['name']);
				$arr['tips'] = htmlspecialchars($val['tips']);
				$arr['minlength'] = intval($val['minlength']);
				$arr['maxlength'] = intval($val['maxlength']);
				$arr['errortips'] = htmlspecialchars($val['errortips']);
				$arr['fieldtype'] = htmlspecialchars($val['fieldtype']);
				$arr['defaultvalue'] = htmlspecialchars($val['defaultvalue']);
				$arr['setting'] = $val['setting'];
				$arr['isrequired'] = intval($val['isrequired']);
				$arr['issystem'] = intval($val['issystem']);
				$arr['isunique'] = intval($val['isunique']);
				$arr['isadd'] = intval($val['isadd']);
				$arr['listorder'] = intval($val['listorder']);
				$arr['disabled'] = intval($val['disabled']);
				$arr['type'] = intval($val['type']);
				$arr['status'] = intval($val['status']);
				if($fieldid){
					$model_field->update($arr, array('fieldid' => $fieldid));
				}else{
					$arr['field'] = htmlspecialchars($val['field']);
					$model_field->insert($arr);
					$this->_add_field($arr, $model_data['tablename']);
				}
			}			
			
			$this->_del_cache($modelid);
			return_json(array('status'=>1,'message'=>'导入成功！'));
		}else{
			$title = '导入表单';
			$url = U('import');
			include $this->admin_tpl('data_import', 'admin');	
		}
	}


	/**
	 * 禁用启用
	 */
	public function public_change_status() {
		if(is_post()){
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$value = isset($_POST['value']) ? intval($_POST['value']) : 0;
			$value = $value ? 0 : 1;

			$data = D('model')->field('isdefault,type')->where(array('modelid' => $id))->find();
			if($value && $data['isdefault']) return_json(array('status'=>0,'message'=>'默认模型不可以禁用！'));
			if($value && $data['type']==2) return_json(array('status'=>0,'message'=>'单页模型不可以禁用！'));
			
			if(D('model')->update(array('disabled'=>$value), array('modelid' => $id))){
				$this->_del_cache();
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}
	}
	
	
	/**
	 * 模板选择
	 * 
	 * @param $style  风格
	 * @param $pre 模板前缀
	 */
	private function select_template($style, $pre = '') {
			$site_theme = self::$siteid ? get_site(self::$siteid, 'site_theme') : C('site_theme');
			$files = glob(APP_PATH.'index'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$site_theme.DIRECTORY_SEPARATOR.$pre.'*.html');
			$files = @array_map('basename', $files);
			$templates = array();
			if(is_array($files)) {
				foreach($files as $file) {
					$key = substr($file, 0, -5);
					$templates[$key] = $file;
				}
			}
			
			$tem_style = APP_PATH.'index'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$site_theme.DIRECTORY_SEPARATOR.'config.php';
			if(is_file($tem_style)){
				$templets = require($tem_style);			
				return array_merge($templates, $templets[$style]);
			}else{
				return $templates;
			}
			
	}

	/*
	 * 添加模型字段
	 */	
	private function _add_field($data, $table){
		if($data['fieldtype'] == 'input' || $data['fieldtype'] == 'datetime'){
			sql::sql_add_field($table, $data['field']);  
		}else if($data['fieldtype'] == 'textarea' || $data['fieldtype'] == 'images' || $data['fieldtype'] == 'attachments'){
			sql::sql_add_field_mediumtext($table, $data['field']);  
		}else if($data['fieldtype'] == 'editor' || $data['fieldtype'] == 'editor_mini'){
			sql::sql_add_field_text($table, $data['field']);  
		}else if($data['fieldtype'] == 'number'){
			sql::sql_add_field_int($table, $data['field'], intval($data['defaultvalue'])); 
		}else if($data['fieldtype'] == 'decimal'){
			sql::sql_add_field_decimal($table, $data['field']); 
		}else{
			sql::sql_add_field($table, $data['field'], $data['defaultvalue'], $data['maxlength']);  
		}		
	}


	/**
	 * 清除缓存
	 */
	private function _del_cache($modelid=0){
		delcache('modelinfo');
		delcache('modelinfo_all');
		delcache('modelinfo_siteid_'.self::$siteid);
		$modelid && delcache($modelid.'_model');
	}

}