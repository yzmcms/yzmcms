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

class scan extends wechat_common{
	
	
    /**
     *  场景列表
     */	
	public function init(){
		$wechat_scan = D('wechat_scan');
        $total = $wechat_scan->total();
		$page = new page($total, 15);
		$data = $wechat_scan->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('scan_list');
    }



    /**
     *  添加场景
     */	
	public function add(){
		if(isset($_POST['dosubmit'])) {
			$scan = $_POST['scan'] = trim($_POST['scan']);
			$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->get_access_token();
			if($_POST['type']){
				//临时二维码
				$json_str = '{"expire_seconds": '.$_POST['expire_time'].', "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$scan.'"}}}';
			}else{
				unset($_POST['expire_time']);
				$json_str = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$scan.'"}}}';
			}
			
			$json_arr = https_request($url, $json_str);
			if(!isset($json_arr['errcode'])){
				$_POST['ticket'] = $json_arr['ticket'];
				D('wechat_scan')->insert($_POST, true);
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>'操作失败：'.$json_arr['errmsg']));
			}
		}else{
			include $this->admin_tpl('scan_add');
		}
    }

	
	
    /**
     *  编辑场景
     */	
	public function edit(){
		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$scan = $_POST['scan'] = trim($_POST['scan']);
			$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->get_access_token();
			if($_POST['type']){
				//临时二维码
				$json_str = '{"expire_seconds": '.$_POST['expire_time'].', "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$scan.'"}}}';
			}else{
				unset($_POST['expire_time']);
				$json_str = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$scan.'"}}}';
			}
			
			$json_arr = https_request($url, $json_str);
			if(!isset($json_arr['errcode'])){
				$_POST['ticket'] = $json_arr['ticket'];
				D('wechat_scan')->update($_POST, array('id' => $id), true);
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>'操作失败：'.$json_arr['errmsg']));
			}
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = D('wechat_scan')->where(array('id' => $id))->find();
			include $this->admin_tpl('scan_edit');
		}
    }
	
	
	/**
	 * 删除场景
	 */	
	public function del(){ 
		if($_POST && is_array($_POST['ids'])){
			$wechat_scan = D('wechat_scan');
			foreach($_POST['ids'] as $val){
				$wechat_scan->delete(array('id'=>$val));
			}
			showmsg(L('operation_success'),'',1);
		}
	}

}