<?php
/**
 * 系统附件上传管理
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2021-03-29 
 */

defined('IN_YZMPHP') or exit('Access Denied');

new_session_start();
yzm_base::load_sys_class('page','',0);

class api{
	
	private $isadmin;
	private $groupid;
	private $userid;
	private $username;
	
	public function __construct() {
		
		$this->userid = isset($_SESSION['adminid']) ? $_SESSION['adminid'] : (isset($_SESSION['_userid']) ? $_SESSION['_userid'] : 0);
		$this->username = isset($_SESSION['adminname']) ? $_SESSION['adminname'] : (isset($_SESSION['_username']) ? $_SESSION['_username'] : '');
		$this->isadmin = isset($_SESSION['roleid']) ? 1 : 0;
		$this->groupid = get_cookie('_groupid') ? intval(get_cookie('groupid')) : 0;
		if(!$this->userid) showmsg(L('login_website'), U('member/index/login'), 1);
		if($this->isadmin) define('IN_YZMADMIN', true);
		
	}	
	
	
	
	public function init(){
		
	}

	
	
	/**
	 * 上传文件
	 */	
	public function upload(){ 

		//$filename = isset($_POST['filename']) ? $_POST['filename'] : 'file';
		$filename = 'file';
		$open_watermark = isset($_POST['open_watermark']) ? intval($_POST['open_watermark']) : 0;
		$filetype = isset($_POST['filetype']) ? intval($_POST['filetype']) : 1;
		$module = isset($_POST['module']) ? htmlspecialchars($_POST['module']) : '';
		$option = array();
		if($filetype == 1){
			$option['allowtype'] = array('gif', 'jpg', 'png', 'jpeg');
		}else{
			$option['allowtype'] = $this->_get_upload_types();
		}

		$upload_type = C('upload_type', 'host');
		yzm_base::load_model($upload_type, '', 0);
		if(!class_exists($upload_type)) return_json(array('status'=>0, 'msg'=>'附件上传类「'.$upload_type.'」不存在！'));
		$upload = new $upload_type($option);
		if($upload->uploadfile($filename)){
			$fileinfo = $upload->getnewfileinfo();
			$fileinfo['module'] = $module;
			$fileinfo['originname'] = safe_replace($fileinfo['originname']);
			$attid = $this->_att_write($fileinfo);
			$arr = array(
				'status' => 1,
				'attid' => $attid,
				'delurl' => U('delete', array('id'=>$attid)),
				'msg' => $fileinfo['filepath'].$fileinfo['filename'],
				'title' => $fileinfo['originname'],
				'size' => $fileinfo['filesize'],
				'filetype' => $fileinfo['filetype']
			);
			if($open_watermark) watermark($arr['msg']);
			return_json($arr);
		}else{
			$arr = array(
				'status' => 0,
				'msg' => $upload->geterrormsg()
			);
			return_json($arr);
		} 
	}
	
	
	/**
	 * 上传框
	 */	
	public function upload_box(){ 
		$pid = isset($_GET['pid']) ? $_GET['pid'] : 'uploadfile';
		$module = isset($_GET['module']) ? $_GET['module'] : '';
		$t = isset($_GET['t']) ? intval($_GET['t']) : 1; //上传类型，1为图片类型
		$n = isset($_GET['n']) ? 20 : 1;  //上传数量
		$s = round(get_config('upload_maxsize')/1024, 2).'MB';  //允许上传附件大小
		$originname = isset($_GET['originname']) ? safe_replace(trim($_GET['originname'])) : '';
		$uploadtime = isset($_GET['uploadtime']) ? htmlspecialchars($_GET['uploadtime']) : '';
		
		if($t == 1){
			$type = 'png,gif,jpg,jpeg';
		}else{
			$type = join(',', $this->_get_upload_types());
		}
		
		$where = array();
		if(!$this->isadmin) $where['userid'] = $this->userid;
		if(isset($_GET['dosubmit'])){
			if($originname) $where['originname'] = '%'.$originname.'%';
			if($uploadtime) {
				$start_time = strtotime($uploadtime.' 00:00:00');
				$end_time = strtotime($uploadtime.' 23:59:59');
				$where['uploadtime>='] = $start_time;
				$where['uploadtime<='] = $end_time;
			}
		}
		$attachment = D('attachment');
		$total = $attachment->where($where)->total();
		$parameter = $_GET;
		$parameter['tab'] = 1;
		$page = new page($total, 8, $parameter);
		$data = $attachment->field('id,isimage,originname,filename,filepath,fileext')->where($where)->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('upload_box'); 
	}
		

