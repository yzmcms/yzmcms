<?php
/**
 * 会员中心公共类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-15
 */

class common{
	
	public $db, $userid, $memberinfo;
	
	public function __construct() {
		new_session_start();
		
		//ajax验证信息不需要登录
		if(substr(ROUTE_A, 0, 7) != 'public_') {
			self::check_member();
		}
		
		//设置会员模块模板风格
		set_module_theme(get_config('member_theme'));
	}

	
	/**
	 * 判断用户是否已经登录
	 */
	private function check_member() {
		if(ROUTE_M =='member' && ROUTE_C =='index' && in_array(ROUTE_A, array('login', 'register'))) {
			if(isset($_SESSION['_userid']) && $_SESSION['_userid'] && $_SESSION['_userid']==intval(get_cookie('_userid'))){
				$referer = isset($_GET['referer']) && !empty($_GET['referer']) ? urldecode($_GET['referer']) : U('member/index/init');
				is_ajax() && return_json(array('status'=>1, 'message'=>L('login_success'), 'url'=>$referer));
				showmsg(L('login_success'), $referer, 1);
			}
			return true;
		} else {
			$this->userid = intval(get_cookie('_userid'));
			if(!isset($_SESSION['_userid']) || !$_SESSION['_userid'] || $this->userid != $_SESSION['_userid']){
				$referer = isset($_GET['referer']) && !empty($_GET['referer']) && is_string($_GET['referer'])? urldecode($_GET['referer']) : '';
				is_ajax() && return_json(array('status'=>0, 'message'=>L('login_website'), 'url'=>url_referer(1, $referer)));
				showmsg(L('login_website'), url_referer(1, $referer), 1);
			}

			$this->db = D('member');
			$this->memberinfo = $this->db->where(array('userid'=>$this->userid))->find();
			
			//如果用户不存在或者不是正常状态，即停止
			if(empty($this->memberinfo) ||  $this->memberinfo['status'] != '1') return_message('账号异常！', 0);
			
			$data = D('member_detail')->where(array('userid'=>$this->userid))->find();
			if(!$data) $data = array();
			
			$this->memberinfo = array_merge($this->memberinfo, $data);
			is_get() && self::get_message();
		
		}
	}
	
	
	
	/**
	 * 获取消息数量
	 */	
	public function get_message(){ 
		//系统消息[群发]
		$system_totnum = D('message_group')->where(array('groupid' => $this->memberinfo['groupid']))->total(); //总条数

		$total = D('message_group')->alias('a')->join('yzmcms_message_data b ON a.id=b.group_message_id', 'LEFT')->where(array('a.groupid'=>$this->memberinfo['groupid'], 'a.status'=>1, 'b.userid'=>$this->memberinfo['userid']))->total();  //已读信息
		$this->memberinfo['system_msg'] = $system_totnum - $total;   //系统消息，未读条数
		
		//收件箱消息，未读条数
		$this->memberinfo['inbox_msg'] = D('message')->where(array('send_to' => $this->memberinfo['username'], 'status' => '1', 'isread' => '0'))->total(); 
	}

	

	/**
	 * 检查验证码
	 */	
	protected function _check_code($code){
		if(empty($_SESSION['code']) || strtolower($code)!=$_SESSION['code']){
			$_SESSION['code'] = '';
			is_ajax() ? return_json(array('status'=>-1, 'message'=>L('code_error'))) : showmsg(L('code_error'), '', 1);
		}
		$_SESSION['code'] = '';
	}
}