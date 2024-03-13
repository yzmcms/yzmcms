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

class comment extends common {

	/**
	 * 评论列表
	 */
	public function init() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','username','title','inputtime','ip','status')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$site_url = self::$siteid ? rtrim(get_site_url(), '/') : '';
		$modelid = 0;
		$modelinfo = get_site_modelinfo();
		$comment = D('comment');
		$where = array('a.siteid'=>self::$siteid);
		$total = $comment->alias('a')->join('yzmcms_comment_data b ON a.commentid=b.commentid')->where($where)->total();
		$page = new page($total, 15);
		$data = $comment->alias('a')->field('a.*,b.title,b.url')->join('yzmcms_comment_data b ON a.commentid=b.commentid')->where($where)->order("$of $or")->limit($page->limit())->select();		
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
		$where = 'a.siteid = '.self::$siteid;
		if(isset($_GET['dosubmit'])){
			$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;
			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';
			$status = isset($_GET["status"]) ? intval($_GET["status"]) : 99 ;
			if($modelid) $where .= ' AND modelid = '.$modelid;
			if($status != 99) $where .= ' AND status = '.$status;
			if(isset($_GET["start"]) && $_GET["start"] != '' && $_GET["end"]){		
				$where .= ' AND inputtime BETWEEN '.strtotime($_GET["start"]).' AND '.strtotime($_GET["end"]);
			}
			if($searinfo){
				if($type == '1'){
					$where .= ' AND title LIKE \'%'.$searinfo.'%\'';
				}elseif($type == '2'){
					$where .= ' AND content LIKE \'%'.$searinfo.'%\'';
				}else{
					$where .= ' AND username LIKE \'%'.$searinfo.'%\'';
				}
					
			}			
		}		
		$_GET = array_map('htmlspecialchars', $_GET);
		$site_url = self::$siteid ? rtrim(get_site_url(), '/') : '';
		$modelinfo = get_site_modelinfo();
		$comment = D('comment');
		$total = $comment->alias('a')->where($where)->join('yzmcms_comment_data b ON a.commentid=b.commentid')->total();
		$page = new page($total, 15);
		$data = $comment->alias('a')->field('a.*,b.title,b.url')->where($where)->join('yzmcms_comment_data b ON a.commentid=b.commentid')->order("$of $or")->limit($page->limit())->select();		
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
				if(!$comment_data) return_json(array('status'=>0,'message'=>'评论【'.$val.'】不存在，请检查！'));
				$commentid = $comment_data['commentid'];
				$comment->delete(array('id'=>$val));
				$comment->query("UPDATE yzmcms_comment_data SET `total` = `total`-1 WHERE commentid='$commentid'");
				$comment->delete(array('reply'=>$val));
			}
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}
	}
	
	
	/**
	 * 通过审核
	 */
	public function adopt() {

		$comment = D('comment');
		if(is_array($_POST['id'])){
			foreach($_POST['id'] as $val){
				$comment->update(array('status'=>1), array('id' => $val));
			}
		}else{
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$value = isset($_POST['value']) ? intval($_POST['value']) : 0;
			$comment->update(array('status'=>$value), array('id'=>$id));
		}
		return_json(array('status'=>1,'message'=>L('operation_success')));
	}
	
}