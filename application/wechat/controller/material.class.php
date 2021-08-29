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
yzm_base::load_controller('wechat_common', 'wechat', 0);
yzm_base::load_sys_class('page','',0);

class material extends wechat_common{
	
	
    /**
     *  素材列表
     */	
	public function init(){
		$types = array('image'=>'图片','voice'=>'语音','video'=>'视频','thumb'=>'缩略图','news'=>'图文');
		$wechat_media = D('wechat_media');
        $total = $wechat_media->total();
		$page = new page($total, 15);
		$data = $wechat_media->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('media_list');
    }


	/**
	 * 素材搜索
	 */
	public function search() {
		$types = array('image'=>'图片','voice'=>'语音','video'=>'视频','thumb'=>'缩略图','news'=>'图文');
		$wechat_media = D('wechat_media');
		$where = '1=1';
		if(isset($_GET['dosubmit'])){	
			$media_type = isset($_GET["media_type"]) ? intval($_GET["media_type"]) : 99;
			$type = isset($_GET["type"]) ? safe_replace($_GET["type"]) : '';
			$media_id = isset($_GET["media_id"]) ? safe_replace($_GET["media_id"]) : '';
			
			if($media_type != 99) {
				$where .= ' AND media_type = '.$media_type;
			}
			
			if($type) {
				$where .= ' AND type = "'.$type.'"';
			}	

			if($media_id) {
				$where .= ' AND media_id = "'.$media_id.'"';
			}			
		}
		$_GET = array_map('htmlspecialchars', $_GET);
        $total = $wechat_media->where($where)->total();
		$page = new page($total, 15);
		$data = $wechat_media->where($where)->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('media_list');
	}
	
	
	
    /**
     *  添加素材
     */	
	public function add(){
		if(isset($_POST['dosubmit'])) {
			
			$type = $_POST['type'];
			
			$option = array();
			if($type == 'image'){
				$option['allowtype'] = array('gif', 'jpg', 'png', 'jpeg');
			}elseif($type == 'voice'){
				$option['allowtype'] = array('amr','mp3');
			}elseif($type == 'video'){
				$option['allowtype'] = array('mp4');
			}else{
				$option['allowtype'] = array('jpg');
			}
			
			yzm_base::load_sys_class('upload','',0);
			$upload = new upload($option);
			if($upload->uploadfile('filename')){
				$fileinfo = $upload->getnewfileinfo();
			}else{
				showmsg($upload->geterrormsg(), 'stop');
			} 	
			
			//上传公众号一定要使用绝对路径
			$filepath = str_replace('\\', '/', rtrim(YZMPHP_PATH, DIRECTORY_SEPARATOR)).$fileinfo['filepath'].$fileinfo['filename'];
			$data = array('media' => '@'.$filepath);
			
			if($_POST['media_type']){
				$url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$this->get_access_token().'&type='.$type;  //永久素材
			}else{
				$url = 'http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->get_access_token().'&type='.$type;
			}
			
			$json_arr = https_request($url, $data);
			
			if(!isset($json_arr['errcode'])){

				if($type == 'thumb') {
					$json_arr['media_id'] = isset($json_arr['media_id']) ? $json_arr['media_id'] : $json_arr['thumb_media_id'];
				}
				
				$json_arr['originname'] = $fileinfo['originname'];
				$json_arr['filename'] = $fileinfo['filename'];
				$json_arr['filepath'] = $fileinfo['filepath'];
				$json_arr['type'] = $type;
				$json_arr['media_type'] = $_POST['media_type'];
				if($_POST['media_type']) $json_arr['created_at'] = SYS_TIME;
				
				D('wechat_media')->insert($json_arr);
				showmsg(L('operation_success'), U('init'), 1);
			}else{
				showmsg('操作失败！errcode：'.$json_arr['errcode'].'，errmsg：'.$json_arr['errmsg'], 'stop');
			}
			
		}else{
			include $this->admin_tpl('media_add');
		}
    }


    /**
     *  添加图文素材
     */	
	public function add_news(){
		if(isset($_POST['dosubmit'])) {
			$article = '';
			for($i=0; $i<$_POST['num']; $i++) {
				$article .= '{"thumb_media_id":"'.$_POST["thumb_media_id_{$i}"].'","author":"'.$_POST["author_{$i}"].'",
				 "title":"'.$_POST["title_{$i}"].'","content_source_url":"'.$_POST["content_source_url_{$i}"].'","content":"'.$_POST["content_{$i}"].'",
				 "digest":"'.$_POST["digest_{$i}"].'","show_cover_pic":"'.$_POST["show_cover_pic_{$i}"].'"},';
			}
					
			$data = '{"articles": ['.rtrim($article, ',').']}';

			if($_POST['media_type']){
				$url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token='.$this->get_access_token();  //永久素材
			}else{
				$url = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token='.$this->get_access_token();
			}
			
			$json_arr = https_request($url, $data);
			
			if(!isset($json_arr['errcode'])){
				D('wechat_media')->insert(array('type'=>'news', 'url'=>$_POST['title_0'], 'media_type'=>$_POST['media_type'], 'created_at'=>SYS_TIME, 'media_id'=>$json_arr['media_id']));
				showmsg(L('operation_success'), U('init'), 1);
			}else{
				showmsg('操作失败！errcode：'.$json_arr['errcode'].'，errmsg：'.$json_arr['errmsg'], 'stop');
			}

		}else{
			$num = isset($_GET['num']) ? intval($_GET['num']) : 1;
			include $this->admin_tpl('media_add_news');
		}
    }
	
	
	/**
	 * 删除素材
	 */	
	public function delete(){ 
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$media_type = isset($_GET['media_type']) ? intval($_GET['media_type']) : 0;
		$wechat_media = D('wechat_media');
		$info = $wechat_media->field('media_id,filepath,filename')->where(array('id'=>$id))->find();
		
		//如果是永久素材，则删除永久素材
		if($media_type){
			$url = 'https://api.weixin.qq.com/cgi-bin/material/del_material?access_token='.$this->get_access_token();
			$data = '{"media_id":"'.$info['media_id'].'"}';
			$json_arr = https_request($url, $data);	
			if($json_arr['errcode'] != 0) showmsg('操作失败！errcode：'.$json_arr['errcode'].'，errmsg：'.$json_arr['errmsg'], 'stop');				
		}

		$file = YZMPHP_PATH.strstr($info['filepath'].$info['filename'], C('upload_file'));
		if(is_file($file)) @unlink($file);
		
		if($wechat_media->delete(array('id' => $id))){
			showmsg(L('operation_success'), U('init'), 1);
		}else{
			showmsg(L('operation_failure'));
		}
	}
	

	/**
	 * 选择缩略图
	 */	
	public function select_thumb(){
		$thumb_media_id = $_GET['thumb_media_id'];
		$types = array('image'=>'图片','voice'=>'语音','video'=>'视频','thumb'=>'缩略图','news'=>'图文');
		$wechat_media = D('wechat_media');
		$where = 'type = "image" OR type = "thumb"';
		$total = $wechat_media->where($where)->total();
		$page = new page($total, 8);
		$data = $wechat_media->field('type, originname, filename, filepath, media_id, created_at, media_type')->where($where)->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('select_thumb');
	}
	
	
	/**
	 * 获取永久素材列表
	 */	
	public function get_material_list(){ 
		$type = 'image';  //图片: image 视频: video 语音: voice 图文: news
		$url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$this->get_access_token();
		$data = '{"type":"'.$type.'","offset":"0","count":"20"}';
		$json_arr = https_request($url, $data);
		P($json_arr);
	}

}