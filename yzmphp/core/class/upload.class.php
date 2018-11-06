<?php
/**
 * upload.class.php	 文件上传类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-10-09
 */

class upload {
	
	private $filepath = './uploads/';     //指定上传文件保存的路径
	private $allowtype = array('gif', 'jpg', 'png', 'jpeg');  //充许上传文件的类型
	private $maxsize = 2097152;  //允上传文件的最大值 2M
	private $israndname = true;  //是否随机重命名， true随机， false不随机，使用原文件名
	private $originname;   //源文件名称
	private $tmpfilename;  //临时文件名
	private $filetype;  //文件类型
	private $filesize;  //文件大小
	private $newfilename; //新文件名
	private $errornum = 0;  //错误号
	private $errormess = ''; //用来提供错误报告


	/**
	 * 构造函数，用于对上传文件初使化
	 * @param array $options
	 * 1. 指定上传路径， 2，充许的类型， 3，限制大小， 4，是否使用随机文件名称
	 * 让用户可以不用按位置传参数，后面参数给值不用将前几个参数也提供值
	 */
	function __construct($options = array()){		
		foreach($options as $key=>$val){
			$key=strtolower($key);
			//查看用户参数中数组的下标是否和成员属性名相同
			if(!in_array($key,get_class_vars(get_class($this)))){
				continue;
			}
			$this->setoption($key, $val);
		}
		$this->setoption('filepath', YZMPHP_PATH.C('upload_file').'/'.date('Ym/d'));
		$this->setoption('maxsize', get_config('upload_maxsize')*1024);
	}



	private function geterror(){
		$str = '上传文件<span style="color:red;margin:0 3px">'.$this->originname.'</span>时出错：';
		switch($this->errornum){
			case 4: $str .= '没有文件被上传'; break;
			case 3: $str .= '文件只被部分上传'; break;
			case 2: $str .= '上传文件超过了HTML表单中max_file_size选项指定的值'; break;
			case 1: $str .= '上传文件超过了php.ini 中upload_max_FILESize选项的值'; break;
			case -1: $str .= '末充许的类型'; break;
			case -2: $str .= '文件过大，上传文件不能超过'.$this->maxsize.'个字节'; break;
			case -3: $str .= '上传失败'; break;
			case -4: $str .= '建立存放上传文件目录失败，请重新指定上传目录'; break;
			case -5: $str .= '必须指定上传文件的路径'; break;
			default: $str .= '末知错误';
		}
		return $str;
	}
	
	
	/**
	 * 用来检查文件上传路径
	 */
	private function checkfilepath(){
		if(empty($this->filepath)) {
			$this->setoption('errornum', -5);
			return false;
		}

		if(!is_dir($this->filepath) || !is_writable($this->filepath)){
			if(!@mkdir($this->filepath, 0755,true)){
				$this->setoption('errornum', -4);
				return false;
			}
		}
		return true;
	}
	
	
	/**
	 * 用来检查文件上传的大小
	 */
	private function checkfilesize() {
		if($this->filesize > $this->maxsize){
			$this->setoption('errornum', '-2');
			return false;
		}else{
			return true;
		}
	}

	
	/**
	 * 用于检查文件上传类型
	 */
	private function checkfiletype() {
		if(in_array(strtolower($this->filetype), $this->allowtype)) {
			return true;
		}else{
			$this->setoption('errornum', -1);
			return false;
		}
	}
	
	
	/**
	 * 设置上传后的文件名称
	 */
	private function setnewfilename(){
		if($this->israndname){
			$this->setoption('newfilename', $this->prorandname());
		} else {
			$this->setoption('newfilename', $this->originname);
		}
	}

	
	/**
	 * 设置随机文件名称
	 */
	private function prorandname(){
		$filename = date("ymdhis").rand(100,999);
		return $filename.'.'.$this->filetype;
	}

	
	private function setoption($key, $val){
		$this->$key = $val;
	}
	
	
	/**
	 * 用来上传一个文件
	 * @param string $filefield 上传文件的name名称
	 * @return boolean
	 */
	function uploadfile($filefield){
		$return = true;
		//检查文件上传路径
		if(!$this->checkfilepath()){
			$this->errormess = $this->geterror();
			return false;
		}

	    if(empty($_FILES)){
			$this->errormess = 'files undefined';
			return false;
		} 
		
		$name = $_FILES[$filefield]['name'];
		$tmp_name = $_FILES[$filefield]['tmp_name'];
		$size = $_FILES[$filefield]['size'];
		$error = $_FILES[$filefield]['error'];

		if($this->setfiles($name, $tmp_name, $size, $error)){
			if($this->checkfiletype() && $this->checkfilesize()){
				$this->setnewfilename();
				if($this->copyfile()){
					return true;
				}else{
					$return = false;
				}					
			}else{
				$return = false;
			}	
		}else{
			$return = false;
		}

		if(!$return)
			$this->errormess = $this->geterror();

		return $return;
		
	}

	
	private function copyfile(){
		if(!$this->errornum){
			$filepath = rtrim($this->filepath, '/').'/';
			$filepath .= $this->newfilename;

			if(@move_uploaded_file($this->tmpfilename, $filepath))	{
				return true;
			}else{
				$this->setoption('errornum', -3);
				return false;
			}
				
		}else{
			return false;
		}
	}

	
	/**
	 * 设置和$_FILES有关的内容
	 */
	private function setfiles($name = '', $tmp_name = '', $size = 0, $error = 0){	
		$this->setoption('errornum', $error);			
		if($error){
			return false;
		}
		$this->setoption('originname', $name);
		$this->setoption('tmpfilename', $tmp_name);
		$arrstr = explode('.', $name); 
		$this->setoption('filetype', strtolower($arrstr[count($arrstr)-1]));
		$this->setoption('filesize', $size);	
		return true;
	}	

	
	/**
	 * 用于获取上传后文件的信息
	 */
	public function getnewfileinfo(){
		$arr = array(
			'filesize' => $this->filesize,
			'filetype' => $this->filetype,
			'filepath' => SITE_PATH.C('upload_file').'/'.date('Ym/d/'),
			'fileurl' => SITE_PATH.C('upload_file').'/'.date('Ym/d/'),
			'filename' => $this->newfilename,
			'originname' => $this->originname,
		);
		return $arr;
	}
	
	
	/**
	 * 用于获取上传后文件的文件名
	 * @param boolean $file_path 是否带文件路径
	 */
	public function getnewfilename($file_path = true){
		if($file_path)
			return SITE_PATH.C('upload_file').'/'.date('Ym/d/').$this->newfilename;
		else
			return $this->newfilename;
	}
	
	
	/**
	 * 上传如果失败，则调用这个方法，就可以查看错误报告
	 */
	public function geterrormsg() {
		return $this->errormess;
	}
}
