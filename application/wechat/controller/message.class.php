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

class message extends wechat_common{
	
	
    /**
     *  消息列表
     */	
	public function init(){
		
		$wechat_message = D('wechat_message');
        $total = $wechat_message->where(array('issystem' => 0))->total();
		$page = new page($total, 15);
		$data = $wechat_message->where(array('issystem' => 0))->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('message_list');
    }


	
	/**
     * 客服接口 给指定用户发送信息
     * 注意：微信规则只允许给在48小时内给公众平台发送过消息的用户发送信息
     * @param  string $openid  用户的openid
     * @param  array  $content 发送的数据，目前仅支持 text 类型
     */
    public function send_message(){
		if(isset($_POST['dosubmit'])){
			$openid = $_POST['openid'];
			$content = $_POST['content'];
			
			$url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->get_access_token();
			$json_str = '{
				"touser":"'.$openid.'",
				"msgtype":"text",
				"text":
				{"content":"'.$content.'"}
			}';
			
			$json_arr = https_request($url, $json_str);

			if($json_arr['errcode'] == 0){
				$arr['openid'] = $openid;
				$arr['content'] = $content;
				$arr['inputtime'] = SYS_TIME;
				$arr['msgtype'] = 'text';
				$arr['isread'] = 1;
				$arr['issystem'] = 1;
				
				D('wechat_message')->insert($arr);
				showmsg(L('operation_success'), U('init'), 1);
			}else{
				showmsg('操作失败！'.$json_arr['errmsg'], 'stop');
			}
		}else{
			$openid = isset($_GET['openid']) ? $_GET['openid'] : 0;
			
			$wechat_message = D('wechat_message');
			
			$data = D('wechat_user')->field('openid, nickname, headimgurl, remark')->where(array('openid' => $openid))->find();
			$wechat_message->update(array('isread'=>1), array('openid' => $openid));
			$message = $wechat_message->field('issystem, inputtime, content')->where(array('openid' => $openid))->order('id ASC')->select();
			include $this->admin_tpl('send_message');	
		}
    }
	

	/**
	 * 标识已读
	 */	
	public function read(){ 
		D('wechat_message')->update(array('isread'=>1), array('1'=>1));
		showmsg(L('operation_success'),'',1);
	}

	
	/**
	 * 删除消息
	 */	
	public function del(){ 
		if($_POST && is_array($_POST['ids'])){
			$wechat_message = D('wechat_message');
			foreach($_POST['ids'] as $val){
				$wechat_message->delete(array('id'=>$val));
			}
			showmsg(L('operation_success'),'',1);
		}
	}
	
	
	
    /**
     *  获取用户信息
     */	
	public function get_userinfo($openid){
		global $wechat_user;
		$wechat_user = isset($wechat_user) ? $wechat_user : D('wechat_user');
        $data = $wechat_user->field('nickname, headimgurl, remark')->where(array('openid' => $openid))->find();
		return $data['nickname'] ? '<img src="'.$data['headimgurl'].'" height="25" title="'.$data['remark'].'"> '.$data['nickname'] : ($data['remark'] ? $data['remark'] : $openid);
    }

}