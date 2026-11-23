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
		$this->groupid = get_cookie('_groupid') ? intval(get_cookie('_groupid')) : 0;
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
		$option['allowtype'] = handle_upload_types($filetype);

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
		$pid = isset($_GET['pid']) ? htmlspecialchars($_GET['pid']) : 'uploadfile';
		$module = isset($_GET['module']) ? htmlspecialchars($_GET['module']) : '';
		$t = isset($_GET['t']) ? intval($_GET['t']) : 1; //上传类型，1为图片类型
		$n = isset($_GET['n'])&&intval($_GET['n']) ? intval($_GET['n']) : 1;  //上传数量
		$s = sizecount(get_config('upload_maxsize')*1024);  //允许上传附件大小
		$originname = isset($_GET['originname']) ? safe_replace($_GET['originname']) : '';
		$uploadtime = isset($_GET['uploadtime']) ? htmlspecialchars($_GET['uploadtime']) : '';
		$type = join(',', handle_upload_types($t));
		
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
			$src_real = $this->_resolve_image_path($_POST['filepath'], $yzmcms_path);
			if(!$src_real) return_message('非法或不存在图像文件！', 0);
			$x = isset($_POST['x']) ? intval($_POST['x']) : 0;
			$y = isset($_POST['y']) ? intval($_POST['y']) : 0;
			$w = isset($_POST['w']) ? intval($_POST['w']) : 0;
			$h = isset($_POST['h']) ? intval($_POST['h']) : 0;
			$attid = isset($_POST['attid']) ? intval($_POST['attid']) : 0;
			if(!$w || !$h) return_message('请选择裁剪区域！', 0);
			
			$image = yzm_base::load_sys_class('image');
			$filepath = SITE_PATH.C('upload_file').'/'.date('Ym/d/');
			$filename = date("ymdhis").rand(100,999);
			$filetype = strtolower(pathinfo($src_real, PATHINFO_EXTENSION));
			if(!in_array($filetype, array('jpg','jpeg','png','gif','bmp','webp'))) return_message('只允许裁剪图片文件！', 0);
			$fileinfo = $image->crop($src_real, $yzmcms_path.$filepath.$filename.'.'.$filetype, $w, $h, $x, $y);
			if($fileinfo){
				$info = D('attachment')->field('userid,filepath,filename,originname')->where(array('id'=>$attid))->find();
				$fileinfo['module'] = ROUTE_M;
				$fileinfo['originname'] = $info ? '裁剪_'.$info['originname'] : '裁剪_'.basename($_POST['filepath']);
				$fileinfo['filepath'] = $filepath;
				$fileinfo['filename'] = $filename.'.'.$filetype;
				$fileinfo['filesize'] = $fileinfo['size'];
				$fileinfo['filetype'] = $filetype;
				$this->_att_write($fileinfo);
				if($info && $this->isadmin){
					yzm_base::load_model('host', '', 0);
					$upload = new host();
					$res = $upload->deletefile($info);
					$res && D('attachment')->delete(array('id'=>$attid));
				}
				return_json(array('status' => 1, 'filepath' => $fileinfo['filepath'].$fileinfo['filename']));
			}else{
				return_json(array('status' => 0));
			}
		}

		$filepath = isset($_GET['f']) ? base64_decode($_GET['f']) : showmsg(L('lose_parameters'), 'stop');
		$attid = D('attachment')->field('id')->where(array('filename'=>basename($filepath)))->one();
		if(strpos($filepath, '://') !== false){
			$pos = strpos($filepath, SITE_PATH.'uploads');
			if($pos === false) return_message('非法的文件路径！', 0);
			$filepath = substr($filepath, $pos);
		}
		if(!$this->_resolve_image_path($filepath, $yzmcms_path)) return_message('请选择本地已存在的图像！', 0);
		$spec = isset($_GET['spec']) ? intval($_GET['spec']) : 1; 
		$cid = isset($_GET['cid']) ? htmlspecialchars($_GET['cid']) : 'thumb';
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
		if(!$info) return_json(array('status'=>0, 'message'=>'附件不存在！'));
		if(!$this->isadmin && $info['userid']!=$this->userid) return_json(array('status'=>0, 'message'=>'无权删除该文件！'));
		if(!$this->isadmin){
			if($info['userid']!=$this->userid) return_json(array('status'=>0, 'message'=>'无权删除该文件！'));
			$groupinfo = get_groupinfo($this->groupid);
			if(strpos($groupinfo['authority'], '5') === false) return_json(array('status'=>0, 'message'=>'你没有删除文件权限，请升级会员组！'));
		}

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
		$arr['originname'] = strlen($fileinfo['originname'])<50 ? htmlspecialchars($fileinfo['originname']) : htmlspecialchars(str_cut($fileinfo['originname'], 50));
		$arr['filename'] = $fileinfo['filename'];
		$arr['filepath'] = $fileinfo['filepath'];
		$arr['filesize'] = $fileinfo['filesize'];
		$arr['fileext'] = $fileinfo['filetype'];
		$arr['module'] = $fileinfo['module'];
		$arr['isimage'] = is_img($fileinfo['filetype']) ? 1 : 0;
		$arr['downloads'] = 0;
		$arr['userid'] = $this->userid;
		$arr['username'] = $this->username;
		$arr['uploadtime'] = SYS_TIME;
		$arr['uploadip'] = getip();
		return D('attachment')->insert($arr);
	}

	

	/**
	 * 校验并解析图片路径，限定在 uploads 目录内，防止路径遍历/任意文件读取
	 * @param string $filepath 相对路径或路径片段
	 * @param string $yzmcms_path 站点根前缀
	 * @return string|false 通过校验的真实路径
	 */
	private function _resolve_image_path($filepath, $yzmcms_path){
		if(!is_string($filepath) || $filepath === '') return false;
		if(strpos($filepath, "\0") !== false) return false;
		$filepath = str_replace('\\', '/', $filepath);
		if(!preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $filepath)) return false;
		$real = realpath($yzmcms_path.$filepath);
		if(!$real) return false;
		$upload_dir = realpath(YZMPHP_PATH.ltrim(C('upload_file'), '/'));
		if(!$upload_dir) return false;
		$real = str_replace('\\', '/', $real);
		$upload_dir = str_replace('\\', '/', $upload_dir);
		if($real !== $upload_dir && strpos($real, $upload_dir.'/') !== 0) return false;
		return $real;
	}


	/**
	 * 加载模板
	 */	
	public static function admin_tpl($file = 'undefined', $m = '') {
		$m = empty($m) ? ROUTE_M : $m;
		if(empty($m)) return false;
		return APP_PATH.$m.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$file.'.html';
	}

}