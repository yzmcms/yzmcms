<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class config extends common{
	
	
    /**
     *  配置列表
     */	
	public function init(){
		$data = get_config();
		$modelinfo = get_modelinfo();
		include $this->admin_tpl('wechat_config');
    }
	
	
    /**
     *  保存配置
     */	
	public function save(){
		if(isset($_POST['dosubmit'])){
			$config = D('config');
			foreach($_POST as $key => $value){
				if(in_array($key, array('wx_appid','wx_secret','wx_token','wx_encodingaeskey','wx_relation_model'))) {
					$value = safe_replace(trim($value));
					$config->update(array('value'=>$value), array('name'=>$key));
				}
			}
			delcache('configs');
			showmsg(L('operation_success'), '', 1);
		}
    }

}