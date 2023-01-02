<?php
/**
 * 系统API接口类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-18
 */

defined('IN_YZMPHP') or exit('Access Denied');
new_session_start();

class index{
	
	
	/**
	 * 验证码图像
	 * 请求方式：GET
	 */
	public function code(){	
		$code = yzm_base::load_sys_class('code');
		if(isset($_GET['width']) && intval($_GET['width'])) $code->width = intval($_GET['width']);
		if(isset($_GET['height']) && intval($_GET['height'])) $code->height = intval($_GET['height']);
		if(isset($_GET['code_len']) && intval($_GET['code_len'])) $code->code_len = intval($_GET['code_len']);
		if(isset($_GET['font_size']) && intval($_GET['font_size'])) $code->font_size = intval($_GET['font_size']);
		if($code->width > 500 || $code->width < 10)  $code->width = 100;
		if($code->height > 300 || $code->height < 10)  $code->height = 35;
		if($code->code_len > 8 || $code->code_len < 2)  $code->code_len = 4;
		$code->show_code();
		$_SESSION['code'] = $code->get_code();
	}


	/**
	 * 检测内容标题是否存在
	 * 请求方式：GET
	 * @param title
	 * @param modelid 可选
	 * @return {-1:错误;0存在;1:不存在;}
	 */	
	public function check_title(){

		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
		$title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : '';
		if(!$title) return_json(array('status'=>-1, 'message'=>L('lose_parameters')));

		if(!isset($_SESSION['adminid']) && !isset($_SESSION['_userid'])) return_json(array('status'=>-1, 'message'=>L('login_website')));

		$dbname =  $modelid ? get_model($modelid) : 'all_content';
		if(!$dbname)  return_json(array('status'=>-1, 'message'=>L('illegal_parameters')));
		$title = D($dbname)->field('id')->where(array('title'=>$title))->one();
		$title ? return_json(array('status'=>0, 'message'=>'内容标题已存在！')) : return_json(array('status'=>1, 'message'=>'内容标题不存在！'));
	}
	

	/**
	 * 收藏文档，必须登录
	 * 请求方式：POST
	 * @param url 地址
	 * @param title 标题
	 * @return {2:取消收藏;1:已收藏;-1:未登录;-2:缺少参数}
	 */	
	public function favorite(){	
		if(isset($_POST['title']) && isset($_POST['url'])) {
			$title = htmlspecialchars(addslashes($_POST['title']));
			$url = safe_replace(addslashes($_POST['url']));
		} else {
			return_json(array('status'=>-2, 'message'=>L('lose_parameters')));
		}

		$userid = $this->_check_login();

		$favorite = D('favorite');
		$id = $favorite->field('id')->where(array('userid'=>$userid, 'url'=>$url))->one();
		if($id) {
			$favorite->delete(array('id'=>$id));
			return_json(array('status'=>2, 'message'=>'取消收藏'));
		}else{
			$data = array('title'=>$title, 'url'=>$url, 'inputtime'=>SYS_TIME, 'userid'=>$userid);
			$favorite->insert($data);
			return_json(array('status'=>1, 'message'=>'已收藏'));
		}
	}


	/**
	 * 检查是否收藏，必须登录
	 * 请求方式：POST
	 * @param url 地址
	 * @return {1:已收藏;0:未收藏;-1:未登录;-2:缺少参数}
	 */	
	public function check_favorite(){	

		$userid = $this->_check_login();
		$url = isset($_POST['url']) ? safe_replace(addslashes($_POST['url'])) : return_json(array('status'=>-2, 'message'=>L('lose_parameters')));

		$id = D('favorite')->field('id')->where(array('userid'=>$userid, 'url'=>$url))->one();
		$id ? return_json(array('status'=>1, 'message'=>'已收藏')) : return_json(array('status'=>0, 'message'=>'未收藏'));

	}


	/**
	 * 获取会员中心消息数量，必须登录
	 * 请求方式：GET
	 * @return {1:成功;-1:未登录;}
	 */	
	public function message(){

		$userid = $this->_check_login();

		$member = D('member');
		$memberinfo = $member->field('groupid,username')->where(array('userid'=>$userid))->find();

		//系统消息[群发]
		$system_totnum = D('message_group')->where(array('groupid' => $memberinfo['groupid']))->total(); //总条数
		$total = D('message_group')->alias('a')->join('yzmcms_message_data b ON a.id=b.group_message_id', 'LEFT')->where(array('a.groupid'=>$memberinfo['groupid'], 'a.status'=>1, 'b.userid'=>$userid))->total();  //已读信息
		$data['system_msg'] = $system_totnum - $total;   //系统消息，未读条数
		
		//收件箱消息，未读条数
		$data['inbox_msg'] = D('message')->where(array('send_to' => $memberinfo['username'], 'status' => '1', 'isread' => '0'))->total(); 
		return_json(array('status'=>1, 'message'=>L('operation_success'), 'data'=>$data));
	}


	/**
	 * 检查是否关注会员，必须登录
	 * 请求方式：GET
	 * @param title
	 * @param modelid 或 catid
	 * @return {-1:错误;0已关注;1:未关注;}
	 */	
	public function check_follow(){

		$current_userid = $this->_check_login();
		$userid = isset($_GET['userid']) ? intval($_GET['userid']) : return_json(array('status'=>-1, 'message'=>L('lose_parameters')));
		$id = D('member_follow')->field('id')->where(array('userid'=>$current_userid, 'followid'=>$userid))->one();
		$id ? return_json(array('status'=>1, 'message'=>'已关注')) : return_json(array('status'=>0, 'message'=>'未关注'));
	}


	/**
	 * 下载远程图片
	 * 请求方式：POST
	 * @param contnet
	 * @return {0错误;1正确;}
	 */	
	public function down_remote_img(){
		if(!isset($_SESSION['adminid']) && !isset($_SESSION['_userid'])) return_json(array('status'=>0, 'message'=>L('login_website')));
		$content = isset($_POST['content']) ? trim($_POST['content']) : return_json(array('status'=>0, 'message'=>L('lose_parameters')));

		return_json(array('status'=>1, 'message'=>L('operation_success'), 'data'=>down_remote_img($content))); 
	}


	/**
	 * 私有方法，检查是否登录
	 */	
	private function _check_login(){
		$userid = intval(get_cookie('_userid'));
		if(!$userid) return_json(array('status'=>-1, 'message'=>L('login_website')));
		return $userid;
	}

}