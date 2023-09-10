<?php
/**
 * 会员中心消息操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-15
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'member', 0);
yzm_base::load_sys_class('page','',0);

class messages extends common{
	
	public function __construct() {
		parent::__construct();
	}

	
	/**
	 * 会员中心
	 */	
	public function init(){ 

	}
	
	
	/**
	 * 会员中心 - 发送消息
	 */	
	public function new_messages(){ 
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		$groupinfo = get_groupinfo($groupid);
			
		//判断当前会员，是否可发信息．
		if(strpos($groupinfo['authority'], '1') === false) showmsg("你没有权限发信息!");		
		
		$messageid = isset($_GET['messageid']) ? intval($_GET['messageid']) : 0;
		if(is_post()){
			
			$this->_check_code($_POST['code']);
			$send_to = trim($_POST['send_to']);
			
			if(!is_username($send_to)) return_message('收件人格式不正确！', 0);
			if($send_to == $username) return_message('不能给自己发送短信息！', 0);
			if(!$this->db->where(array('username' => $send_to))->find()) return_message('收件人不存在！', 0);

			$data['send_from'] = $username;
			$data['send_to'] = $send_to;
			$data['message_time'] = SYS_TIME;
			$data['subject'] = trim($_POST['subject']);
			$data['content'] = trim($_POST['content']);
			$data['replyid'] = $messageid;
			$data['status'] = 1;
			$data['isread'] = 0;
			$data['issystem'] = 0;
			if(D('message')->insert($data, true)){
				return_message(L('operation_success'), 1, U('outbox'));
			}else{
				return_message(L('operation_failure'), 0);
			}
			
		}else{
			$data = array();
			if($messageid){
				$data = D('message')->where(array('messageid' => $messageid, 'send_to' => $username, 'status' => '1'))->find();
				$data['subject'] = !empty($data['subject']) ? '回复：'.$data['subject'] : '';	
			}
			if(isset($_GET['username'])) $data['send_from'] = safe_replace($_GET['username']);
		}		
		include template('member', 'new_messages');
	}
	
	
	/**
	 * 会员中心 - 发件箱
	 */	
	public function outbox(){ 
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		$message = D('message');
		$total = $message->where(array('send_from' => $username))->total();
		$page = new page($total, 15);
		$data = $message->where(array('send_from' => $username))->order('messageid DESC')->limit($page->limit())->select();	
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull(false);
		include template('member', 'outbox');
	}
	
	
	
	/**
	 * 会员中心 - 删除发件箱信息
	 */	
	public function outbox_del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		if(!isset($_POST['fx'])) showmsg('请选择要操作的内容！');
		if(!is_array($_POST['fx'])) showmsg(L('illegal_operation'), 'stop');
		$message = D('message');
		foreach($_POST['fx'] as $v){
			$message->delete(array('messageid' => intval($v), 'send_from' => $username));
		}
		
		showmsg(L('operation_success'), U('outbox'), 1);
	}
	
	
	
	/**
	 * 会员中心 - 收件箱
	 */	
	public function inbox(){ 
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		$message = D('message');
		//收件箱中只有收件人未删除[未隐藏]的信息
		$total = $message->where(array('send_to' => $username, 'status' => 1))->total();
		$page = new page($total, 15);
		$data = $message->where(array('send_to' => $username, 'status' => 1))->order('messageid DESC')->limit($page->limit())->select();	
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull(false);
		include template('member', 'inbox');
	}

	
	
	/**
	 * 会员中心 - 系统消息
	 */	
	public function system_msg(){ 
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$message_group = D('message_group');
		$total = $message_group->where(array('groupid' => $groupid, 'status' => '1'))->total(); 

		$page = new page($total,10);
		$data = $message_group->where(array('groupid' => $groupid, 'status' => '1'))->limit($page->limit())->order('id DESC')->select(); 

		$read = array();
		foreach($data as $val){
			$d = D('message_data')->where(array('userid'=>$userid, 'group_message_id'=>$val['id']))->find();
			if(!$d){
				$read[$val['id']] = 0;//未读 红色
			}else {
				$read[$val['id']] = 1;
			}
		}		
		
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull(false);
		include template('member', 'system_msg');
	}



	
	/**
	 * 会员中心 - 删除收件箱信息
	 */	
	public function inbox_del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		if(!isset($_POST['fx'])) showmsg('请选择要操作的内容！');
		if(!is_array($_POST['fx'])) showmsg(L('illegal_operation'), 'stop');
		$message = D('message');
		foreach($_POST['fx'] as $v){
			$message->update(array('status' => 0), array('send_to' => $username, 'messageid' => intval($v))); //只是隐藏，不执行删除操作	
		}
		
		showmsg(L('operation_success'), U('inbox'), 1);
	}
	
	
	
	/**
	 * 会员中心 - 阅读发件箱的信息
	 */	
	public function read_my_message(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$messageid = isset($_GET['messageid']) ? intval($_GET['messageid']) : showmsg(L('illegal_operation'), 'stop');

		$data = D('message')->where(array('messageid' => $messageid, 'send_from' => $username))->find(); 
		if(!$data) showmsg('你查看的信息不存在！');
		include template('member', 'read_message');
	}
	
	
	
	
	/**
	 * 会员中心 - 阅读收件箱的信息
	 */	
	public function read_message(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$messageid = isset($_GET['messageid']) ? intval($_GET['messageid']) : showmsg(L('illegal_operation'), 'stop');
		$message = D('message');
		$data = $message->where(array('messageid' => $messageid, 'send_to' => $username))->find(); 
		if(!$data || $data['status']==0) showmsg('你查看的信息不存在！');   //信息不存在或者已经删除
		$message->update(array('isread' => '1'),array('send_to' => $username, 'messageid' => $messageid));  //更新为已读状态
		include template('member', 'read_message');
	}
	
	
	
	/**
	 * 会员中心 - 阅读系统信息[群发]消息
	 */	
	public function read_system_message(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$id = isset($_GET['id']) ? intval($_GET['id']) : showmsg(L('illegal_operation'), 'stop'); //系统[群发]消息
		$message_group = D('message_group');
		$data = $message_group->where(array('id' => $id))->find(); 
		if(!$data || $data['groupid']!=$groupid || $data['status']!=1) showmsg('你查看的信息不存在！');
		$message_data = D('message_data');
		$result = $message_data->where(array('group_message_id' => $id, 'userid' => $userid))->find();
		if(!$result){
			$message_data->insert(array('userid' => $userid, 'group_message_id' => $id));   //更新为已读状态,插入到已读表
		}
		
		include template('member', 'read_system_message');
	}
}