<?php
/**
 * ！！！请勿修改或删除本文件！！！
 * YzmCMS应用中心 - 正在为您打造一款全能型建站系统
 * 如果您有能力，请购买授权支持YzmCMS开发，如果您实在是不想购买，请务必保留YzmCMS版权信息！！！
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
		$api_url = base64_decode('aHR0cHM6Ly93d3cueXptY21zLmNvbS9hcGkvc3RvcmUvaW5pdA==');
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
	

}