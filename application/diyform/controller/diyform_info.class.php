<?php
/**
 * 后台管理中心 - 自定义表单信息管理
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-01-14
 */
 
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class diyform_info extends common{
	
	private $modelid, $modeltable, $modelname;
	public function __construct() {
		parent::__construct();
		$this->modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : (isset($_POST['modelid']) ? intval($_POST['modelid']) : 0);
		$this->public_set_modelinfo();
	}	
	
	
	/**
	 * 用户提交表单信息列表
	 */	
	public function init(){
		$modelid = $this->modelid;
		yzm_base::load_sys_class('page','',0);
		$db = D($this->modeltable);
		$total = $db->total();
		$page = new page($total, 15);
		$data = $db->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('diyform_info_list');
	}
	
	
	/**
	 * 查看
	 */
 	public function view() {
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$db = D($this->modeltable);
		$model_data =  D('model_field')->field('field,name,fieldtype')->where(array('modelid'=>$this->modelid))->order('listorder ASC, fieldid ASC')->select();
		$data = $db->where(array('id' => $id))->find();
		if(!$data) showmsg(L('illegal_parameters'), 'stop');
		include $this->admin_tpl('diyform_info_view');
	}

	
	/**
	 * 删除
	 */
	public function del() {
		$db = D($this->modeltable);
		$model = D('model');
		foreach ($_POST['id'] as $v) {
			$db->delete(array('id'=>intval($v)));
			$model->update('`items` = `items`-1', array('modelid'=>$this->modelid));
		}
	
		showmsg(L('operation_success'), U('init', array('modelid'=>$this->modelid)), 1);
	}
	
	
	/**
	 * 获取模型信息
	 */
	private function public_set_modelinfo() {
		$data = D('model')->field('name,tablename')->where(array('modelid'=>$this->modelid))->find();
		if($data){
			$this->modelname = $data['name'];
			$this->modeltable = $data['tablename'];
		}else{
			showmsg('模型不存在！', 'stop');
		}
	}

}