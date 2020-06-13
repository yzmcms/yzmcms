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


// YzmCMS获取附件上传类型
$upload_type = C('upload_type', 'host');
if($upload_type == 'host'){
    /* 生成上传实例对象并完成上传 */
    $up = new Uploader($fieldName, $config, $base64);

    $info = $up->getFileInfo();
    if($info['state'] == 'SUCCESS'){
        attachment_write($info);
        if(C('watermark_enable')){
            $img = new image(1,1);
            $img->watermark($document_root.$info['url']);       
        }
    }

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
    exit(json_encode($info));  //YzmCMS 修改写法
}else{

    // 上传第三方oss
    yzm_base::load_sys_class($upload_type, YZMPHP_PATH.'application/attachment/model', 0);
    if(!class_exists($upload_type)){
        $info = array(
            "state" => '附件上传类「'.$upload_type.'」不存在！',
        );
        exit(json_encode($info));
    }


    $option['allowtype'] = array_map('handle_suffix', $config['allowFiles']);
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
    }else{
        $info = array(
            "state" => $upload->geterrormsg()
        );
    }
    exit(json_encode($info));

}


// 处理扩展名后缀
function handle_suffix($type){
    return substr($type, 1);;
}


// 写入附件表
function attachment_write($info){
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
}

