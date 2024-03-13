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
		$where = array('siteid'=>self::$siteid);
		$total = $attachment->where($where)->total();
		$page = new page($total, 15);
		$data = $attachment->where($where)->order("$of $or")->limit($page->limit())->select();		

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
		$where = array('siteid' => self::$siteid);
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
			echo '<p style="text-align:center;margin-top:50px">';
			echo $info['isimage'] ? '<img src="'.$info['filepath'].$info['filename'].'" style="max-width:100%">' : '<img src="'.file_icon($info['filename']).'" title="'.$info['originname'].'" style="width:100px">
				<span style="display:block;margin-top:8px;font-size:13px;color:#333;">'.$info['originname'].'</span>
				<a style="font-size:14px;display:block;margin-top:20px;color:#0583e7" href="'.$info['filepath'].$info['filename'].'" download="'.$info['originname'].'">点击下载</a>';
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
			return_json(array('status'=>0,'message'=>'附件操作类「'.$upload_type.'」不存在！'));
		}

		// PHP5.2不支持 $class::$method();
		$upload = new $upload_type();
		$res = $upload->deletefile($info);
		if(!$res)  return_json(array('status'=>0,'message'=>'删除文件「'.$info['filename'].'」失败！'));

		if($attachment->delete(array('id' => $id))){
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			return_json(array('status'=>0,'message'=>'删除文件「'.$info['filename'].'」失败！'));
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
				return_json(array('status'=>0,'message'=>'附件操作类「'.$upload_type.'」不存在！'));
			}

			// PHP5.2不支持 $class::$method();
			$upload = new $upload_type();
			foreach($_POST['id'] as $val){
				$info = $attachment->field('filepath,filename')->where(array('id'=>$val))->find();
				$res = $upload->deletefile($info);
				if(!$res)  return_json(array('status'=>0,'message'=>'删除文件「'.$info['filename'].'」失败！'));
				$attachment->delete(array('id' => $val));
			}
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}
	}


	/**
	 * 附件统计
	 */
	public function public_count(){
		$img_total = D('attachment')->where(array('siteid'=>self::$siteid,'isimage'=>1))->total();
		$att_total = D('attachment')->where(array('siteid'=>self::$siteid,'isimage'=>0))->total();
		$total = $img_total+$att_total;
		
		$img_size = D('attachment')->field('SUM(filesize)')->where(array('siteid'=>self::$siteid,'isimage'=>1))->one();
		$att_size = D('attachment')->field('SUM(filesize)')->where(array('siteid'=>self::$siteid,'isimage'=>0))->one();
		$total_size = $img_size+$att_size;

		$data = array(
			'img_total' => $img_total,
			'att_total' => $att_total,
			'total' => $total,
			'img_size' => sizecount($img_size),
			'att_size' => sizecount($att_size),
			'total_size' => sizecount($total_size),
			'img_proportion' => $total_size ? round($img_size/$total_size*100, 2) : 0,
			'att_proportion' => $total_size ? round($att_size/$total_size*100, 2) : 0,
		);
		return_json(array('status'=>1,'message'=>L('operation_success'),'data'=>$data));
	}
	
}