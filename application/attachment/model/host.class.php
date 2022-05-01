<?php
/**
 * YzmCMS本地上传文件操作类 - 商业用途请购买官方授权
 * @license          http://www.yzmcms.com
 * @lastmodify       2020-05-15
 */

defined('IN_YZMPHP') or exit('Access Denied');
yzm_base::load_sys_class('upload','',0);

class host{

	private $upload;
	
	/**
	 * 构造函数，用于对上传文件初使化
	 * @param array $option
	 */
	public function __construct($option = array()){
		$this->upload = new upload($option);
	}


	/**
	 * 用来上传一个文件
	 * @param string $filefield 上传文件的name名称
	 * @return boolean
	 */
	public function uploadfile($filename){
		return $this->upload->uploadfile($filename);
	}


	/**
	 * 用于获取上传后文件的信息
	 */
	public function getnewfileinfo(){
		return $this->upload->getnewfileinfo();
	}


	/**
	 * 上传如果失败，则调用这个方法，就可以查看错误报告
	 */
	public function geterrormsg() {
		return $this->upload->geterrormsg();
	}


	/**
	 * 删除文件
	 */
	public function deletefile($info) {
		$file = YZMPHP_PATH.strstr($info['filepath'].$info['filename'], C('upload_file'));
		if(is_file($file)) @unlink($file);
		return true;
	}
}