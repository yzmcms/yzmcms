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

class link extends common{

	/**
	 * 友情链接列表
	 */	
	public function init(){	
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','listorder','typeid','linktype','addtime','status')) ? $of : 'listorder ASC, id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$link = D('link');
		$where = array('siteid'=>self::$siteid);
		if(isset($_GET['dosubmit'])){	
			$status = isset($_GET['status']) ? intval($_GET['status']) : 99;
			$linktype = isset($_GET['linktype']) ? intval($_GET['linktype']) : 99;
			$type = isset($_GET['type']) ? intval($_GET['type']) : 1;
			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';

			if($status != 99) $where['status'] = $status;
			if($linktype != 99)  $where['linktype'] = $linktype;
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where['addtime>='] = strtotime($_GET['start']);
				$where['addtime<='] = strtotime($_GET['end']);
			}
			if($searinfo){
				if($type == '1')
					$where['name'] = '%'.$searinfo.'%';
				elseif($type == '2')
					$where['url'] = '%'.$searinfo.'%';
				elseif($type == '3')
					$where['username'] = '%'.$searinfo.'%';
				else
					$where['email'] = '%'.$searinfo.'%';
			}
		}
		$total = $link->where($where)->total();
		$page = new page($total, 15);
		$data = $link->where($where)->order("$of $or")->limit($page->limit())->select();		
		include $this->admin_tpl('link_list');
	}
	

	/**
	 * 添加友情链接
	 */
 	public function add() {
 		if(isset($_POST['dosubmit'])) {
 			$_POST['url'] = trim($_POST['url']);
			if(!$_POST['url']) return_json(array('status'=>0,'message'=>'网站地址不能为空！'));

			$link = D('link');
			$res = $link->where(array('siteid'=>self::$siteid, 'url'=>$_POST['url']))->find();
			if($res) return_json(array('status'=>0, 'message'=>'该网站地址已存在！'));
			$_POST['siteid'] = self::$siteid;										
			$_POST['addtime'] = SYS_TIME;										
			$link->insert($_POST, true);
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			include $this->admin_tpl('link_add');
		}

	}

	
	/**
	 * 编辑友情链接
	 */
 	public function edit() {
		$link = D('link');
		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			
			$_POST['url'] = trim($_POST['url']);
			if($link->update($_POST, array('id' => $id), true)){
				return_json(array('status'=>1, 'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = $link->where(array('id' => $id))->find();
			include $this->admin_tpl('link_edit');
		}

	}

	
	/**
	 * 删除多个友情链接
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			if(D('link')->delete($_POST['id'], true)){
				showmsg(L('operation_success'), '', 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}
	
	
	/**
	 * 删除一个友情链接
	 */
	public function del_one() {
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(D('link')->delete(array('id' => $id))){
			showmsg(L('operation_success'), '', 1);
		}else{
			showmsg(L('operation_failure'));
		}
	}
	
	
	/**
	 * 排序
	 */
	public function order() {
		if(isset($_POST['order_id']) && is_array($_POST['order_id'])){
			$link = D('link');
			foreach($_POST['order_id'] as $key=>$val){
				$link->update(array('listorder'=>$_POST['listorder'][$key]),array('id'=>intval($val)));
			}
		}
		showmsg(L('operation_success'), '', 1);
	}
	
	
	/**
	 * 审核
	 */
	public function adopt() {
		if(is_post()){
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$value = isset($_POST['value']) ? intval($_POST['value']) : 0;
			
			if(D('link')->update(array('status'=>$value), array('id' => $id))){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}
	}

}