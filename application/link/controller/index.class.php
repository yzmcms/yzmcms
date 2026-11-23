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
			check_captcha();

			if(!rate_limit('apply_link', 3, 600)) {
			    return_message('操作太频繁了，请稍后重试！', 0);
			}

			if(!isset($_POST['name']) || !isset($_POST['url']) || !isset($_POST['email'])) return_message(L('lose_parameters'), 0);
			if($_POST['name']=='' || strlen($_POST['name'])>150) return_message(L('lose_parameters'), 0);
 			if($_POST['url']=='' || !preg_match('/^https?:\/\/(.*)/i', $_POST['url'])){
 				return_message(L('lose_parameters'), 0);
 			}
			if($_POST['email']=='' || !is_email($_POST['email'])) return_message(L('mail_format_error'), 0);

			$site_logo = isset($_POST['logo']) ? trim($_POST['logo']) : '';
			if(!preg_match('/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp|bmp)(\?.*)?$/i', $site_logo)) $site_logo = '';
					
			$data = array(
				'siteid' => $this->siteid,
				'name' => $_POST['name'],
				'url' => $_POST['url'],
				'logo' => $site_logo,
				'linktype' => $site_logo ? 1 : 0,
				'username' => isset($_POST['username']) ? str_cut($_POST['username'], 36) : '',
				'email' => $_POST['email'],
				'msg' => isset($_POST['msg']) ? trim($_POST['msg']) : '',
				'addtime' => SYS_TIME,
			);										
			D('link')->insert($data, true);
			return_message(L('apply_link_success'));
		}
	}

}