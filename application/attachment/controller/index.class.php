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

class index extends common{
	
	/**
	 * 附件列表
	 */	
	public function init(){ 
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','module','filesize','uploadtime','username','uploadip')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$attachment = D('attachment');
		$total = $attachment->total();
		$page = new page($total, 15);
		$data = $attachment->order("$of $or")->limit($page->limit())->select();		

		include $this->admin_tpl('attachment_list');
	}
	

	/**
	 * 附件搜索
	 */	
	public function search_list(){ 
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','module','filesize','uploadtime','username','uploadip')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$attachment = D('attachment');
		$where = array();
		if(isset($_GET['dosubmit'])){
			if(isset($_GET['originname']) && $_GET['originname']){
				$where['originname'] = '%'.$_GET['originname'].'%';
			}
			if(isset($_GET['username']) && $_GET['username']){
				$where['username'] = '%'.$_GET['username'].'%';
			}
			if(isset($_GET['fileext']) && $_GET['fileext']){
				$where['fileext'] = $_GET['fileext'];
			}
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where['uploadtime>='] = strtotime($_GET['start']);
				$where['uploadtime<='] = strtotime($_GET['end']);
			}			
		}
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $attachment->where($where)->total();
		$page = new page($total, 15);
		$data = $attachment->where($where)->order("$of $or")->limit($page->limit())->select();			

		include $this->admin_tpl('attachment_list');
	}

	
	/**
	 * 附件浏览
	 */	
	public function public_att_view(){		
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$info = D('attachment')->where(array('id'=>$id))->find();
		if($info){
			echo '<p style="text-align:center;">';
			echo $info['isimage'] ? '<img src="'.$info['filepath'].$info['filename'].'" style="max-width:100%">' : '<img src="'.(in_array($info['fileext'], array('zip', 'rar')) ? STATIC_URL.'images/ext/rar.png' : STATIC_URL.'images/ext/blank.png').'" title="'.$info['originname'].'"><a style="font-size:14px;display:block;margin-top:20px;" href="'.$info['filepath'].$info['filename'].'">点击下载</a>';
			echo '</p>';
		}
	}
	
	
	/**
	 * 删除一个附件
	 */
	public function del_one() {
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$attachment = D('attachment');
		$info = $attachment->field('filepath,filename')->where(array('id'=>$id))->find();
		$upload_type = C('upload_type', 'host');
		yzm_base::load_model($upload_type, '', 0);
		if(!class_exists($upload_type)){
			showmsg('附件操作类「'.$upload_type.'」不存在！', 'stop');
		}

		// PHP5.2不支持 $class::$method();
		$upload = new $upload_type();
		$res = $upload->deletefile($info);
		if(!$res)  showmsg('删除文件「'.$info['filename'].'」失败！', 'stop');

		if($attachment->delete(array('id' => $id))){
			showmsg(L('operation_success'), '', 1);
		}else{
			showmsg('删除文件「'.$info['filename'].'」失败！', 'stop');
		}
	}

	
	/**
	 * 删除多个附件
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			$attachment = D('attachment');
			$upload_type = C('upload_type', 'host');
			yzm_base::load_model($upload_type, '', 0);
			if(!class_exists($upload_type)){
				showmsg('附件操作类「'.$upload_type.'」不存在！', 'stop');
			}

			// PHP5.2不支持 $class::$method();
			$upload = new $upload_type();
			foreach($_POST['id'] as $val){
				$info = $attachment->field('filepath,filename')->where(array('id'=>$val))->find();
				$res = $upload->deletefile($info);
				if(!$res)  showmsg('删除文件「'.$info['filename'].'」失败！', 'stop');
				$attachment->delete(array('id' => $val));
			}
			showmsg(L('operation_success'), '', 1);
		}
	}
	
}