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

class index{

	private $siteid,$siteinfo,$ip,$module;
	
	public function __construct(){
		$ismobile = ismobile() ? true : false;
		$this->siteid = get_siteid();
		$this->siteinfo = array();
		$this->module = 'index';
		if($this->siteid){
			$this->siteinfo = get_site($this->siteid);
			if($ismobile && $this->siteinfo['site_wap_theme']){
				$this->module = 'mobile';
				set_module_theme($this->siteinfo['site_wap_theme']);
			}else{
				set_module_theme($this->siteinfo['site_theme']);
			}
		}else{
			if($ismobile && get_config('site_wap_open')) {
				$this->module = 'mobile';
				set_module_theme(get_config('site_wap_theme'));
			}
		}
	}
	
	/**
	 * 站点留言
	 */
	public function init(){	
		new_session_start();
		if(!get_config('is_words')) return_message('管理员已关闭留言功能！', 0);
		
 		if(is_post()) {
			if(get_config('words_code')){
				!isset($_POST['code']) && return_message(L('code_error'), 0);
				if(empty($_SESSION['code']) || strtolower($_POST['code'])!=$_SESSION['code']){
					$_SESSION['code'] = '';
					return_message(L('code_error'), 0);
				}
				$_SESSION['code'] = '';
			}

			$this->_check($_POST);
			
			$_POST['siteid'] = $this->siteid;
			$_POST['booktime'] = SYS_TIME;
			$_POST['ip'] = $this->ip;
			$_POST['ispc'] = ismobile() ? 0 : 1;
			$_POST['ischeck'] = $_POST['isread'] = $_POST['replyid'] = 0;
			D('guestbook')->insert($_POST, true);
			
			//发送邮件通知
			$this->_sendmail($_POST);
			
			return_message('留言成功，请耐心等待管理员审核！');
		}else{
			$site = array_merge(get_config(), $this->siteinfo);

			list($seo_title, $keywords, $description) = get_site_seo($this->siteid, '留言反馈');
			include template($this->module,'guestbook');			
		}
	}
	
	
	/**
	 * 检查内容是否合法
	 */
	private function _check($data){
		if(empty($data['title']) || empty($data['bookmsg']) || empty($data['name'])){
			return_message('留言必填项不能为空！', 0);
		}

		// IP黑名单验证
		$this->ip = getip();
		$blacklist_ip = get_config('blacklist_ip');
		if($blacklist_ip){
			$arr = explode(',', $blacklist_ip);
			foreach($arr as $val){
				if(check_ip_matching($val, $this->ip)) return_message('IP黑名单用户，禁止访问！', 0);
			}
		}

		// 开启重复验证
		$res = D('guestbook')->field('title,name,bookmsg')->order('id DESC')->find();
		if($res && $data['title']==$res['title'] && $data['bookmsg']==$res['bookmsg']){
			return_message('请勿重复提交！', 0);
		}

		// 开启中文验证
		// if(get_config('is_words_chinese')){
		// 	if(!preg_match("/([\x{4e00}-\x{9fa5}]+)/u", $data['title']) || !preg_match("/([\x{4e00}-\x{9fa5}]+)/u", $data['bookmsg'])){
		// 		return_message('请填写有意义的内容！', 0);
		// 	}
		// }
		
		return true;
	}


	/**
	 * 发送邮件通知
	 */
	private function _sendmail($data){
		$content = strip_tags($data['bookmsg']);
		$site_url = get_config('site_url');
		$html = <<<EOF
	<div style="border:1px solid #dee0e5;color:#676767;width:600px;margin:0 auto;">
        <div style="height:60px;background:#4d5058;line-height:60px;color:#fff;font-size:18px;padding-left:10px;text-align:center;">新留言信息</div>
        <div style="padding:25px;word-wrap:break-word">
            <div>留言内容：</div>
            <div style="padding:10px;background:#f2f2f2;margin:10px 0">$content</div>
            <a href="$site_url" target="_blank" style="color:#22aaff;">点击查看详情</a>
        </div>
        <div style="background:#fafafa;color:#b4b4b4;text-align:center;line-height:45px;height:45px;">系统邮件，请勿直接回复</div>
    </div>
EOF;
		sendmail(get_config('mail_inbox'), '您的网站有新留言', $html);
	}

}