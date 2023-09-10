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

class system_manage extends common {

	/**
	 * 配置信息
	 */
	public function init() {
		$data = get_config();
		$pc_theme_list = get_theme_list('index');
		$wap_theme_list = get_theme_list('mobile');
		include $this->admin_tpl('system_set');
	}
	

	/**
	 * 会员中心设置
	 */
	public function member_set() {
		$data = get_config();
		$member_theme_list = get_theme_list('member');
		include $this->admin_tpl('member_set', 'member');
	}
	
	
	
	/**
	 * 保存配置信息
	 */
	public function save() {
		yzm_base::load_common('function/function.php', 'admin');
		if(is_ajax()){
			$arr = array();
			$config = D('config');
			foreach($_POST as $key => $value){
				if(in_array($key, array('error_log_save','site_theme','watermark_enable','watermark_name','watermark_position'))) {
					$value = in_array($key, array('error_log_save','watermark_enable')) ? intval($value) : safe_replace(trim($value));
					$arr[$key] = $value;
				}else{
					if($key != 'site_code'){
						$value = htmlspecialchars(trim($value));
					}
				}
				$config->update(array('value'=>$value), array('name'=>$key));
			}
			set_config($arr);
			delcache('configs');
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}
	}		

	
	/**
	 * 屏蔽词管理
	 */
	public function prohibit_words() {
		if(isset($_POST['dosubmit'])){
			D('config')->update(array('value'=>$_POST['prohibit_words']), array('name'=>'prohibit_words'), true);
			delcache('configs');
			showmsg(L('operation_success'), '', 1);
		}
		include $this->admin_tpl('prohibit_words');
	}
	
	
	/*
	 * 测试邮件配置
	 */
	public function public_test_mail() {
		$config = D('config');
		foreach($_POST as $key => $value){
			if(in_array($key, array('mail_server','mail_port','mail_from','mail_auth','mail_user','mail_pass'))) {
				$config->update(array('value'=>trim($value)), array('name'=>$key), true);
			}
		}
		delcache('configs');

		if(sendmail($_POST['mail_to'], 'YzmCMS邮件测试', '这是一封测试邮件，如果您成功接收此邮件，说明您的邮件配置正确！')){
			return_json(array('status'=>1, 'message'=>'发送邮件成功！'));
		}else{
			return_json(array('status'=>0, 'message'=>'发送邮件失败！'));
		}	
	}
	
	
	/*
	 * 用户自定义配置列表
	 */
	public function user_config_list() {
		$config = D('config');
		$total = $config->where(array('type' => 99))->total();
		$page = new page($total, 15);
		$data = $config->where(array('type' => 99))->order('id DESC')->limit($page->limit())->select();	
		include $this->admin_tpl('user_config_list');
	}

	
	/*
	 * 用户自定义配置添加
	 */
	public function user_config_add() {
		if(isset($_POST['dosubmit'])){
			$config = D('config');
			$_POST['name'] = trim($_POST['name']);
			$_POST['value'] = trim($_POST['value']);
			$res = $config->where(array('name' => $_POST['name']))->find();
			if($res) return_json(array('status'=>0,'message'=>'配置名称已存在！'));
			if(empty($_POST['value']))  return_json(array('status'=>0,'message'=>'配置值不能为空！'));
			$_POST = new_html_special_chars($_POST);
			
			$_POST['type'] = 99;
			if(in_array($_POST['fieldtype'], array('select','radio'))){
			    $_POST['setting'] = array2string(explode('|', rtrim($_POST['setting'], '|')));
		    }else{
				$_POST['setting'] = '';
			}
			
			if($config->insert($_POST)){
				delcache('configs');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>L('data_not_modified')));
			}			
		}
		include $this->admin_tpl('user_config_add');
	}

	
	/*
	 * 用户自定义配置编辑
	 */
	public function user_config_edit() {
		if(isset($_POST['dosubmit'])) {
			$data = array();
			$data['title'] = trim($_POST['title']);
			$data['value'] = trim($_POST['value']);
			$data['status'] = intval($_POST['status']);
			
			if(D('config')->update($data, array('id' => intval($_POST['id'])), true)){
				delcache('configs');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
						
		} else {
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = D('config')->where(array('id' => $id))->find();
			$fieldtype = $data['fieldtype'] ? $data['fieldtype'] : 'textarea';
			include $this->admin_tpl('user_config_edit');			
		}
	}

	
	/*
	 * 用户自定义配置删除
	 */
	public function user_config_del() {
		if($_POST && is_array($_POST['id'])){
			if(D('config')->delete($_POST['id'], true)){
				delcache('configs');
				showmsg(L('operation_success'), '', 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}

	
	/*
	 * 用户自定义配置导出
	 */
	public function user_config_export() {
		$data = D('config')->where(array('type' => 99))->select();
		$res = array2string($data);
		header('Content-Disposition: attachment; filename="yzm_config.cfg"');
		echo $res;exit;
	}
	
	
	/*
	 * 用户自定义配置导入
	 */
	public function user_config_import() {
		if(isset($_POST['dosubmit'])) {
			$data_import = $_FILES['data_import']['tmp_name'];
			if(empty($data_import)) return_json(array('status'=>0,'message'=>'请上传文件！'));
			if(fileext($_FILES['data_import']['name']) != 'cfg') return_json(array('status'=>0,'message'=>'上传文件类型错误！'));
			$data_import = @file_get_contents($data_import);
			if(empty($data_import)) return_json(array('status'=>0,'message'=>'上传文件数据为空！'));
			$config_import_data = string2array($data_import);	
			if(!is_array($config_import_data))  return_json(array('status'=>0,'message'=>'解析文件数据错误！'));
			$config = D('config');
			foreach($config_import_data as $val){
				$name = $config->field('name')->where(array('name' => $val['name']))->one();
				$arr = array();
				$arr['type'] = intval($val['type']);
				$arr['title'] = htmlspecialchars($val['title']);
				$arr['value'] = htmlspecialchars($val['value']);
				$arr['fieldtype'] = htmlspecialchars($val['fieldtype']);
				$arr['setting'] = $val['setting'];
				$arr['status'] = intval($val['status']);
				if($name){
					$config->update($arr, array('name' => $name));
				}else{
					$arr['name'] = htmlspecialchars($val['name']);
					$config->insert($arr);
				}
			}
			delcache('configs');
			return_json(array('status'=>1,'message'=>'导入成功！'));
		}else{
			$title = '导入配置';
			$url = U('user_config_import');
			include $this->admin_tpl('data_import');	
		}
	}


	/**
	 * 启用禁用
	 */
	public function public_change_status() {
		if(is_post()){
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$value = isset($_POST['value']) ? intval($_POST['value']) : 0;
			
			if(D('config')->update(array('status'=>$value), array('id' => $id))){
				delcache('configs');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}
	}
	
	
	/*
	 * 根据字段类型获取html
	 */
	public function public_gethtml($ftype='', $val='', $setting='') {
		debug();
		yzm_base::load_sys_class('form','',0);
		
		$fieldtype = $ftype ? $ftype : (isset($_POST['fieldtype'])&&is_string($_POST['fieldtype']) ? safe_replace($_POST['fieldtype']) : 'textarea');

		if($fieldtype == 'textarea'){
				echo '<textarea name="value" class="textarea w_420"  placeholder="例如：214243830">'.$val.'</textarea>';
		}elseif(in_array($fieldtype, array('select', 'radio'))){
				if($val){
				echo form::$fieldtype('value', $val, string2array($setting));
				}else{
				echo '<textarea name="setting" class="textarea w_300"  placeholder="多个选项用“|”分开，如“男|女|未知”"></textarea> &nbsp;<input type="text" name="value" class="input" style="width:180px" placeholder="默认值">';
				}
		}elseif($fieldtype == 'image' || $fieldtype == 'attachment'){
				echo form::$fieldtype('value', $val);
		}else{
				echo '<textarea name="value" class="textarea w_420"  placeholder="例如：214243830">'.$val.'</textarea>';
		}
	}	
}