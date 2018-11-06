<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_common('lib/module_api'.EXT, 'admin');

class module extends common {
	
	private $module;
	
	public function init() {
		define('INSTALL', true);
		$dirs = $dirs_arr = array();
		$dirs = glob(APP_PATH.'*', GLOB_ONLYDIR);

		foreach ($dirs as $d) {
			$dirs_arr[] = basename($d);
		}
		
		$total = count($dirs_arr);
		$dirs_arr = array_chunk($dirs_arr, 10, true);
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$directory = isset($dirs_arr[($page-1)]) ? $dirs_arr[($page-1)] : array();
		yzm_base::load_sys_class('page','',0);
		$page = new page($total, 10);
		$data = D('module')->select();
		foreach($data as $val){
			$modules[$val['module']] = $val;
		}

		include $this->admin_tpl('module_list');
	}

	
	/**
	 * 模块安装
	 */
	public function install() {
		define('INSTALL', true);
		$this->module = isset($_POST['module']) ? $_POST['module'] : $_GET['module'];
		$module_api = new module_api();
		if(isset($_POST['dosubmit'])) {
			if($module_api->install($this->module)){
				delcache('menu_string_1');
				showmsg('安装成功！', U('cache'));
			}else{
				showmsg($module_api->error_msg, U('init'));
			}
		}else{
			if (!$module_api->check($this->module)) showmsg($module_api->error_msg, U('init'), 10);
			$config = include APP_PATH.$this->module.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'config.inc.php';
			if(!is_array($config)){
				showmsg('配置文件错误！', U('init'), 10);
			}
			extract($config);
			include $this->admin_tpl('module_config');			
		}
	}
	
	
	/**
	 * 模块卸载
	 */
	public function uninstall() {
		define('UNINSTALL', true);
		if(!isset($_GET['module']) || empty($_GET['module'])) showmsg('模块名称为空！');
		$module_api = new module_api();
		if ($module_api->uninstall($_GET['module'])){
			delcache('menu_string_1');
			showmsg('卸载成功！', U('init'));
		}else{
			showmsg($module_api->error_msg, U('init'));
		}
	}
	
	
	/**
	 * 更新模块缓存
	 */
	public function cache() {
		echo '<script type="text/javascript">parent.location.reload();</script>';
	}
}
?>