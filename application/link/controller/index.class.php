<?php
defined('IN_YZMPHP') or exit('Access Denied'); 

class index{
	
	public function __construct(){
		if(!get_config('is_link')) showmsg(L('close_apply_link'), 'stop');
	}

	/**
	 * 申请友情链接
	 */	
	public function init(){	
		$site = get_config();
		$seo_title = L('apply_link').'_'.$site['site_name'];
		$keywords = L('apply_link').','.$site['site_keyword'];
		$description = $site['site_description'];
		$location = '<a href="'.SITE_URL.'">首页</a> &gt; '.L('apply_link');
		include template('index', 'apply_link');
	}
	

	/**
	 * 添加友情链接
	 */
 	public function post() {
 		if(isset($_POST['dosubmit'])) {
			
			session_start();
			if(empty($_SESSION['code']) || strtolower($_POST['code'])!=$_SESSION['code']){
				$_SESSION['code'] = '';
				showmsg(L('code_error'));
			}
			$_SESSION['code'] = '';
			
			if($_POST['name']=='') showmsg(L('lose_parameters'), 'stop');
 			if($_POST['url']=='' || !preg_match('/^(http|https)?:\/\/(.*)/i', $_POST['url'])){
 				showmsg(L('lose_parameters'), 'stop');
 			}
			if($_POST['email']=='' || !is_email($_POST['email'])) showmsg(L('mail_format_error'), 'stop');
			
			if(!preg_match('/^(http|https)?:\/\/(.*)/i', $_POST['logo']))  $_POST['logo'] = '';
					
			$data = array(
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
			showmsg(L('apply_link_success'));
		}
	}

}