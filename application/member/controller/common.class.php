<?php
/**
 * 会员中心公共类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-15
 */
 
session_start();

class common{
	
	public $db, $userid, $memberinfo;
	
	public function __construct() {
		//ajax验证信息不需要登录
		if(substr(ROUTE_A, 0, 7) != 'public_') {
			self::check_member();
		}
		
		//设置会员模块模板风格
		set_module_theme('default');
	}

	
	/**
	 * 判断用户是否已经登录
	 */
	final private function check_member() {
		if(ROUTE_M =='member' && ROUTE_C =='index' && in_array(ROUTE_A, array('login', 'register'))) {
			if(isset($_SESSION['_userid']) && $_SESSION['_userid'] && $_SESSION['_userid']==intval(get_cookie('_userid'))){
				showmsg(L('login_success'), U('member/index/init'), 1);
			}
			return true;
		} else {
			$this->userid = $userid = intval(get_cookie('_userid'));
			if(!isset($_SESSION['_userid']) || !$_SESSION['_userid'] || $userid != $_SESSION['_userid']){
				
				showmsg(L('login_website'), U('member/index/login'), 1);
			}

			$this->db = D('member');
			$this->memberinfo = $this->db->where(array('userid'=>$userid))->find();
			
			//如果用户不存在或者不是正常状态，即停止
			if(empty($this->memberinfo) ||  $this->memberinfo['status'] != '1') showmsg('账号异常！', 'stop');
			
			$data = D('member_detail')->where(array('userid'=>$userid))->find();
			if(!$data) $data = array();
			
			$this->memberinfo = array_merge($this->memberinfo, $data);
			self::get_message();
		
		}
	}
	
	
	
	/**
	 * 获取消息数量
	 */	
	public function get_message(){ 
		//系统消息[群发]
		$system_totnum = D('message_group')->where(array('groupid' => $this->memberinfo['groupid']))->total(); //总条数
		$data = $this->db->fetch_array($this->db->query("SELECT COUNT(*) AS total FROM yzmcms_message_group a LEFT JOIN yzmcms_message_data b ON a.id=b.group_message_id WHERE a.groupid='{$this->memberinfo['groupid']}' AND a.`status`=1 AND b.userid={$this->memberinfo['userid']}"));  //已读信息
		$this->memberinfo['system_msg'] = $system_totnum - $data['total'];   //系统消息，未读条数
		
		//收件箱消息，未读条数
		$this->memberinfo['inbox_msg'] = D('message')->where(array('send_to' => $this->memberinfo['username'], 'isread' => '0', 'status' => '1'))->total(); 
	}

	
}
?>