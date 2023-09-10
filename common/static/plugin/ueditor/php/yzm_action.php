<?php
/**
 * UEditor编辑器整合YzmCMS处理
 * User: 袁志蒙
 * QQ: 214243830
 * Web: www.yzmcms.com
 * Date: 2021-03-30
 * Remarks: YZM-CMS All Rights Reserved.
 */

defined('IN_YZMCMS') or exit('Access Denied'); 

$document_root = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT']), '/');
$web_path = str_replace('\\','/',dirname(dirname(__FILE__)));  
if(strpos($web_path, $document_root) !== false){
	$web_path = str_replace($document_root,'', $web_path);
	$web_path = str_replace('/common/static/plugin/ueditor','',$web_path).'/';
}else{
	//换一种方式获取安装目录
	$web_path = str_replace('\\','/',dirname(dirname($_SERVER['SCRIPT_FILENAME']))); 
	if(strpos($web_path, $document_root) !== false){
		$web_path = str_replace($document_root,'', $web_path);
		$web_path = str_replace('/common/static/plugin/ueditor','',$web_path).'/';
	}else{
		//如果还是获取不到安装目录，就设置安装目录为根目录（/）
		$web_path = '/';
	}
}

define('APP_DEBUG', false);
define('URL_MODEL', '3');
if(version_compare(PHP_VERSION,'5.4.0','<')) {
    define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? true : false);
}else{
    define('MAGIC_QUOTES_GPC', false);
}
define('SYS_TIME', time());
define('SERVER_PORT', is_https() ? 'https://' : 'http://');
define('HTTP_HOST', (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''));
define('SITE_PATH', $web_path);
define('SITE_URL', SERVER_PORT.HTTP_HOST.SITE_PATH);
define('YZMPHP_PATH', stripos(PHP_OS, 'WIN')!==false ? str_replace('/', DIRECTORY_SEPARATOR, $document_root.$web_path) : $document_root.$web_path);
define('APP_PATH', YZMPHP_PATH.'application'.DIRECTORY_SEPARATOR);
define('EXT', '.class.php'); 

$web_upload = $web_path.'uploads';
$CONFIG['imagePathFormat'] = $web_upload.$CONFIG['imagePathFormat'];
$CONFIG['scrawlPathFormat'] = $web_upload.$CONFIG['scrawlPathFormat'];
$CONFIG['snapscreenPathFormat'] = $web_upload.$CONFIG['snapscreenPathFormat'];
$CONFIG['catcherPathFormat'] = $web_upload.$CONFIG['catcherPathFormat'];
$CONFIG['videoPathFormat'] = $web_upload.$CONFIG['videoPathFormat'];
$CONFIG['filePathFormat'] = $web_upload.$CONFIG['filePathFormat'];
$CONFIG['imageManagerListPath'] = $web_upload.$CONFIG['imageManagerListPath'];
$CONFIG['fileManagerListPath'] = $web_upload.$CONFIG['fileManagerListPath'];

class yzm_base {
        
    /**
     * 加载系统类方法
     * @param string $classname 类名
     * @param string $path 扩展地址
     * @param intger $initialize 是否初始化
     * @return object or true
     */
    public static function load_sys_class($classname, $path = '', $initialize = 1) {
        static $classes = array();
        if (empty($path)) $path = YZMPHP_PATH.'yzmphp'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'class';
        $key = md5($path.$classname);
        if (isset($classes[$key])) {
            return $initialize&&!is_object($classes[$key]) ? new $classname : $classes[$key];
        }
        if (!is_file($path.DIRECTORY_SEPARATOR.$classname.EXT)) {
            return false;
        }
        
        include $path.DIRECTORY_SEPARATOR.$classname.EXT; 
        if ($initialize) {
            $classes[$key] = new $classname;
        } else {
            $classes[$key] = true;
        }
        return $classes[$key];
    }


    /**
     * 加载系统的函数库
     * @param string $func 函数库名
     */
    public static function load_sys_func($func) {
        if (is_file(YZMPHP_PATH.'yzmphp'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.$func.'.func.php')) {
            include YZMPHP_PATH.'yzmphp'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'function'.DIRECTORY_SEPARATOR.$func.'.func.php';
        }
    }


    /**
     * 加载common目录下的文件
     * @param string $path 文件地址（包括文件全称）
     * @param string $m 模块(如果模块名为空，则加载根目录下的common)
     */
    public static function load_common($path, $m = '') {
        if(!$m){
            if (is_file(YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.$path)) {
                return include YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.$path;
            }           
        }else{
            if (is_file(APP_PATH.$m.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.$path)) {
                return include APP_PATH.$m.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.$path;
            }           
        }
    }

}

