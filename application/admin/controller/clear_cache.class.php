<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class clear_cache extends common {

	public function init() {
	}
	
	public function public_clear() {
		
		//清除模块模板缓存
		$chache_files = array('index', 'member');
		foreach ($chache_files as $files){
			$files = glob(YZMPHP_PATH.'cache/'.$files.'/*.tpl.php');
			foreach ($files as $v){
				@unlink($v);
			}
        }	
		
		//清除文件缓存
		delcache('',true);
		
		echo '<script type="text/javascript">function myclose(){var index = parent.layer.getFrameIndex(window.name); parent.layer.close(index);} setTimeout("myclose()",2500);</script>';
		showmsg('操作成功，3秒自动关闭');
	}
}