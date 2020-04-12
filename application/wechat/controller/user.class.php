<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('wechat_common', 'wechat', 0);
yzm_base::load_sys_class('page','',0);

class user extends wechat_common{
	
    /**
     *  关注者列表
     */
    public function init(){

    	$of = input('get.of');
    	$or = input('get.or');
    	$of = in_array($of, array('wechatid','openid','groupid','sex','subscribe','subscribe_time')) ? $of : 'wechatid';
    	$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';

		//微信分组
		$groupid = 99;
        $wechat_group = D('wechat_group')->select(); 
		
		$wechat_user = D('wechat_user');
		$total = $wechat_user->total();
		$page = new page($total, 15);
		$data = $wechat_user->order("$of $or")->limit($page->limit())->select();
		include $this->admin_tpl('user_list');
    }
	
	
	/**
	 * 关注者搜索
	 */
	public function search() {
		$wechat_user = D('wechat_user');
		$wechat_group = D('wechat_group')->select();
		$where = '1=1';
		if(isset($_GET['dosubmit'])){	
			$status = isset($_GET["status"]) ? intval($_GET["status"]) : 99;
			$groupid = isset($_GET["groupid"]) ? intval($_GET["groupid"]) : 99;
			$searinfo = isset($_GET["searinfo"]) ? safe_replace($_GET["searinfo"]) : '';
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;
			
			if($searinfo != ''){
				if($type == '1')
					$where .= ' AND wechatid = \''.$searinfo.'\'';
				elseif($type == '2')
					$where .= ' AND scan = \''.$searinfo.'\'';
				else
					$where .= ' AND nickname LIKE \'%'.$searinfo.'%\'';
			}
			
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where .= " AND `subscribe_time` >= '".strtotime($_GET['start'])."' AND `subscribe_time` <= '".strtotime($_GET['end'])."' ";
			}
			
			if($status != 99) {
				$where .= ' AND subscribe = '.$status;
			}
			
			if($groupid != 99) {
				$where .= ' AND groupid = '.$groupid;
			}			
		}
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $wechat_user->where($where)->total();
		$page = new page($total, 15);
		$data = $wechat_user->where($where)->order('wechatid DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('user_list');
	}
	

    /**
     *  获取分组名称
     */	
	public function get_groupname($wechat_group, $groupid){
		$arr = array();
        foreach($wechat_group as $val){
			$arr[$val['id']] = $val['name'];
		}
		
		return $arr[$groupid];
    }
	
	
	
	/**
     *  同步微信服务器用户
     *  注意：单次拉取最多10000个关注者的OpenID，当关注者超过10000时，可通过填写next_openid的值获取
     */	
	public function synchronization(){
		$next_openid = '';
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->get_access_token().'&next_openid='.$next_openid;
        $json_arr = $this->https_request($url);

		if(isset($json_arr['errcode'])){
			showmsg('获取关注者列表失败！'.$json_arr['errmsg'], 'stop');
		}
		
		$wechat_user = D('wechat_user');
		
		//为了避免openid重复导致的报错，先删除本地所有的用户信息
		$wechat_user->delete(array('1' => 1));
		
		foreach($json_arr['data']['openid'] as $val){
			$info = $this->get_userinfo($val);
			$wechat_user->insert($info);
		}
		
		echo '<script type="text/javascript">function myclose(){window.parent.location.reload();} setTimeout("myclose()",2500);</script>';
		showmsg('远程同步成功！3秒自动关闭');
		
    }
	
	
	
    /**
     *  批量移动用户分组
     */	
	public function move_user_group(){

		if(isset($_POST['dosubmit'])){
			//要移动的openid的列表
			$arr = explode(',', $_POST['openids']);
			//要移动到的groupid
			$to_groupid = $_POST['to_groupid'];
			$str = '"'.join('","', $arr).'"';
			$url = 'https://api.weixin.qq.com/cgi-bin/groups/members/batchupdate?access_token='.$this->get_access_token();
			$str = '{"openid_list":['.$str.'],"to_groupid":'.$to_groupid.'}';
			$json_arr = $this->https_request($url, $str);

			if($json_arr['errcode'] == 0){
				D('wechat_user')->update(array('groupid' => $to_groupid), 'wechatid IN ('.$_POST['ids'].')');
				$wechat_group = D('wechat_group');
				$wechat_group->delete(array('1' => 1));
				$url = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token='.$this->get_access_token();
				$json_arr = $this->https_request($url);
				foreach($json_arr['groups'] as $val){
					$wechat_group->insert($val, false, false);
				}				
				showmsg(L('operation_success'), U('init'), 1);
			}else{
				showmsg('操作失败！'.$json_arr['errmsg'], 'stop');
			}			
		}else{
			$openids = is_array($_POST['ids']) ? join(',', $_POST['ids']) : showmsg(L('lose_parameters'), 'stop');
			$ids = join(',', array_keys($_POST['ids']));
			$wechat_group = D('wechat_group')->select(); 
			include $this->admin_tpl('user_group_remove');	
		}
    }
	
	
	
    /**
     *  设置用户备注
     */	
	public function set_userremark(){
		
		if(isset($_POST['dosubmit'])){
			$openid = $_POST['openid'];
			$remark = $_POST['remark'];
			$url = 'https://api.weixin.qq.com/cgi-bin/user/info/updateremark?access_token='.$this->get_access_token();
			$arr = array('openid' => $openid, 'remark' => $remark);
			$json_str = $this->my_json_encode($arr);
			$json_arr = $this->https_request($url, $json_str);

			if($json_arr['errcode'] == 0){
				D('wechat_user')->update(array('remark' => $remark), array('openid' => $openid));
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>'操作失败：'.$json_arr['errmsg']));
			}
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = D('wechat_user')->field('openid, nickname, remark')->where(array('wechatid' => $id))->find();
			include $this->admin_tpl('user_set_userremark');	
		}
    }
	
	
	
    /**
     *  查询用户所在组
     *  return groupid
     */	
	public function select_user_group(){
		
		//$openid = $_GET['openid'];
		$openid = 'oPfVu1HljS2Dg3RAbjtsjG_QpYKM';
        $url = 'https://api.weixin.qq.com/cgi-bin/groups/getid?access_token='.$this->get_access_token();

        $str = '{"openid":"'.$openid.'"}';
        $json_arr = $this->https_request($url, $str);

		if(isset($json_arr['errcode'])){
			showmsg('查询用户所在组失败！'.$json_arr['errmsg'], 'stop');
		}
		
		P($json_arr);
		
    }

	
    /**
     *  获取用户基本信息
     */	
	private function get_userinfo($openid){
		
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->get_access_token().'&openid='.$openid.'&lang=zh_CN';
        $json_arr = $this->https_request($url);

		if(isset($json_arr['errcode'])){
			showmsg('获取用户信息失败！'.$json_arr['errmsg'], 'stop');
		}
		
		return $json_arr;
    }

}