<?php
/**
 * YzmCMS内容管理系统
 * 商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * 功能定制QQ: 21423830
 * 版权所有 WWW.YZMCMS.COM
 */

defined('IN_YZMPHP') or exit('Access Denied'); 

class module_api {
	
	public $error_msg = '';
	private $installdir, $uninstalldir, $module;
	
	/**
	 * 模块安装
	 * @param string $module 模块名
	 */
	public function install($module) {
		if ($module) $this->module = $module;
		
		$this->installdir = APP_PATH.$this->module.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR;
		if(!$this->check()) return false;
		$model = include($this->installdir.'model.php');
		foreach ($model as $m) {
			$sql = file_get_contents($this->installdir.$m.'.sql');
			$this->sql_execute($sql);
		}
		
		if(is_file($this->installdir.'extention.inc.php')) {
			$menu = D('menu');
			include ($this->installdir.'extention.inc.php');
		}
		$arr = require $this->installdir.'config.inc.php';
		$arr['installdate'] = date('Y-m-d');
		D('module')->insert($arr, true, false);
		return true;
	}
	
	
	/**
	 * 模块卸载
	 * @param string $module 模块名
	 */
	public function uninstall($module) {
		if(!$module) {
			$this->error_msg = '模块名称为空！';
			return false;
		}
		$this->uninstalldir = APP_PATH.$module.DIRECTORY_SEPARATOR.'uninstall'.DIRECTORY_SEPARATOR;
		if(!is_dir($this->uninstalldir)) {
			$this->error_msg = '模块【'.$module.'】uninstall目录不存在！';
			return false;
		}
		if(!is_file($this->uninstalldir.'model.php')) {
			$this->error_msg = '模块【'.$module.'】model.php不存在！';
			return false;
		}
		$model = include($this->uninstalldir.'model.php');
		if(is_array($model) && !empty($model)) {
			$admin = D('admin');
			foreach($model as $m) {
				$admin->query('DROP TABLE IF EXISTS `yzmcms_'.$m.'`');
			}
		}	
		D('module')->delete(array('module' => $module));		
		if(is_file($this->uninstalldir.'extention.inc.php')) {
			$menu = D('menu');
			include ($this->uninstalldir.'extention.inc.php');
		}
		return true;
	}
	
	
	/**
	 * 检查安装目录
	 * @param string $module 模块名
	 */
	public function check($module = '') {
		if($module) $this->module = $module;
		if(!$this->module) {
			$this->error_msg = '模块名为空！';
			return false;
		}

		if(D('module')->where(array('module'=>$this->module))->find()) {
			$this->error_msg = '模块【'.$this->module.'】已经安装过！';
			return false;
		}
		
		if(!$this->installdir) {
			$this->installdir = APP_PATH.$this->module.DIRECTORY_SEPARATOR.'install'.DIRECTORY_SEPARATOR;
		}
		
		if(!is_dir($this->installdir)) {
			$this->error_msg = '模块【'.$this->module.'】install目录不存在！';
			return false;
		}
		
		if(!is_file($this->installdir.'config.inc.php')) {
			$this->error_msg = '模块【'.$this->module.'】config.inc.php文件不存在！';
			return false;
		}

		if(!is_file($this->installdir.'model.php')) {
			$this->error_msg = '模块【'.$this->module.'】model.php文件不存在！';
			return false;
		}

		$model = include($this->installdir.'model.php');
		if(is_array($model) && !empty($model)) {
			foreach ($model as $m) {
				if($m && !is_file($this->installdir.$m.'.sql')) {
					$this->error_msg = '模块【'.$this->module.'】'.$m.'.sql不存在！';
					return false;
				}
			}
		}		
		return true;
	}
	
	/**
	 * 执行sql文件，创建数据表等
	 * @param string $sql sql语句
	 */
	private function sql_execute($sql) {
	    $sqls = explode(";", trim($sql));;
		$sqls = array_filter($sqls);
		$admin = D('admin');
		foreach ($sqls as $sql){
			$str = substr($sql, 0, 1);
			if($str != '#' && $str != '-') $admin->query($sql);
		}
		return true;
	}
	
}