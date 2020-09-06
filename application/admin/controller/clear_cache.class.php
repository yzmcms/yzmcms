<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class clear_cache extends common {

	public function init() {
	}
	
	public function public_clear() {
		
		//清除模块模板缓存
		$chache_files = array('index', 'mobile', 'member');
		foreach ($chache_files as $files){
			$files = glob(YZMPHP_PATH.'cache/'.$files.'/*.tpl.php');
			foreach ($files as $v){
				@unlink($v);
			}
        }	
		
		//清除文件缓存
		delcache('',true);
		
		return_json(array('status'=>1,'message'=>'系统缓存已清理完成.'));
	}
}