	/**
	 * 图像裁剪
	 */	
	public function img_cropper(){
		$yzmcms_path = SITE_PATH == '/' ? YZMPHP_PATH : str_replace(SITE_PATH, '', str_replace('\\', '/', YZMPHP_PATH));
		if(isset($_POST['filepath'])){
			$x = $_POST['x'];
			$y = $_POST['y'];
			$w = $_POST['w'];
			$h = $_POST['h'];
			$image = yzm_base::load_sys_class('image');
			$filepath = SITE_PATH.C('upload_file').'/'.date('Ym/d/');
			$filename = date("ymdhis").rand(100,999);
			$filetype = fileext($_POST['filepath']);
			$fileinfo = $image->crop($yzmcms_path.$_POST['filepath'], $yzmcms_path.$filepath.$filename.'.'.$filetype, $w, $h, $x, $y);
			if($fileinfo){
				$fileinfo['module'] = 'admin';
				$fileinfo['originname'] = basename($_POST['filepath']);
				$fileinfo['filepath'] = $filepath;
				$fileinfo['filename'] = $filename.'.'.$filetype;
				$fileinfo['filesize'] = $fileinfo['size'];
				$fileinfo['filetype'] = $filetype;
				$this->_att_write($fileinfo);
				return_json(array('status' => 1, 'filepath' => $fileinfo['filepath'].$fileinfo['filename']));
			}else{
				return_json(array('status' => 0));
			}
		}

		$filepath = isset($_GET['f']) ? base64_decode($_GET['f']) : showmsg(L('lose_parameters'), 'stop');
		if(strpos($filepath, '://')) $filepath = strstr($filepath, SITE_PATH.'uploads');
		if(!is_file($yzmcms_path.$filepath)) showmsg('请选择本地已存在的图像！', 'stop');
		$spec = isset($_GET['spec']) ? intval($_GET['spec']) : 1; 
		$cid = isset($_GET['cid']) ? $_GET['cid'] : 'thumb';
		switch ($spec){
			case 1:
			  $spec = '4 / 3';
			  break;  
			case 2:
			  $spec = '3 / 2';
			  break;
			case 3:
			  $spec = '1 / 1';
			  break;
			case 4:
			  $spec = '2 / 3';
			  break;  
			default:
			  $spec = '3 / 2';
		}
		include $this->admin_tpl('cropper_box'); 
	}


	/**
	 * 删除附件
	 */	
	public function delete(){
		$id = isset($_GET['id']) ? intval($_GET['id']) : return_json(array('status'=>0, 'message'=>L('lose_parameters')));
		$attachment = D('attachment');
		$info = $attachment->field('userid,filepath,filename')->where(array('id'=>$id))->find();
		if(!$this->isadmin && $info['userid']!=$this->userid) return_json(array('status'=>0, 'message'=>'无权删除该文件！'));

		$upload_type = C('upload_type', 'host');
		yzm_base::load_model($upload_type, '', 0);
		if(!class_exists($upload_type)) return_json(array('status'=>0, 'message'=>'附件操作类「'.$upload_type.'」不存在！'));

		// PHP5.2不支持 $class::$method();
		$upload = new $upload_type();
		$res = $upload->deletefile($info);
		if(!$res)  return_json(array('status'=>0, 'message'=>'删除文件「'.$info['filename'].'」失败！'));

		if($attachment->delete(array('id' => $id))){
			return_json(array('status'=>1, 'message'=>L('operation_success')));
		}else{
			return_json(array('status'=>0, 'message'=>'删除文件「'.$info['filename'].'」失败！'));
		}
	}

	
	/**
	 * 上传附件写入数据库
	 */	
	private function _att_write($fileinfo){
		$arr = array();
		$arr['siteid'] = get_siteid();
		$arr['originname'] = strlen($fileinfo['originname'])<50 ? htmlspecialchars($fileinfo['originname']) : htmlspecialchars(str_cut($fileinfo['original'], 45));
		$arr['filename'] = $fileinfo['filename'];
		$arr['filepath'] = $fileinfo['filepath'];
		$arr['filesize'] = $fileinfo['filesize'];
		$arr['fileext'] = $fileinfo['filetype'];
		$arr['module'] = $fileinfo['module'];
		$arr['isimage'] = in_array($fileinfo['filetype'], array('gif', 'jpg', 'png', 'jpeg')) ? 1 : 0;
		$arr['downloads'] = 0;
		$arr['userid'] = $this->userid;
		$arr['username'] = $this->username;
		$arr['uploadtime'] = SYS_TIME;
		$arr['uploadip'] = getip();
		return D('attachment')->insert($arr);
	}

	
	
	/**
	 * 获取上传类型
	 */	
	private function _get_upload_types(){
		$arr = explode('|', get_config('upload_types'));
		$allow = array('gif', 'jpg', 'png', 'jpeg', 'webp', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'csv', 'mp4', 'avi', 'wmv', 'rmvb', 'flv', 'mp3', 'wma', 'wav', 'amr', 'ogg', 'torrent');
		foreach($arr as $key => $val){
			if(!in_array($val, $allow)) unset($arr[$key]);
		}
		
		return $arr;
	}
	

	/**
	 * 加载模板
	 */	
	public static function admin_tpl($file = 'Undefined', $m = '') {
		$m = empty($m) ? ROUTE_M : $m;
		if(empty($m)) return false;
		return APP_PATH.$m.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$file.'.html';
	}

}