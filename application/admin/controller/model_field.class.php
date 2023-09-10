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
yzm_base::load_common('lib/sql'.EXT, 'admin');

class model_field extends common {
	
	public $modelid;
	public $modeltype;
	public $modeltable;
	public $modelname;
	public function __construct() {
		parent::__construct();
		$this->modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
		$this->public_set_modelinfo();
	}


	/**
	 * 字段列表
	 */
	public function init() {
		$modelid = $this->modelid;
		$model_field = D('model_field');

		// 0:内容模型,1:自定义表单模型,2:单页模型
		if($this->modeltype){
			$data = $model_field->where(array('modelid' => $this->modelid))->order('listorder ASC,fieldid ASC')->select();
		}else{
			$data = $model_field->where(array('modelid' => 0),array('modelid' => $this->modelid))->order('listorder ASC,fieldid ASC')->select();
		}
			
		include $this->admin_tpl('model_field_list');
	}

	
	/**
	 * 添加字段
	 */
	public function add() {

		if(isset($_POST['dosubmit'])) {
			
		   if(!preg_match('/^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,29}$/', $_POST['field'])) showmsg('字段名格式不正确！');
		   
		   $files = array('input','textarea','number','decimal','datetime','image','images','attachment','attachments','select','radio','checkbox','editor', 'editor_mini');
		   if(!in_array($_POST['fieldtype'], $files))  showmsg(L('illegal_parameters'), 'stop');
		   $_POST = new_html_special_chars($_POST);
		   
		   $_POST['issystem'] = 0;	
		   $_POST['modelid'] = $this->modelid;	
		   $_POST['listorder'] = 1;	
		   
		   if(in_array($_POST['fieldtype'], array('select','radio','checkbox'))){
			   $_POST['setting'] = array2string(explode('|', rtrim($_POST['setting'], '|')));
		   }elseif($_POST['fieldtype']=='datetime'){
			   $_POST['setting'] = $_POST['dateset'] ? '{"0":"1"}' : '';
		   }else{
			   unset($_POST['setting']);
		   }
		   
		   if(isset($_POST['setting_catid'])&&is_array($_POST['setting_catid'])) $_POST['setting_catid'] = join(',',$_POST['setting_catid']);
		   if($_POST['minlength']) $_POST['isrequired'] = 1;

		   if($_POST['fieldtype'] == 'input' || $_POST['fieldtype'] == 'datetime'){
		   		sql::sql_add_field($this->modeltable, $_POST['field']);  
		   }else if($_POST['fieldtype'] == 'textarea' || $_POST['fieldtype'] == 'images' || $_POST['fieldtype'] == 'attachments'){
				sql::sql_add_field_mediumtext($this->modeltable, $_POST['field']);  
		   }else if($_POST['fieldtype'] == 'editor' || $_POST['fieldtype'] == 'editor_mini'){
				sql::sql_add_field_text($this->modeltable, $_POST['field']);  
		   }else if($_POST['fieldtype'] == 'number'){
				sql::sql_add_field_int($this->modeltable, $_POST['field'], intval($_POST['defaultvalue'])); 
		   }else if($_POST['fieldtype'] == 'decimal'){
				sql::sql_add_field_decimal($this->modeltable, $_POST['field']); 
		   }else{
				sql::sql_add_field($this->modeltable, $_POST['field'], $_POST['defaultvalue'], $_POST['maxlength']);  
		   }

		   D('model_field')->insert($_POST); 
		   delcache($this->modelid.'_model');
		   showmsg(L('operation_success'), U('init',array('modelid'=>$this->modelid)), 1);
		}else{
			$modelid = $this->modelid;
			$modelname = $this->modelname;
			if($this->modeltype==2) $select_page = $this->_select_page();
			include $this->admin_tpl('model_field_add');
		}
	}
	
	
	/**
	 * 修改字段
	 */
	public function edit() {
		$fieldid = isset($_GET['fieldid']) ? intval($_GET['fieldid']) : 0;
		if(isset($_POST['dosubmit'])) {

			$_POST = new_html_special_chars($_POST);
			
			if(in_array($_POST['fieldtype'], array('select','radio','checkbox'))){
			   $_POST['setting'] = array2string(explode('|', rtrim($_POST['setting'], '|')));
			}elseif($_POST['fieldtype']=='datetime'){
			   $_POST['setting'] = $_POST['dateset'] ? '{"0":"1"}' : '';
			}else{
			   unset($_POST['setting']);
			}
		   	
			unset($_POST['issystem'], $_POST['modelid'], $_POST['fieldtype']);	
			
			if(isset($_POST['setting_catid'])&&is_array($_POST['setting_catid'])) $_POST['setting_catid'] = join(',',$_POST['setting_catid']);
		    $_POST['isrequired'] = $_POST['minlength'] ? 1 :0;
			if(D('model_field')->update($_POST, array('fieldid' => $fieldid))){
				delcache($this->modelid.'_model');
				showmsg(L('operation_success'), U('init',array('modelid'=>$this->modelid)), 1);
			}else{
				showmsg(L('data_not_modified'), U('init',array('modelid'=>$this->modelid)));
			}
		}else{
			$modelid = $this->modelid;
			$modelname = $this->modelname;
			$data = D('model_field')->where(array('fieldid'=>$fieldid))->find();
			if($this->modeltype==2) $select_page = $this->_select_page($data['setting_catid']);
			include $this->admin_tpl('model_field_edit');
		}
	}
	
	
	/**
	 * 删除字段
	 */
	public function delete() {
		$fieldid = isset($_GET['fieldid']) ? intval($_GET['fieldid']) : 0;
		$model_field = D('model_field'); 
		$data = $model_field->field('field,issystem')->where(array('fieldid' => $fieldid))->find();
		if(!$data['issystem']){
			$model_field->delete(array('fieldid' => $fieldid));
			sql::sql_del_field($this->modeltable, $data['field']);  
			delcache($this->modelid.'_model');
			showmsg(L('operation_success'), U('init',array('modelid'=>$this->modelid)), 1);
		}else{
			showmsg('不能删除系统字段！');
		}
	}
	
	
	/**
	 * 排序字段
	 */
	public function order() {
		if(isset($_POST['fieldid']) && is_array($_POST['fieldid'])){
			$model_field = D('model_field'); 
			foreach($_POST['fieldid'] as $key=>$val){
				$model_field->update(array('listorder'=>$_POST['listorder'][$key]),array('fieldid'=>intval($val)));
			}
			delcache(intval($_POST['modelid']).'_model');
		}
		showmsg(L('operation_success'), '' ,1);
	}


