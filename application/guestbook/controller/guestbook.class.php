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
		$total = $guestbook->where(array('siteid'=>self::$siteid, 'replyid'=>'0'))->total();
		$page = new page($total, 15);
		$data = $guestbook->where(array('siteid'=>self::$siteid, 'replyid'=>'0'))->order("$of $or")->limit($page->limit())->select();		
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
		$where = 'siteid='.self::$siteid.' AND replyid=0';
		if(isset($_GET['dosubmit'])){
			$ischeck = isset($_GET['ischeck']) ? intval($_GET['ischeck']) : 99;
			$isread = isset($_GET['isread']) ? intval($_GET['isread']) : 99;
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;
			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';

			if($ischeck != 99) $where .= ' AND ischeck = '.$ischeck;
			if($isread != 99) $where .= ' AND isread = '.$isread;
			if(isset($_GET["start"]) && $_GET["start"] != '' && $_GET["end"]){		
				$where .= ' AND booktime BETWEEN '.strtotime($_GET["start"]).' AND '.strtotime($_GET["end"]);
			}
			if($searinfo){
				if($type == '1'){
					$where .= ' AND title LIKE \'%'.$searinfo.'%\'';
				}elseif($type == '2'){
					$where .= ' AND name LIKE \'%'.$searinfo.'%\'';
				}else{
					$where .= ' AND ip LIKE \'%'.$searinfo.'%\'';
				}
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
 		if(is_post()) {
			$_POST['name'] = $_SESSION['adminname'];
			$_POST['booktime'] = SYS_TIME;
			$_POST['ip'] = getip();
			$guestbook->insert($_POST, true);
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			$guestbook->update(array('isread'=>'1'),array('id'=>$id));
			$data = $guestbook->where(array('id'=>$id))->find();
			$reply = $guestbook->field('id,name,booktime,bookmsg,ip')->where(array('replyid'=>$id))->select(); //管理员回复
			include $this->admin_tpl('guestbook_read');
		}
	}


	/**
	 * 删除回复
	 */
	public function del_reply(){
		$id = input('get.id', 0, 'intval');
		D('guestbook')->delete(array('id'=>$id));
		return_json(array('status'=>1,'message'=>L('operation_success')));
	}
	
	
	/**
	 * 留言显示/隐藏
	 */
 	public function toggle() {
		$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		$value = isset($_POST['value']) ? intval($_POST['value']) : 0;
		D('guestbook')->update(array('ischeck'=>$value), array('id'=>$id));
		return_json(array('status'=>1, 'message'=>L('operation_success')));
	}


	/**
	 * 设置已读
	 */
 	public function set_read() {
		if($_POST && is_array($_POST['id'])){
			$guestbook = D('guestbook');
			foreach($_POST['id'] as $val){
				$guestbook->update(array('isread'=>1), array('id'=>$val));
			}
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}
	}

	
	/**
	 * 删除多个留言
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			$guestbook = D('guestbook');
			foreach($_POST['id'] as $val){
				$guestbook->delete(array('id'=>$val));
				$guestbook->delete(array('replyid'=>$val));
			}
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}
	}
	
}