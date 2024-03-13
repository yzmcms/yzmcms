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
new_session_start();

class index{

	private $siteid,$siteinfo;
	
	public function __construct(){
		$this->siteid = get_siteid();
		$this->siteinfo = array();
		if($this->siteid){
			$this->siteinfo = get_site($this->siteid);
			set_module_theme($this->siteinfo['site_theme']);
		}
	}
	
	/**
	 * 发布评论
	 */
	public function init(){	
 		if(is_post()) {
			if(empty($_POST['content'])) return_message('评论内容不能为空！', 0);
			
			$site = array_merge(get_config(), $this->siteinfo);
			if($site['comment_code']){
				!isset($_POST['code']) && return_message(L('code_error'), 0);
				if(empty($_SESSION['code']) || strtolower($_POST['code'])!=$_SESSION['code']){
					$_SESSION['code'] = '';
					return_message(L('code_error'), 0);
				}
				$_SESSION['code'] = '';
			}
			
			$userid = $_POST['userid'] = isset($_SESSION['_userid']) ? $_SESSION['_userid']  : 0;
			$username = $_POST['username'] = isset($_SESSION['_username']) ? $_SESSION['_username'] : '游客';
			if(!$userid && !$site['comment_tourist']){
				is_ajax() && return_json(array('status'=>0, 'message'=>'登录后方可发布评论！'));
				showmsg('登录后方可发布评论！', url_referer(1, HTTP_REFERER), 1);
			}
			
			$ip = getip();
			if($userid) $this->_check_auth($userid, $username, $ip);
			
			$_POST = new_html_special_chars($_POST);
			
			$_POST['siteid'] = $this->siteid;
			$_POST['content'] = $this->_recontent($_POST['content']);
			$_POST['userpic'] = $userid ? get_memberavatar($userid) : '';
			$_POST['inputtime'] = SYS_TIME;
			$_POST['ip'] = $ip;	
			$_POST['reply'] = isset($_POST['reply']) ? intval($_POST['reply']) : 0;
			$_POST['modelid'] = isset($_POST['modelid']) ? intval($_POST['modelid']) : 1; 
			$_POST['catid'] = isset($_POST['catid']) ? intval($_POST['catid']) : 0; 
			$_POST['id'] = isset($_POST['id']) ? intval($_POST['id']) : 0; 
			$_POST['commentid'] = $this->_get_commentid($_POST['modelid'], $_POST['catid'], $_POST['id']);
			$_POST['url'] = get_content_url($_POST['catid'], $_POST['id']);
			$_POST['status'] = !$site['comment_check']; 
			$_POST['total'] = 1;

			if(empty($_POST['url'])) return_message(L('illegal_parameters'), 0);
			
			//评论回复
			if($_POST['reply']){
				$uname = $_POST['username'];
				if(isset($_SESSION['roleid'])){
					$uname = '<strong style="color:#de4c1c">管理员</strong>';
					$_POST['username'] = '管理员';
					$_POST['status'] = 1;  //管理员回复，评论状态则是通过审核的
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
			}
			
			D('comment')->insert($_POST); //评论表
			$comment_data = D('comment_data');
			if($comment_data->where(array('commentid' => $_POST['commentid']))->find()){
				$comment_data->update('`total`=`total`+1', array('commentid' => $_POST['commentid']));
			}else{
				$comment_data->insert($_POST, false, false);
			}
			
			return_message($_POST['status']?'评论成功！':'评论成功，待管理员审核后显示！');
				
		}
	}
	

	/**
	 * 更多评论
	 */
	public function more(){
		$sign = isset($_GET['sign'])&&is_string($_GET['sign']) ? trim($_GET['sign']) : return_message(L('lose_parameters'), 0);
		if(!strpos($sign, '_')) return_message(L('illegal_parameters'), 0);
		list($modelid, $id) = explode('_', $sign);
		$tabname = get_model($modelid, 'tablename', true);
		if(!$tabname) return_message(L('illegal_parameters'), 0);
		$id = intval($id);
		$content_data = D($tabname)->field('catid,status,title,url,inputtime,updatetime,keywords,description')->where(array('id'=>$id))->find();
		if(!$content_data || $content_data['status'] != 1) return_message(L('illegal_parameters'), 0);
		$commentid = $this->_get_commentid($modelid, $content_data['catid'], $id);

		yzm_base::load_sys_class('page','',0);
		$total = D('comment')->where(array('commentid'=>$commentid,'status'=>1))->total();
		$page = new page($total, 20);
		$comment_data = D('comment')->where(array('commentid'=>$commentid,'status'=>1))->order('id DESC')->limit($page->limit())->select();	

		$site = array_merge(get_config(), $this->siteinfo);
		list($seo_title, $keywords, $description) = get_site_seo($this->siteid, $content_data['title'].'的内容评论', $content_data['keywords']);

		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull(false);

		include template('index', 'comment_more');
	}


	/**
	 * 获取评论唯一ID
	 */
	protected function _get_commentid($modelid, $catid, $id){

		return $modelid.'_'.$catid.'_'.$id;
		
	}
	
	
	/**
	 * 处理内容
	 */
	protected function _recontent($content){
		$content = preg_replace('[\[em_([0-9]*)\]]', '<img src="'.SITE_PATH.'common/static/images/face/$1.gif"/>', $content);
		$arr = explode('|', get_config('prohibit_words'));
		return str_replace($arr, '*', $content);
		
	}
	
	
	
	/**
	 * 判断会员权限及会员积分奖励
	 */
	protected function _check_auth($userid, $username, $ip){
		$groupid = intval(get_cookie('_groupid'));
		$groupinfo = get_groupinfo($groupid);
		if(strpos($groupinfo['authority'], '2') === false) return_message('你没有权限发布评论，请升级会员组！', 0);

		// IP黑名单验证
		$blacklist_ip = get_config('blacklist_ip');
		if($blacklist_ip){
			$arr = explode(',', $blacklist_ip);
			foreach($arr as $val){
				if(check_ip_matching($val, $ip)) return_message('IP黑名单用户，禁止评论！', 0);
			}
		}

		$inputtime = D('comment')->field('inputtime')->where(array('userid'=>$userid))->order('id DESC')->one();
		if($inputtime && ($inputtime+5>=SYS_TIME)) return_message('评论过快，请稍后再试！', 0);
		
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