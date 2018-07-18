<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class system_manage extends common {

	/**
	 * 配置信息
	 */
	public function init() {
		$data = get_config();
		$theme_list = get_theme_list();
		include $this->admin_tpl('system_set');
	}
	

	/**
	 * 会员中心设置
	 */
	public function member_set() {
		$data = get_config();
		include $this->admin_tpl('member_set', 'member');
	}
	
	
	
	/**
	 * 保存配置信息
	 */
	public function save() {
		yzm_base::load_common('function/function.php', 'admin');
		if(isset($_POST['dosubmit'])){
			if(isset($_POST['mail_inbox']) && $_POST['mail_inbox']){
				if(!is_email($_POST['mail_inbox'])) showmsg(L('mail_format_error'));
			}
			if(isset($_POST['upload_types'])){
				$arr = explode('|', $_POST['upload_types']);
				$allow = array('gif', 'jpg', 'png', 'jpeg','zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf','mp4', 'avi', 'wmv', 'rmvb', 'flv','mp3', 'wma', 'wav', 'amr', 'ogg');
				foreach($arr as $key => $val){
					if(!in_array($val, $allow)) unset($arr[$key]);
				}
				$_POST['upload_types'] = join('|', $arr);
				if(empty($_POST['upload_types'])) showmsg('允许上传附件类型不能为空！');
			}
			$arr = array();
			$config = D('config');
			foreach($_POST as $key => $value){
				if(in_array($key, array('site_theme','watermark_enable','watermark_name','watermark_position'))) {
					$value = safe_replace(trim($value));
					$arr[$key] = $value;
				}else{
					if($key!='site_code'){
						$value = htmlspecialchars($value);
					}
				}
				$config->update(array('value'=>$value), array('name'=>$key));
			}
			set_config($arr);
			delcache('configs');
			showmsg(L('operation_success'), '', 1);
		}
	}		

	
	/**
	 * 屏蔽词管理
	 */
	public function prohibit_words() {
		if(isset($_POST['dosubmit'])){
			D('config')->update(array('value'=>$_POST['prohibit_words']), array('name'=>'prohibit_words'), true);
			delcache('configs');
			showmsg(L('operation_success'));
		}
		include $this->admin_tpl('prohibit_words');
	}
	
	
	/*
	 * 测试邮件配置
	 */
	public function public_test_mail() {
		if(sendmail($_POST['mail_to'], 'YzmCMS邮件测试', '这是一封测试邮件，如果您成功接收此邮件，说明您的邮件配置正确！')){
			exit('发送邮件成功！');
		}else{
			exit('发送邮件失败！');
		}	
	}
	
	
	/*
	 * 用户自定义配置列表
	 */
	public function user_config_list() {
		$config = D('config');
		$total = $config->where(array('type' => 99))->total();
		$page = new page($total, 10);
		$data = $config->where(array('type' => 99))->order('id DESC')->limit($page->limit())->select();	
		include $this->admin_tpl('user_config_list');
	}

	
	/*
	 * 用户自定义配置添加
	 */
	public function user_config_add() {
		if(isset($_POST['dosubmit'])){
			$config = D('config');
			$res = $config->where(array('name' => $_POST['name']))->find();
			if($res) return_json(array('status'=>0,'message'=>'配置名称已存在！'));
			if(empty($_POST['value']))  return_json(array('status'=>0,'message'=>'配置值不能为空！'));
			
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
			$data['title'] = $_POST['title'];
			$data['value'] = $_POST['value'];
			$data['status'] = $_POST['status'];
			
			if(D('config')->update($data, array('id' => intval($_POST['id'])))){
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
				showmsg(L('operation_success'));
			}else{
				showmsg(L('operation_failure'));
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
				echo '<textarea name="setting" class="textarea w_300"  placeholder="选项用“|”分开，如“男|女|人妖”"></textarea> &nbsp;<input type="text" name="value" class="input" style="width:180px" placeholder="默认值">';
				}
		}elseif($fieldtype == 'image' || $fieldtype == 'attachment'){
				echo form::$fieldtype('value', $val);
		}else{
				echo '<textarea name="value" class="textarea w_420"  placeholder="例如：214243830">'.$val.'</textarea>';
		}
	}	
}