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

class config extends common{
	
	
    /**
     *  配置列表
     */	
	public function init(){
		$data = get_config();
		$modelinfo = get_site_modelinfo();
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