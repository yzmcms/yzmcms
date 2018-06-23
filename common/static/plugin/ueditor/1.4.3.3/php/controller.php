<?php
//header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
//header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
date_default_timezone_set('PRC');  
error_reporting(E_ERROR);
header("Content-Type: text/html; charset=utf-8");

$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("config.json")), true);
$action = $_GET['action'];

//YzmCMS 新增 Start
define('IN_YZMCMS', true); 

$document_root = rtrim(str_replace('\\','/', $_SERVER['DOCUMENT_ROOT']), '/');
$web_path = str_replace('\\','/',dirname(dirname(__FILE__)));  
if(strpos($web_path, $document_root) !== false){
	$web_path = str_replace($document_root,'', $web_path);
	$web_path = str_replace('/common/static/plugin/ueditor/1.4.3.3','',$web_path).'/';
}else{
	//换一种方式获取安装目录
	$web_path = str_replace('\\','/',dirname(dirname($_SERVER['SCRIPT_FILENAME']))); 
	if(strpos($web_path, $document_root) !== false){
		$web_path = str_replace($document_root,'', $web_path);
		$web_path = str_replace('/common/static/plugin/ueditor/1.4.3.3','',$web_path).'/';
	}else{
		//如果还是获取不到安装目录，就设置安装目录为根目录（/）
		$web_path = '/';
	}
}
define('YZMPHP_PATH', $document_root.$web_path);

include YZMPHP_PATH.'yzmphp'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'image.class.php';

/**
 * 获取配置参数
 * @param string $key  要获取的配置荐
 * @param string $default  默认配置。当获取配置项目失败时该值发生作用。
 * @return mixed
 */
function C($key = '', $default = '') {
    static $configs = array(); 
	if (isset($configs['config'])) {
		if (empty($key)) {
			return $configs['config'];
		} elseif (isset($configs['config'][$key])) {
			return $configs['config'][$key];
		} else {
			return $default;
		}
	}
	$path = YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
	if (is_file($path)) { 
		$configs['config'] = include $path;
	}
	if (empty($key)) {
		return $configs['config'];
	} elseif (isset($configs['config'][$key])) {
		return $configs['config'][$key];
	} else {
		return $default;
	}
}

$web_upload = $web_path.'uploads';
$CONFIG['imagePathFormat'] = $web_upload.$CONFIG['imagePathFormat'];
$CONFIG['scrawlPathFormat'] = $web_upload.$CONFIG['scrawlPathFormat'];
$CONFIG['snapscreenPathFormat'] = $web_upload.$CONFIG['snapscreenPathFormat'];
$CONFIG['catcherPathFormat'] = $web_upload.$CONFIG['catcherPathFormat'];
$CONFIG['videoPathFormat'] = $web_upload.$CONFIG['videoPathFormat'];
$CONFIG['filePathFormat'] = $web_upload.$CONFIG['filePathFormat'];
$CONFIG['imageManagerListPath'] = $web_upload.$CONFIG['imageManagerListPath'];
$CONFIG['fileManagerListPath'] = $web_upload.$CONFIG['fileManagerListPath'];

//YzmCMS 新增 End

switch ($action) {
    case 'config':
        $result =  json_encode($CONFIG);
        break;

    /* 上传图片 */
    case 'uploadimage':
    /* 上传涂鸦 */
    case 'uploadscrawl':
    /* 上传视频 */
    case 'uploadvideo':
    /* 上传文件 */
    case 'uploadfile':
        $result = include("action_upload.php");
        break;

    /* 列出图片 */
    case 'listimage':
        $result = include("action_list.php");
        break;
    /* 列出文件 */
    case 'listfile':
        $result = include("action_list.php");
        break;

    /* 抓取远程文件 */
    case 'catchimage':
        $result = include("action_crawler.php");
        break;

    default:
        $result = json_encode(array(
            'state'=> '请求地址出错'
        ));
        break;
}

/* 输出结果 */
if (isset($_GET["callback"])) {
    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
    } else {
        echo json_encode(array(
            'state'=> 'callback参数不合法'
        ));
    }
} else {
    echo $result;
}