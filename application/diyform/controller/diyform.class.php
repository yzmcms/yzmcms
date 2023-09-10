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
		$total = $model->where(array('type'=>1))->total();
		$page = new page($total, 15);
		$data = $model->where(array('type'=>1))->order('modelid DESC')->limit($page->limit())->select();
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