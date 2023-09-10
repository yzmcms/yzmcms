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

class menu extends wechat_common{
	
	
    /**
     *  菜单列表
     */	
	public function init(){
		$wechat_menu = D('wechat_menu');
        $data = $wechat_menu->where(array('parentid' => 0))->order('listorder ASC')->limit('3')->select();
		include $this->admin_tpl('menu_list');
    }

	
    /**
     *  添加菜单
     */	
	public function add(){
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(isset($_POST['dosubmit'])) {
			$parentid = intval($_POST['parentid']);
			$wechat_menu = D('wechat_menu');
			if($parentid){
				$total = $wechat_menu->where(array('parentid' => $parentid))->total();
				if($total >= 5) return_json(array('status'=>0,'message'=>'每个一级菜单最多包含5个二级菜单！'));	
				
				if($_POST['type'] == 1 && $_POST['keyword'] == '') return_json(array('status'=>0,'message'=>'菜单KEY值不能为空！'));	
				if($_POST['type'] == 2 && $_POST['url'] == '') return_json(array('status'=>0,'message'=>'URL跳转地址不能为空！'));	
			}else{
				$total = $wechat_menu->where(array('parentid' => 0))->total();
				if($total >= 3) return_json(array('status'=>0,'message'=>'一级菜单最多3个！'));	
			}
			$_POST['event'] = $_POST['type']==1 ? 'click' : 'view';
			$wechat_menu->insert($_POST, true);
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			$data = D('wechat_menu')->where(array('parentid' => 0))->order('listorder ASC')->limit('3')->select();
			include $this->admin_tpl('menu_add');
		}
    }
	
	
    /**
     *  编辑菜单
     */	
	public function edit(){
        
		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$wechat_menu = D('wechat_menu');
			$_POST['event'] = $_POST['type']==1 ? 'click' : 'view';
			$wechat_menu->update($_POST, array('id' => $id), true);
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = D('wechat_menu')->where(array('id' => $id))->find();
			$res = D('wechat_menu')->where(array('parentid' => 0))->order('listorder ASC')->limit('3')->select();
			include $this->admin_tpl('menu_edit');
		}
    }
	
	
    /**
     *  删除菜单
     */	
	public function delete(){
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(D('wechat_menu')->delete(array('id'=>$id))){
			 showmsg(L('operation_success'),'',1);
		}else{
			 showmsg(L('operation_failure'));
		}
    }
	
	
	/**
	 * 菜单排序
	 */
	public function order() {
		if(isset($_POST['id']) && is_array($_POST['id'])){
			$wechat_menu = D('wechat_menu');
			foreach($_POST['id'] as $key=>$val){
				$wechat_menu->update(array('listorder'=>$_POST['listorder'][$key]),array('id'=>intval($val)));
			}
		}
		showmsg(L('operation_success'), '', 1);
	}
	

    /**
     *  创建菜单
     */	
	public function create_menu(){
		$wechat_menu = D('wechat_menu');
		$arr = array();
		$arr['button'] = $wechat_menu->field('event AS `type`,keyword AS `key`,id,name,url')->where(array('parentid' => 0))->order('listorder ASC')->limit('3')->select();
		
		foreach($arr['button'] as $key=>$val){
			if($val['type'] == 'click'){
				unset($arr['button'][$key]['url']);
			}else{
				unset($arr['button'][$key]['key']);
			}
			$r = $wechat_menu->field('event AS `type`,keyword AS `key`,name,url')->where(array('parentid' => $val['id']))->order('listorder ASC,id DESC')->limit('5')->select();
			if($r){
				foreach($r as $k=>$v){
					if($v['type'] == 'click'){
						unset($r[$k]['url']);
					}else{
						unset($r[$k]['key']);
					}					
				}
				$arr['button'][$key]['sub_button'] = $r;
			}
			unset($arr['button'][$key]['id']);
		}
		
		$str = $this->my_json_encode($arr);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->get_access_token();
        $json_arr = https_request($url, $str);

		if($json_arr['errcode'] == 0){
			return_json(array('status'=>1,'message'=>'操作成功，请清除微信端缓存后查看！'));
		}else{
			return_json(array('status'=>0,'message'=>L('operation_failure').$json_arr['errmsg']));
		}
    }



    /**
     *  查询菜单
     */	
	public function select_menu(){
 
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.$this->get_access_token();
        $json_arr = https_request($url);
		
		P($json_arr);
    }
	
	
	
    /**
     *  删除所有菜单提交微信
     */	
	public function delete_menu(){
 
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$this->get_access_token();
        $json_arr = https_request($url);
		
		if($json_arr['errcode'] == 0){
			D('wechat_menu')->delete(array('1' => 1));
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			return_json(array('status'=>0,'message'=>L('operation_failure').$json_arr['errmsg']));
		}
		
    }

}