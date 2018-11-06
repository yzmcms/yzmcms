<?php
class index{
	
	public function init(){	
		session_start();
 		if(isset($_POST['dosubmit'])) {
			if(trim($_POST['content']) == '') showmsg('你不打算说点什么吗？');
			
			$site = get_config();
			
			$userid = $_POST['userid'] = isset($_SESSION['_userid']) ? $_SESSION['_userid']  : 0;
			$username = $_POST['username'] = isset($_SESSION['_username']) ? $_SESSION['_username'] : $site['site_name'].'网友';
			
			if(!$userid && !$site['comment_tourist']){
				 showmsg('登录后方可发布评论！', url_referer($_SERVER['HTTP_REFERER']), 1);
			}
			
			$ip = getip();
			
			if($userid) $this->_check_auth($userid, $username, $ip);
			
			$_POST = new_html_special_chars($_POST);
			
			$_POST['content'] = $this->_recontent($_POST['content']);
			$_POST['userpic'] = $userid ? get_memberavatar($userid) : '';
			$_POST['inputtime'] = SYS_TIME;
			$_POST['ip'] = $ip;	
			$_POST['reply'] = isset($_POST['reply']) ? intval($_POST['reply']) : 0;
			$_POST['modelid'] = isset($_POST['modelid']) ? intval($_POST['modelid']) : 1; 
			$_POST['catid'] = isset($_POST['catid']) ? intval($_POST['catid']) : 0; 
			$_POST['id'] = isset($_POST['id']) ? intval($_POST['id']) : 0; 
			$_POST['commentid'] = $_POST['modelid'].'_'.$_POST['catid'].'_'.$_POST['id']; 
			$_POST['status'] = !$site['comment_check']; 
			$_POST['total'] = 1;
			
			//如果是回复
			if($_POST['reply']){
				$uname = $_POST['username'];
				if(isset($_SESSION['roleid'])){
					$uname = '<strong style="color:#de4c1c">管理员</strong>';
					$_POST['username'] = '管理员';
				} 
				
				//查询之前的评论内容,并整理楼层样式
				$r = D('comment')->field('username,content')->where(array('id' => $_POST['reply']))->find();
				if($r){					
					$str = strstr($r['content'], 'original_comment') ? '<span class="original_comment">'.$r['content'].' </span>' : '<span class="original_comment"><a href="javascript:void(0);" class="user_name" rel="nofollow">'.$r['username'].'</a> : '.$r['content'].' </span>';
				}else{
					$str = '';
				}
				
				//这里的格式不可以修改，会影响到后台的评论展示列表
				$_POST['content'] = $str.'<a href="javascript:void(0);" class="user_name" rel="nofollow">'.$uname.'</a>：'.$_POST['content'];
				$_POST['status'] = 1;  //如果是管理员回复，评论状态肯定是通过审核的
			}
			
			D('comment')->insert($_POST); //评论表
			$comment_data = D('comment_data');
			if($comment_data->where(array('commentid' => $_POST['commentid']))->find()){
				$comment_data->update('`total`=`total`+1', array('commentid' => $_POST['commentid']));
			}else{
				$comment_data->insert($_POST, false, false);
			}
			
			if(!$site['comment_check']){
				showmsg('评论成功！');
			}else{
				showmsg('评论成功，待管理员审核后显示！');
			}
				
		}
	}
	
	
	//处理内容
	protected function _recontent($content){
		$content = preg_replace('[\[em_([0-9]*)\]]', '<img src="'.STATIC_URL.'images/face/$1.gif"/>', $content);
		$arr = explode('|', get_config('prohibit_words'));
		return str_replace($arr, '*', $content);
		
	}
	
	
	
	
	//判断会员权限及会员积分奖励
	protected function _check_auth($userid, $username, $ip){
		$groupid = intval(get_cookie('_groupid'));
		$groupinfo = get_groupinfo($groupid);
		if(strpos($groupinfo['authority'], '2') === false) showmsg('你没有权限发布评论，请升级会员组！');
		
		$pay = D('pay');
		$comment_point = get_config('comment_point');
		$total = $pay->where(array('userid' => $userid, 'type' => '1', 'creat_time>' => strtotime(date('Y-m-d'))))->total(); //今日获取积分的次数
		//发布评论奖励积分 [奖励条件为每日获取积分次数不超过5次]
		if($comment_point > 0 && $total < 5){
			D('member')->update('`point`=`point`+'.$comment_point.',`experience`=`experience`+'.$comment_point, array('userid' => $userid));  
			$pay->insert(array('trade_sn'=>create_tradenum(), 'userid'=>$userid, 'username'=>$username, 'money'=>$comment_point, 'creat_time'=>SYS_TIME, 'msg'=>'发布评论','remarks'=>'自动获取', 'type'=>'1', 'status'=>'1', 'ip'=>$ip));
		}
	}

}