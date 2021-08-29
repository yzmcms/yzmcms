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

class group extends wechat_common{
	
	
    /**
     *  分组列表
     */	
	public function init(){
		
		$wechat_group = D('wechat_group');
        $data = $wechat_group->select();
		
		//首先从本地数据库里查询，如果查询不到数据，则从微信服务器同步
		if(!$data){
			$data = $this->select_group();
			foreach($data as $val){
				$wechat_group->insert($val, false, false);
			}
		} 
	
		include $this->admin_tpl('group_list');
    }

	
    /**
     *  创建分组
     */	
	public function add(){
		
		if(isset($_POST['dosubmit'])) {
			$groupname = $_POST['groupname'];
			$url = 'https://api.weixin.qq.com/cgi-bin/groups/create?access_token='.$this->get_access_token();
			$str = '{"group":{"name":"'.$groupname.'"}}';

			$json_arr = https_request($url, $str);

			if(isset($json_arr['errcode'])){
				return_json(array('status'=>0,'message'=>'操作失败：'.$json_arr['errmsg']));
			}

			D('wechat_group')->insert($json_arr['group'], false, false);
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			include $this->admin_tpl('group_add');
		}
    }
	
	
    /**
     *  修改分组
     */	
	public function edit(){
        
		if(isset($_POST['dosubmit'])) {
			
			$groupid = intval($_POST['groupid']);
			$groupname = $_POST['groupname'];
			$url = 'https://api.weixin.qq.com/cgi-bin/groups/update?access_token='.$this->get_access_token();

			$str = '{"group":{"id":'.$groupid.',"name":"'.$groupname.'"}}';
			$json_arr = https_request($url, $str);

			if($json_arr['errcode'] == 0){
				D('wechat_group')->update(array('name' => $groupname), array('id' => $groupid), true);
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>'操作失败：'.$json_arr['errmsg']));
			}			
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = D('wechat_group')->where(array('id' => $id))->find();
			include $this->admin_tpl('group_edit');
		}
    }
	
	
    /**
     *  删除分组
     *  注意：删除分组后，该分组内的用户自动进入默认分组，从新创建分组则ID是自动增长的
     */	
	public function delete(){

		$groupid = intval($_GET['id']);
	
        $url = 'https://api.weixin.qq.com/cgi-bin/groups/delete?access_token='.$this->get_access_token();
        $str = '{"group":{"id":'.$groupid.'}}';

        $json_arr = https_request($url, $str);

		if($json_arr['errcode'] == 0){
			//查询该分组的人数
			$wechat_group = D('wechat_group');
			$data = $wechat_group->field('count')->where(array('id' => $groupid))->find();
			//如果该分组下有人数，则把该组下的人数添加到默认组
			if($data['count']){
				$wechat_group->update('`count` = `count`+'.$data['count'], array('id' => '0'));
			}
			//删除该分组
			$wechat_group->delete(array('id' => $groupid));
			showmsg(L('operation_success'), U('init'));
		}else{
			showmsg('操作失败！'.$json_arr['errmsg'], 'stop');
		}
		
    }
	
	
    /**
     *  查询所有分组
     */	
	public function select_group(){
		
        $url = 'https://api.weixin.qq.com/cgi-bin/groups/get?access_token='.$this->get_access_token();
        $json_arr = https_request($url);

		if(isset($json_arr['errcode'])){
			showmsg('查询分组失败！'.$json_arr['errmsg'], 'stop');
		}
		
		return $json_arr['groups'];
		
    }

}