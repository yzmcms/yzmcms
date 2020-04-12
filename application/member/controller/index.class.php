<?php
/**
 * 会员中心操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-15
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'member', 0);

class index extends common{
	
	function __construct() {
		parent::__construct();
	}

	
	/**
	 * 会员中心
	 */	
	public function init(){ 
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		$groupinfo = get_groupinfo($groupid);
		include template('member', 'index');
	}
	
	
	/**
	 * 会员登录
	 */	
	public function login(){		
		
		if(isset($_POST['dosubmit'])){		
			
			//检查是否开启验证码
			if(get_config('member_yzm')){
				if(empty($_SESSION['code']) || strtolower($_POST['code'])!=$_SESSION['code']){
					$_SESSION['code'] = '';
					showmsg(L('code_error'));
				}
				$_SESSION['code'] = '';
			}
			$member = D('member');
			$username = isset($_POST['username']) ? trim($_POST['username']) : showmsg(L('lose_parameters'), 'stop');
			$password = password($_POST['password']);

			//电子邮箱和用户名两种登录方式
			$where = is_email($username) ? array('email'=>$username) : array('username'=>$username);
			
			$data = $member->where($where)->find();
			if(!$data) showmsg(L('user_does_not_exist'));
			if($data['password'] != $password) showmsg('密码错误！', '', 1);
			if($data['status'] == '0') 
				showmsg('用户未通过审核！', 'stop');		
			else if($data['status'] == '2') 
				showmsg('用户已锁定！', 'stop');		
			else if($data['status'] == '3') 
				showmsg('用户已被管理员拒绝！', 'stop');
			
			$_SESSION['_userid'] = $data['userid'];
			$_SESSION['_username'] = $data['username'];
			set_cookie('_userid', $data['userid']);
			set_cookie('_username', $data['username']);
			set_cookie('_nickname', $data['username']);
			set_cookie('_groupid', $data['groupid']);			
			
			//每日登录，增加积分和经验，并更新新用户组
			$last_day = date('d', $data['lastdate']);
			if($last_day != date('d')  &&  SYS_TIME>$data['lastdate'] && get_config('login_point')>0){
				 M('point')->point_add('1',get_config('login_point'),'0',$data['userid'],$data['username'],$data['experience']);		
			}
			
			$where = '';
			if($data['vip'] && $data['overduedate']<SYS_TIME)	$where .= '`vip`=0,';   //如果用户是vip用户，检查vip是否过期
			
			$where .= '`lastip`="'.getip().'",`lastdate`="'.SYS_TIME.'",`loginnum`=`loginnum`+1';
			$member->update($where, array('userid'=>$data['userid']));
			$referer = isset($_POST['referer']) && !empty($_POST['referer']) ? urldecode($_POST['referer']) : U('member/index/init');
			showmsg(L('login_success'), $referer, 1);
		}

		$referer = isset($_GET['referer']) && trim($_GET['referer']) ? urlencode($_GET['referer']) : '';
		include template('member', 'login');
	}


	/**
	 * 会员注册
	 */	
	public function register(){ 
		$config = get_config();
		//检查是否允许新会员注册
		if($config['member_register'] == 0) showmsg('管理员关闭了新会员注册！', 'stop');
		
		if(isset($_SESSION['_userid']) && $_SESSION['_userid']){
			showmsg(L('login_success'), U('member/index/init'), 1);
		}
			
			
		if(isset($_POST['dosubmit'])){
			
			if(empty($_SESSION['code']) || strtolower($_POST['code']) != $_SESSION['code']){
				$_SESSION['code'] = '';
				showmsg(L('code_error'), '', 1);
			}
			$_SESSION['code'] = '';
			
			$member = D('member');
			$data = array();
			$data['username'] = isset($_POST['username']) && is_username($_POST['username']) ? trim($_POST['username']) : showmsg(L('user_name_format_error'));		
			$data['password'] = isset($_POST['password']) && is_password($_POST['password']) ? trim($_POST['password']) : showmsg(L('password_format_error'));	
			$data['email'] = isset($_POST['email']) && is_email($_POST['email']) ? trim($_POST['email']) : showmsg(L('mail_format_error'));				
			
			$result = $member->where(array('username'=>$_POST['username']))->find();
			if($result) showmsg(L('user_already_exists'));
			$result = $member->where(array('email'=>$_POST['email']))->find();
			if($result) showmsg("邮箱已存在！");
			
			$data['nickname'] = $data['username'];
			$data["password"] = password($data['password']);
			$data['regdate'] = $data['lastdate'] = SYS_TIME;
			$data['regip'] = $data['lastip'] = getip();
			$data['groupid'] = '1';
			$data['amount'] = '0.00';
			$data['point'] = $data['experience'] = $config['member_point'];	 //经验和积分
			$data['status'] = ($config['member_check'] || $config['member_email']) ? 0 : 1;		
			$data['userid'] = $member->insert($data, true);		
			if(!$data['userid']) showmsg("注册失败！");
			
			D('member_detail')->insert($data, true, false); //插入附表
			
			if($config['member_email']){
				//需要邮件验证
				$mail_code = string_auth($data['userid'].'|'.SYS_TIME, 'ENCODE', make_auth_key('email'));
				$url = U('member/index/register', array('mail_code'=>$mail_code, 'verify'=>1));
				$message = '<p>您正在注册'.get_config('site_name').'网站会员：</p><p>请点击如下链接进行邮箱验证：<a href="'. $url .'" target="_blank">'. $url .'</a></p><p>验证有效期为30分钟！</p>';
				$res = sendmail($data['email'], '会员注册邮箱验证', $message);
				if(!$res) showmsg('邮件发送失败，请联系网站管理员！', 'stop');
				showmsg('我们已将邮件发送到您的邮箱，请尽快完成验证！');
			}elseif($config['member_check']){  
				//需要管理员审核
				showmsg('注册成功，由于管理员开启审核机制，请耐心等待！');
			}
			
			$_SESSION['_userid'] = $data['userid'];
			$_SESSION['_username'] = $data['username'];
			set_cookie('_userid', $data['userid']);
			set_cookie('_username', $data['username']);
			set_cookie('_nickname', $data['username']);
			set_cookie('_groupid', $data['groupid']);		
			showmsg('注册成功！', U('member/index/init'), 1);			
			
		}else{
			if(!empty($_GET['verify'])) {
				$mail_code = isset($_GET['mail_code']) ? trim($_GET['mail_code']) : showmsg(L('illegal_operation'), 'stop');
				$code_res = string_auth($mail_code, 'DECODE', make_auth_key('email'));
				$code_arr = explode('|', $code_res);
				$userid = isset($code_arr[0]) ? intval($code_arr[0]) : showmsg(L('illegal_operation'), 'stop');
				$time = isset($code_arr[1]) ? $code_arr[1] : showmsg(L('illegal_operation'), 'stop');
				if($time+1800 > SYS_TIME){
					D('member')->update(array('status' => 1, 'email_status' => 1),array('userid'=>$userid));
					showmsg('邮箱验证成功！', U('member/index/login'), 2);
				}else{
					showmsg('邮箱验证失败，验证时间已失效！', U('member/index/register'));
				}
			}
			include template('member', 'register');
		}		
		
	}	

	
	/**
	 * 会员退出
	 */	
	public function logout(){ 
		unset($_SESSION['_userid']);
		unset($_SESSION['_username']);
		del_cookie('_userid');
		del_cookie('_username');
		del_cookie('_nickname');
		del_cookie('_groupid');
		showmsg(L('you_have_safe_exit'), U('member/index/login'), 2);
	}	

	
	/**
	 * ajax检查用户名是否存在
	 * @param string $username	用户名
	 * @return $status {0：用户名格式不正确;-1:用户名已经存在 ;1:成功}	 
	 */	
	public function public_checkname(){ 
		$username = isset($_POST['username']) && is_username($_POST['username']) ? trim($_POST['username']) : exit('0');
		$result = D('member')->where(array('username' => $username))->find();
		$result ? exit('-1') : exit('1');
	}
	
	
	/**
	 * ajax检查邮箱是否存在
	 * @param string $email	邮箱
	 * @return $status {0：邮箱格式不正确;-1:邮箱已经存在 ;1:成功}	 
	 */	
	public function public_checkemail(){ 
		$email = isset($_POST['email']) && is_email($_POST['email']) ? trim($_POST['email']) : exit('0');
		$result = D('member')->where(array('email' => $email))->find();
		$result ? exit('-1') : exit('1');
	}
	
	
	/**
	 * 用户修改资料
	 */	
	public function account(){
		
		if(isset($_POST['dosubmit'])){
			if(!is_mobile($_POST['mobile'])) showmsg('手机号不正确！');
			unset($_POST['userpic'], $_POST['guest'], $_POST['fans']);
			$res = D('member_detail')->update($_POST, array('userid'=>$this->userid), true);
			if($res){ 
				showmsg('更新资料成功！','',1);
			}else{
				showmsg(L('data_not_modified'));
			}
		}

		yzm_base::load_sys_class('form','',0);
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		if($area){
			list($cmbProvince,$cmbCity,$cmbArea) = explode('|',$area); //分配地区
		}else{
			$cmbProvince = $cmbCity = $cmbArea ='';
		}		
		include template('member', 'account');
	}
		
	
	
	/**
	 * 用户修改头像
	 */	
	public function user_pic(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		if(isset($_POST['dosubmit'])){
			$filename = $_FILES['user_pic']['name'];
			if($filename == '') showmsg('请上传头像！');
			$upload = yzm_base::load_sys_class('upload');
			if($upload->uploadfile('user_pic')){
				$fileinfo = $upload->getnewfileinfo();
				$picname = $fileinfo['filepath'].$fileinfo['filename'];
				D('member_detail')->update(array('userpic'=>$picname),array('userid'=>$this->userid));
				$userpic = YZMPHP_PATH.ltrim($userpic, SITE_PATH);
				if(is_file($userpic)) @unlink($userpic);    //删除原来的头像
				showmsg('更新头像成功！','',1);
			}else{
				showmsg($upload->geterrormsg());
			}
		}
	
		include template('member', 'user_pic');
	}
	
		
	
	/**
	 * 用户修改密码
	 */	
	public function password(){

		if(isset($_POST['dosubmit'])){
			if(strtolower($_POST['code']) != $_SESSION['code']) showmsg(L('code_error'));
			if($_POST['oldpass'] == '') showmsg("原密码不能为空！");
			if(!$this->db->where(array('userid'=>$this->userid, 'password'=>password($_POST['oldpass'])))->find()) showmsg('原密码错误!');
			if(!is_password($_POST['password'])) showmsg('新密码不符合规范!');
			if($this->db->update(array('password'=>password($_POST['password'])), array('userid'=>$this->userid))){
				showmsg(L('operation_success'),'',1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
		
		$memberinfo = $this->memberinfo;
		extract($memberinfo);	
		include template('member', 'password');
	}
	
	
	/**
	 * 用户修改邮箱/安全问题
	 */	
	public function email(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		if(isset($_POST['dosubmit'])){
			if(strtolower($_POST['code']) != $_SESSION['code']) showmsg(L('code_error'));
			if(!$this->db->where(array('userid'=>$this->userid, 'password'=>password($_POST['password'])))->find()) showmsg(L('password_error'));
			
			$data = array();
			if(!$email_status){
				if(!isset($_POST['email']) || !is_email($_POST['email'])) showmsg(L('mail_format_error'));
				$data['email'] = $_POST['email'];
			}
			
			$problem = new_html_special_chars(strip_tags(trim($_POST['problem'])));
			$answer = new_html_special_chars(strip_tags(trim($_POST['answer'])));
			if($problem != '0' && $answer != ''){ 
				$data['problem'] = $problem;  //安全问题
				$data['answer'] = $answer;  //安全答案
			}	
				
			if(!empty($data) && $this->db->update($data, array('userid'=>$this->userid))){
				showmsg(L('operation_success'),'',1);
			}else{
				showmsg(L('data_not_modified'));
			}
		}
		
		$problemarr = array('你最喜欢的格言什么？','你家乡的名称是什么？','你读的小学叫什么？','你的父亲叫什么名字？','你的母亲叫什么名字？','你的配偶叫什么名字？','你最喜欢的歌曲是什么？');	
		include template('member', 'email');
	}
	
	
	/**
	 * ajax会员加关注/取消关注
	 * @param int userid	用户ID
	 * @return $status {-3:不能关注自己 ;-2:用户ID不合法 ;-1:用户名不存在 ;0:用户未登录 ;1:关注成功 ;2:取消关注成功}
	 */	
	public function public_follow(){ 
		$this->userid = intval(get_cookie('_userid'));
		if(!isset($_SESSION['_userid']) || !$_SESSION['_userid'] || $this->userid != $_SESSION['_userid']) exit('0');
		$userid = isset($_POST['userid']) ? intval($_POST['userid']) : exit('-2');
		if($this->userid == $userid) exit('-3');
		
		$memberinfo = D('member')->field('username')->where(array('userid'=>$userid))->find();
		if(!$memberinfo)  exit('-1');
	
		$member_follow = D('member_follow');
		$r = $member_follow->where(array('userid'=>$this->userid, 'followid'=>$userid))->find();
		if($r){
			$member_follow->delete(array('userid'=>$this->userid, 'followid'=>$userid));
			D('member_detail')->update('`fans`=`fans`-1', array('userid'=>$userid));  //减少粉丝数
			exit('2');
		}else{
			$member_follow->insert(array('userid'=>$this->userid,'followid'=>$userid,'followname'=>$memberinfo['username'],'inputtime'=>SYS_TIME));
			D('member_detail')->update('`fans`=`fans`+1', array('userid'=>$userid));  //增加粉丝数
			exit('1'); 
		}
	}
	
	
	/**
	 * 我的关注
	 */	
	public function follow(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$member_follow = D('member_follow');
		
		if(isset($_GET['followid'])){
			$followid = intval($_GET['followid']);
			if($member_follow->delete(array('userid'=>$userid, 'followid'=>$followid))){
				D('member_detail')->update('`fans`=`fans`-1', array('userid'=>$followid));  //减少粉丝数
				showmsg(L('operation_success'),'',1);
			}else{
				showmsg(L('data_not_modified'));
			}
		}
		yzm_base::load_sys_class('page','',0);
		$total = $member_follow->where(array('userid'=>$userid))->total();
		$page = new page($total, 9);
		$data = $member_follow->where(array('userid'=>$userid))->order('id DESC')->limit($page->limit())->select();	
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'follow');
	}
	
	
	/**
	 * TA的动态
	 */	
	public function follow_dynamic(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$member_content = D('member_content');
		yzm_base::load_sys_class('page','',0);
		$total = $member_content->join('yzmcms_member_follow ON yzmcms_member_follow.followid = yzmcms_member_content.userid', 'RIGHT')->where("yzmcms_member_follow.userid=$userid AND status=1")->total();
		$page = new page($total, 15);
		$res = $member_content->field('yzmcms_member_content.checkid,yzmcms_member_content.catid,yzmcms_member_content.username,yzmcms_member_content.title,yzmcms_member_content.inputtime')->join('yzmcms_member_follow ON yzmcms_member_follow.followid = yzmcms_member_content.userid', 'RIGHT')->where("yzmcms_member_follow.userid=$userid AND status=1")->order('inputtime DESC')->limit($page->limit())->select();	
		$data = array();
		foreach($res as $val) {
			list($val['modelid'], $val['id']) = explode('_', $val['checkid']);
			$val['url'] = SITE_URL.'index.php?m=index&c=index&a=show&catid='.$val['catid'].'&id='.$val['id'];
			$val['event'] = $val['username'].' 发布了《<a href="'.$val['url'].'" target="_blank">'.$val['title'].'</a>》';
			$data[] = $val;
		}
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'follow_dynamic');
	}



	/**
	 * 我的粉丝
	 */	
	public function fans(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$member_follow = D('member_follow');
		yzm_base::load_sys_class('page','',0);
		$total = $member_follow->where(array('followid'=>$userid))->total();
		$page = new page($total, 9);
		$data = $member_follow->alias('f')->field('m.userid,m.username')->join('yzmcms_member m ON f.userid=m.userid')->where(array('followid'=>$userid))->order('id DESC')->limit($page->limit())->select();	
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'fans');
	}
	
}