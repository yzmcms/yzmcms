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

class index{

	private $siteid,$siteinfo;
	
	public function __construct(){
		if(!get_config('is_link')) return_message(L('close_apply_link'), 0);
		$this->siteid = get_siteid();
		$this->siteinfo = array();
		if($this->siteid){
			$this->siteinfo = get_site($this->siteid);
			set_module_theme($this->siteinfo['site_theme']);
		}
	}

	/**
	 * 申请友情链接
	 */	
	public function init(){	
		$site = array_merge(get_config(), $this->siteinfo);

		list($seo_title, $keywords, $description) = get_site_seo($this->siteid, L('apply_link'), L('apply_link'));
		$location = '<a href="'.SITE_URL.'">首页</a> &gt; '.L('apply_link');
		include template('index', 'apply_link');
	}
	

	/**
	 * 添加友情链接
	 */
 	public function post() {
 		if(is_post()) {
			
			new_session_start();
			!isset($_POST['code']) && return_message(L('code_error'), 0);
			if(empty($_SESSION['code']) || strtolower($_POST['code'])!=$_SESSION['code']){
				$_SESSION['code'] = '';
				return_message(L('code_error'), 0);
			}
			$_SESSION['code'] = '';
			
			if($_POST['name']=='') return_message(L('lose_parameters'), 0);
 			if($_POST['url']=='' || !preg_match('/^(http|https)?:\/\/(.*)/i', $_POST['url'])){
 				return_message(L('lose_parameters'), 0);
 			}
			if($_POST['email']=='' || !is_email($_POST['email'])) return_message(L('mail_format_error'), 0);
			
			if(!preg_match('/^(http|https)?:\/\/(.*)/i', $_POST['logo']))  $_POST['logo'] = '';
					
			$data = array(
				'siteid' => $this->siteid,
				'name' => $_POST['name'],
				'url' => $_POST['url'],
				'logo' => $_POST['logo'],
				'linktype' => $_POST['logo'] ? 1 : 0,
				'username' => $_POST['username'],
				'email' => $_POST['email'],
				'msg' => $_POST['msg'],
				'addtime' => SYS_TIME,
			
			);										
			D('link')->insert($data, true);
			return_message(L('apply_link_success'));
		}
	}

}