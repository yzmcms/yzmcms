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
		$dirs_arr = array_chunk($dirs_arr, 15, true);
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$directory = isset($dirs_arr[($page-1)]) ? $dirs_arr[($page-1)] : array();
		yzm_base::load_sys_class('page','',0);
		$page = new page($total, 15);
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
		if(is_post()) {
			if($module_api->install($this->module)){
				delcache('menu_string_1');
				return_json(array('status'=>1,'message'=>'安装成功！'));
			}else{
				return_json(array('status'=>0,'message'=>$module_api->error_msg));
			}
		}

		if (!$module_api->check($this->module)) showmsg($module_api->error_msg, 'stop');
		$config = include APP_PATH.$this->module.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR.'config.inc.php';
		if(!is_array($config)){
			showmsg('配置文件读取错误！', 'stop');
		}
		extract($config);
		include $this->admin_tpl('module_config');
	}
	
	
	/**
	 * 模块卸载
	 */
	public function uninstall() {
		if(!isset($_GET['yzm_csrf_token']) || !check_token($_GET['yzm_csrf_token'])) return_message(L('token_error'), 0);
		if(!isset($_GET['module']) || empty($_GET['module'])) return_json(array('status'=>0,'message'=>'模块名称为空！'));
		define('UNINSTALL', true);
		$module_api = new module_api();
		if ($module_api->uninstall($_GET['module'])){
			delcache('menu_string_1');
			return_json(array('status'=>1,'message'=>'卸载成功！'));
		}else{
			return_json(array('status'=>0,'message'=>$module_api->error_msg));
		}
	}
}
?>