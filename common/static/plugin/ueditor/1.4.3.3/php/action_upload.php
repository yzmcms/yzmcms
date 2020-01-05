<?php
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
defined('IN_YZMCMS') or exit('Access Denied'); 
include "Uploader.class.php";

/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}

/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);

//YzmCMS 新增 文件入库及加水印
$info = $up->getFileInfo();
if($info['state'] == 'SUCCESS'){
    $pathinfo = pathinfo($info['url']);
    $param = yzm_base::load_sys_class('param');
    $arr = array();
    $arr['originname'] = strlen($info['original'])<50 ? htmlspecialchars($info['original']) : htmlspecialchars(str_cut($info['original'], 45));
    $arr['filename'] = htmlspecialchars($info['title']);
    $arr['filepath'] = $pathinfo['dirname'].'/';
    $arr['filesize'] = $info['size'];
    $arr['fileext'] = ltrim($info['type'], '.');
    $arr['module'] = $param->route_m();
    $arr['isimage'] = in_array($arr['fileext'], array('gif', 'jpg', 'png', 'jpeg')) ? 1 : 0;
    $arr['downloads'] = 0;
    $arr['userid'] = isset($_SESSION['adminid']) ? $_SESSION['adminid'] : (isset($_SESSION['_userid']) ? $_SESSION['_userid'] : 0);
    $arr['username'] = isset($_SESSION['adminname']) ? $_SESSION['adminname'] : (isset($_SESSION['_username']) ? $_SESSION['_username'] : '');
    $arr['uploadtime'] = time();
    $arr['uploadip'] = getip();
    D('attachment')->insert($arr);
	
	if(C('watermark_enable')){
		$img = new image(1,1);
		$img->watermark($document_root.$info['url']);		
	}
}
//YzmCMS 新增 文件入库及加水印

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */

/* 返回数据 */
//return json_encode($up->getFileInfo());  //原始写法
return json_encode($info);  //YzmCMS 修改写法
