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