yzm_base::load_sys_class('debug', '', 0);
yzm_base::load_sys_class('image', '', 0);
yzm_base::load_sys_func('global');
yzm_base::load_common('function/system.func.php');
new_session_start();

if(!isset($_SESSION['adminid']) && !isset($_SESSION['_userid'])){
    exit(json_encode(array('state'=> '请登录后再继续操作！')));
}
if(isset($_SESSION['roleid'])) define('IN_YZMADMIN', true);


/**
 * 处理扩展名后缀
 * @param  string $str 
 */
function handle_suffix($str, $add=1){
    if($add){
        return $str ? '.'.$str : '';
    }else{
        return $str ? ltrim($str, '.') : '';
    }
}


/**
 * 判断是否SSL协议
 * @return boolean
 */
function is_https() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }elseif(isset($_SERVER['REQUEST_SCHEME']) && ('https' == strtolower($_SERVER['REQUEST_SCHEME']))) {
        return true;
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ('https' == strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']))) {
        return true;
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_SCHEME']) && ('https' == strtolower($_SERVER['HTTP_X_FORWARDED_SCHEME']))) {
        return true;
    }
    return false;
}


/**
 * 写入系统附件表
 * @param  array $info 
 */
function attachment_write($info){
    $pathinfo = pathinfo($info['url']);
    $param = yzm_base::load_sys_class('param');
    $arr = array();
    $arr['siteid'] = get_siteid();
    $arr['originname'] = strlen($info['original'])<50 ? htmlspecialchars($info['original']) : htmlspecialchars(str_cut($info['original'], 50));
    $arr['filename'] = htmlspecialchars($info['title']);
    $arr['filepath'] = $pathinfo['dirname'].'/';
    $arr['filesize'] = $info['size'];
    $arr['fileext'] = ltrim($info['type'], '.');
    $arr['module'] = $param->route_m();
    $arr['isimage'] = is_img($arr['fileext']) ? 1 : 0;
    $arr['downloads'] = 0;
    $arr['userid'] = isset($_SESSION['adminid']) ? $_SESSION['adminid'] : (isset($_SESSION['_userid']) ? $_SESSION['_userid'] : 0);
    $arr['username'] = isset($_SESSION['adminname']) ? $_SESSION['adminname'] : (isset($_SESSION['_username']) ? $_SESSION['_username'] : '');
    $arr['uploadtime'] = SYS_TIME;
    $arr['uploadip'] = getip();
    $id = D('attachment')->insert($arr);
    if(get_config('att_relation_content')) attachment_content($id);
}


/**
 * 附件关联内容
 */
function attachment_content($id){
    if(defined('IN_YZMADMIN')){
        $attachmentid = isset($_SESSION['attachmentid']) ? $_SESSION['attachmentid'].'|'.$id : $id;
        $_SESSION['attachmentid'] = $attachmentid;
    }else{
        $attachmentid = get_cookie('attachmentid');
        $attachmentid = $attachmentid ? $attachmentid.'|'.$id : $id;
        set_cookie('attachmentid', $attachmentid);
    }
    return true;
}


/**
 * 文件上传处理
 */
function ue_file_upload($fieldName, $config, $base64, $document_root){
	$upload_type = C('upload_type', 'host');
	if($upload_type == 'host'){
	    $upload = new Uploader($fieldName, $config, $base64);

	    $info = $upload->getFileInfo();
	    if($info['state'] == 'SUCCESS'){
	        attachment_write($info);
	        if(C('watermark_enable')){
	            $img = new image(1, 1);
	            $img->watermark($document_root.$info['url']);       
	        }
	    }
	    return json_encode($info); 

	}else{

	    yzm_base::load_sys_class($upload_type, YZMPHP_PATH.'application/attachment/model', 0);
	    if(!class_exists($upload_type)){
	        $info = array(
	            "state" => '附件上传类「'.$upload_type.'」不存在！',
	        );
	        return json_encode($info);
	    }

        $option['allowtype'] = array_map('handle_suffix', $config['allowFiles'],  array());
	    $upload = new $upload_type($option);
	    if($upload->uploadfile($fieldName)){
	        $fileinfo = $upload->getnewfileinfo();
	        $info = array(
	            "state" => 'SUCCESS',
	            "url" => $fileinfo['filepath'].$fileinfo['filename'],
	            "title" => $fileinfo['filename'],
	            "original" => $fileinfo['originname'],
	            "type" => '.'.$fileinfo['filetype'],
	            "size" => $fileinfo['filesize'],
	        );
	        attachment_write($info);
            if(C('watermark_enable') && isset($fileinfo['watermark'])){
                $img = new image(1, 1);
                $img->watermark($document_root.$fileinfo['watermark']);       
            }
	    }else{
	        $info = array(
	            "state" => $upload->geterrormsg()
	        );
	    }
	    return json_encode($info);

	}

}