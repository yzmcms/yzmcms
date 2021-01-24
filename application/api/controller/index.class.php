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
	 * 收藏文档，必须登录
	 * @param url 地址
	 * @param title 标题
	 * @return {1:成功;-1:未登录;-2:缺少参数}
	 */	
	public function favorite(){	
		if(isset($_POST['title']) && isset($_POST['url'])) {
			$title = htmlspecialchars(addslashes($_POST['title']));
			$url = safe_replace(addslashes($_POST['url']));
		} else {
			return_json(array('status'=>-2, 'message'=>L('lose_parameters')));
		}

		$userid = intval(get_cookie('_userid'));
		if(!$userid) return_json(array('status'=>-1, 'message'=>L('login_website')));

		$favorite = D('favorite');
		//根据url判断是否已经收藏过。
		$is_exists = $favorite->field('id')->where(array('userid'=>$userid, 'url'=>$url))->one();
		if(!$is_exists) {
			$data = array('title'=>$title, 'url'=>$url, 'inputtime'=>SYS_TIME, 'userid'=>$userid);
			$favorite->insert($data);
		}

		return_json(array('status'=>1, 'message'=>L('operation_success')));
	}


	/**
	 * 获取会员中心消息数量
	 * @return {1:成功;-1:未登录;}
	 */	
	public function message(){

		$userid = intval(get_cookie('_userid'));
		if(!$userid) return_json(array('status'=>-1, 'message'=>L('login_website')));

		$member = D('member');
		$memberinfo = $member->field('groupid,username')->where(array('userid'=>$userid))->find();

		//系统消息[群发]
		$system_totnum = D('message_group')->where(array('groupid' => $memberinfo['groupid']))->total(); //总条数
		$res = $member->fetch_array($member->query("SELECT COUNT(*) AS total FROM yzmcms_message_group a LEFT JOIN yzmcms_message_data b ON a.id=b.group_message_id WHERE a.groupid='{$memberinfo['groupid']}' AND a.`status`=1 AND b.userid={$userid}"));  //已读信息
		$data['system_msg'] = $system_totnum - $res['total'];   //系统消息，未读条数
		
		//收件箱消息，未读条数
		$data['inbox_msg'] = D('message')->where(array('send_to' => $memberinfo['username'], 'status' => '1', 'isread' => '0'))->total(); 
		return_json(array('status'=>1, 'message'=>L('operation_success'), 'data'=>$data));
	}


	/**
	 * 检测内容标题是否存在
	 * @param title
	 * @param modelid 或 catid
	 * @return {-1:错误;0存在;1:不存在;}
	 */	
	public function check_title(){

		$modelid = 0;
		if(isset($_GET['modelid'])){
			$modelid = intval($_GET['modelid']);
		}else{
			$catid = isset($_GET['catid']) ? intval($_GET['catid']) : return_json(array('status'=>-1, 'message'=>L('lose_parameters')));
			$modelid = get_category($catid, 'modelid');
		}
		if(!$modelid) return_json(array('status'=>-1, 'message'=>L('illegal_parameters')));

		$title = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : '';
		if(!$title) return_json(array('status'=>-1, 'message'=>L('lose_parameters')));

		if(!isset($_SESSION['adminid']) && !isset($_SESSION['_userid'])) return_json(array('status'=>-1, 'message'=>L('login_website')));

		$dbname =  get_model($modelid);
		if(!$dbname)  return_json(array('status'=>-1, 'message'=>L('illegal_parameters')));
		$title = D($dbname)->field('id')->where(array('title'=>$title))->one();

		if($title) return_json(array('status'=>0, 'message'=>'内容标题已存在！'));
		return_json(array('status'=>1, 'message'=>'内容标题不存在！'));
	}

}