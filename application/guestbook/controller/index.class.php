<?php
class index{
	
	public function init(){	
		session_start();
 		if(isset($_POST['dosubmit'])) {
			if(!get_config('is_words')) showmsg("管理员已关闭留言功能！");
			if(empty($_SESSION['code']) || strtolower($_POST['code'])!=$_SESSION['code']){
				$_SESSION['code'] = '';
				showmsg(L('code_error'));
			}
			$_SESSION['code'] = '';
			if($_POST['bookmsg'] == '' || $_POST['name'] == '' || $_POST['title'] == '') showmsg('留言主题，留言人，留言内容不能为空！');
			
			$_POST['booktime'] = SYS_TIME;
			$_POST['ip'] = getip();
			$_POST['ispc'] = ismobile() ? 0 : 1;
			$_POST['ischeck'] = $_POST['isread'] = $_POST['replyid'] = 0;
			D('guestbook')->insert($_POST, true);
			
			//发送邮件通知
			$this->_sendmail($_POST);
			
			showmsg('留言成功，请耐心等待管理员审核！');
		}else{
			$site = get_config();
			$seo_title = '留言反馈_'.$site['site_name'];
			$keywords = $site['site_keyword'];
			$description = $site['site_description'];
			include template('index','guestbook');			
		}
	}
	
	
	/**
	 *发送邮件通知
	 */
	private function _sendmail($data){
		$title = strip_tags($data['title']);
		$name = strip_tags($data['name']);
		$content = strip_tags($data['bookmsg']);
		$html = '';
		$html .= '<p><b>留言标题：</b>'.$title.'</p>';
		$html .= '<p style="margin-top:-10px"><b>联系人：</b>'.$name.'</p>';
		$html .= '<p style="margin-top:-10px"><b>内容：</b></p>';
		$html .= '<p style="margin-top:-10px">'.$content.'</p>';
		$html .= '<p style="margin-top:-10px"><a href="'.get_config('site_url').'" target="_blank">点击查看详情</a></p>';
		$html .= '<p style="margin-top:20px">'.get_config('site_name').'</p>';
		sendmail(get_config('mail_inbox'), '您的网站有新留言', $html);
	}

}