<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class comment extends common {

	/**
	 * 评论列表
	 */
	public function init() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','username','title','inputtime','ip','status')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$modelid = 0;
		$modelinfo = get_modelinfo();
		$comment = D('comment');
		$total = $comment->join('yzmcms_comment_data b ON yzmcms_comment.commentid=b.commentid')->total();
		$page = new page($total, 15);
		$data = $comment->field('yzmcms_comment.*,b.title,b.url')->join('yzmcms_comment_data b ON yzmcms_comment.commentid=b.commentid')->order("$of $or")->limit($page->limit())->select();		
		include $this->admin_tpl('comment_list');
	}


	/**
	 * 评论搜索
	 */
	public function search() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','username','title','inputtime','ip','status')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$where = '1=1';
		if(isset($_GET['dosubmit'])){
			$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;
			$searinfo = isset($_GET["searinfo"]) ? safe_replace($_GET["searinfo"]) : '';
			$status = isset($_GET["status"]) ? intval($_GET["status"]) : 99 ;
			if($modelid) $where .= ' AND modelid = '.$modelid;
			if($searinfo != ''){
				if($type == '1')
					$where .= ' AND title LIKE \'%'.$searinfo.'%\'';
				else
					$where .= ' AND username LIKE \'%'.$searinfo.'%\'';
			}			
			if($status != 99) $where .= ' AND status = '.$status;
		}		
		$_GET = array_map('htmlspecialchars', $_GET);
		$modelinfo = get_modelinfo();
		$comment = D('comment');
		$total = $comment->where($where)->join('yzmcms_comment_data b ON yzmcms_comment.commentid=b.commentid')->total();
		$page = new page($total, 15);
		$data = $comment->field('yzmcms_comment.*,b.title,b.url')->where($where)->join('yzmcms_comment_data b ON yzmcms_comment.commentid=b.commentid')->order("$of $or")->limit($page->limit())->select();		
		include $this->admin_tpl('comment_list');
	}

	
	/**
	 * 删除评论
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			$comment = D('comment');
			foreach($_POST['id'] as $val){
				$comment_data = $comment ->field('commentid')->where(array('id'=>$val))->find();
				if(!$comment_data) showmsg('该评论不存在，请返回检查！');
				$commentid = $comment_data['commentid'];
				$comment->delete(array('id'=>$val));
				$comment->query("UPDATE yzmcms_comment_data SET `total` = `total`-1 WHERE commentid='$commentid'");
				$comment->delete(array('reply'=>$val));
			}
			showmsg(L('operation_success'));
		}
	}
	
	
	/**
	 * 通过审核
	 */
	public function adopt() {
		if($_POST && is_array($_POST['id'])){
			$comment = D('comment');
			foreach($_POST['id'] as $val){
				$comment->update(array('status' => '1'), array('id' => $val));
			}
			showmsg(L('operation_success'));
		}
	}
	
}