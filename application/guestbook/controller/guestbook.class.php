<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class guestbook extends common {

	/**
	 * 留言板列表
	 */
	public function init() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','ip','booktime','isread','ischeck')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$guestbook = D('guestbook');
		$total = $guestbook->where(array('replyid'=>'0'))->total();
		$page = new page($total, 15);
		$data = $guestbook->where(array('replyid'=>'0'))->order("$of $or")->limit($page->limit())->select();		
		include $this->admin_tpl('guestbook_list');
	}


	/**
	 * 留言搜索
	 */
	public function search() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','ip','booktime','isread','ischeck')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$where = 'replyid=0';
		if(isset($_GET['dosubmit'])){
			$ischeck = isset($_GET['ischeck']) ? intval($_GET['ischeck']) : 99;
			$isread = isset($_GET['isread']) ? intval($_GET['isread']) : 99;
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;
			$searinfo = isset($_GET["searinfo"]) ? trim(safe_replace($_GET["searinfo"])) : '';

			if($ischeck != 99) $where .= ' AND ischeck = '.$ischeck;
			if($isread != 99) $where .= ' AND isread = '.$isread;
			if($searinfo != ''){
				if($type == '1')
					$where .= ' AND title LIKE \'%'.$searinfo.'%\'';
				else
					$where .= ' AND name LIKE \'%'.$searinfo.'%\'';
			}			
		}		
		$_GET = array_map('htmlspecialchars', $_GET);
		$guestbook = D('guestbook');
		$total = $guestbook->where($where)->total();
		$page = new page($total, 15);
		$data = $guestbook->where($where)->order("$of $or")->limit($page->limit())->select();		
		include $this->admin_tpl('guestbook_list');
	}


	/**
	 * 查看及回复留言
	 */
	public function read() {
		$guestbook = D('guestbook');
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
 		if(isset($_POST['dosubmit'])) {
			$_POST['name'] = $_SESSION['adminname'];
			$_POST['booktime'] = SYS_TIME;
			$_POST['ip'] = getip();
			$guestbook->insert($_POST, true);
			showmsg("回复成功！", '', 1);
		}else{
			$guestbook->update(array('isread'=>'1'),array('id'=>$id));
			$data = $guestbook->where(array('id'=>$id))->find();
			$reply = $guestbook->field('booktime,bookmsg')->where(array('replyid'=>$id))->select(); //管理员回复
			include $this->admin_tpl('guestbook_read');
		}
	}
	
	/**
	 * 留言显示/隐藏
	 */
 	public function toggle() {
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$ischeck = isset($_POST['ischeck']) ? intval($_POST['ischeck']) : 0;
	    D('guestbook')->update(array('ischeck'=>$ischeck), array('id'=>$id));
	}

	
	/**
	 * 删除多个留言
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			$guestbook = D('guestbook');
			foreach($_POST['id'] as $val){
				$guestbook->delete(array('id'=>$val));
				$guestbook->delete(array('replyid'=>$val));  //删除回复
			}
			showmsg(L('operation_success'));
		}
	}
	
}