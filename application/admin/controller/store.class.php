<?php
/**
 * ！！！请勿修改或删除本文件！！！
 * YzmCMS应用中心 - 正在为您打造一款全能型建站系统
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * 功能定制QQ: 21423830
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2020-04-11
 */

defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class store extends common {
	
	
	/**
	 * init
	 */
	public function init(){
		$api_url = base64_decode('aHR0cDovL2FwaS55em1jbXMuY29tL2FwaS9zdG9yZS9pbml0');
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$data = array('auth_key'=>C('auth_key'), 'page'=>$page);
		if(isset($_GET['dosubmit'])){
			if($_GET['catid']){
				$data['catid'] = intval($_GET['catid']);
			}
			if($_GET['point'] != 99){
				$data['point'] = intval($_GET['point']);
			}
			if($_GET['system'] != 99){
				$data['system'] = intval($_GET['system']);
			}
		}
		$res = https_request($api_url, $data);
		$total = $res ? $res['total'] : 0;
		$data = $res ? $res['data'] : array();
		$page = new page($total, 15);
		include $this->admin_tpl('store_list');
	}


	/**
	 * 系统信息
	 */
	public function public_system_info(){

		include $this->admin_tpl('system_info');
	}
	

}