	/**
	 * ajax修改状态
	 */
	public function public_change_status() {
		if(is_post()){
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$value = isset($_POST['value']) ? intval($_POST['value']) : 0;
			$data = D('model_field')->field('modelid,issystem')->where(array('fieldid' => $id))->find();
			if($data['issystem']) return_json(array('status'=>0,'message'=>'系统字段不允许修改！'));

			$disabled = $value ? 0 : 1;
			if(D('model_field')->update(array('disabled' => $disabled), array('fieldid' => $id))){
				delcache($data['modelid'].'_model');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}
	}

	
	/**
	 * 获取模型信息
	 */
	public function public_set_modelinfo() {
		$data = D('model')->field('name,tablename,type')->where(array('modelid'=>$this->modelid))->find();
		if($data){
			$this->modeltype = $data['type'];
			$this->modelname = $data['name'];
			$this->modeltable = $data['tablename'];
		}else{
			return_message('模型不存在！', 0);
		}
	}

	
	/**
	 * 检查字段
	 */
	public function public_check_field() {
		$field = isset($_GET['field']) ? $_GET['field'] : '';
		if(!$field) exit('-1');  
		$model = D($this->modeltable);
		$fields = $model->get_fields();
		if(!in_array($field, $fields))
		   exit('1');  
		else
		   exit('0');  
	}


	/**
	 * 单页栏目select
	 */
	private function _select_page($value=0){
		$categorys = array();
		$html='<select name="setting_catid[]" class="select" multiple="true" style="min-width:300px;min-height:200px">';
		$html.='<option value="0" '.($value==0 ? 'selected' : '').'>≡ 应用到所有栏目 ≡</option>';

		$tree = yzm_base::load_sys_class('tree');
		$data = D('category')->field('catid AS id,catname AS name,parentid,arrparentid,arrchildid,type,modelid,member_publish')->where(array('siteid'=>get_siteid()))->order('listorder ASC,catid ASC')->select();

		$page_arr = array();
		foreach($data as $catinfo){
		    if($catinfo['type']!=1) continue;
		    $key = md5($catinfo['arrparentid']);
			$page_arr[$key] = isset($page_arr[$key]) ? $page_arr[$key].','.$catinfo['id'] : $catinfo['arrparentid'].','.$catinfo['id'];
		}
		$page_arr = array_unique(explode(',', join(',', $page_arr)));

		foreach($data as $val){
			if(!array_search($val['id'], $page_arr)) continue;
			$val['html_disabled'] = 0;
			if($val['type']!=1 && strpos($val['arrchildid'], ',')) $val['html_disabled'] = 1;
			$categorys[$val['id']] = $val;
		}
		$tree->init($categorys);
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$html .= $tree->get_tree_multi(0, "<option value='\$id' \$selected>\$spacer\$name</option>", "<optgroup label='\$spacer \$name'></optgroup>", $value);

		$html .= '</select>';
		return $html;
	}

}