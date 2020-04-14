<?php
/**
 * 会员个人主页
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-17
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_sys_class('page','',0);

class myhome{

	public function __construct() {
		//设置会员模块模板风格
		set_module_theme('default');
	}

	
	/**
	 * 会员主页
	 */	
	public function init(){ 
		$userid = isset($_GET['userid']) ? intval($_GET['userid']) : showmsg(L('lose_parameters'), 'stop');
		$memberinfo = get_memberinfo($userid, true);
		if(!$memberinfo) showmsg('会员不存在或已被删除！', 'stop');
		extract($memberinfo);
		
		$groupinfo = get_groupinfo($groupid);
		D('member_detail')->update('`guest`=`guest`+1', array('userid'=>$userid));
		
		$member_content = D('member_content');
		$total = $member_content->where(array('userid' =>$userid,'status' =>1))->total();
		$page = new page($total, 10);
		$res = $member_content->field('checkid,title,catid,inputtime')->where(array('userid' =>$userid,'status' =>1))->order('id DESC')->limit($page->limit())->select();
		$data = array();
		foreach($res as $val) {
			list($val['modelid'], $val['id']) = explode('_', $val['checkid']);
			$val['url'] = U('index/index/show', array('catid'=>$val['catid'],'id'=>$val['id']));
			$data[] = $val;
		}
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		
		$guest_data = $this->_guest($userid);
		
		include template('member', 'myhome');
	}
	
	
	
	/**
	 * 记录并获取访客记录
	 */	
	private function _guest($userid){		
		$member_guest = D('member_guest');
		$guest_id = $member_guest->field('guest_id')->where(array('space_id'=>$userid))->order('id DESC')->one();
		
		//现在的访客id
		$now_userid = intval(get_cookie('_userid'));

		//如果访客已登录，并且访问的不是自己的主页，并且访客表的最后一个访客不是自己，则保存记录
		if($now_userid && $now_userid!=$userid && $guest_id!=$now_userid){
			$data['space_id'] = $userid;
			$data['guest_id'] = $now_userid;
			$data['guest_name'] = safe_replace(get_cookie('_username'));
			$data['guest_pic'] = get_memberavatar($now_userid);
			$data['inputtime'] = SYS_TIME;
			$data['ip'] = getip();
			$member_guest->insert($data);
		}

		return $member_guest->field('guest_id,guest_name,guest_pic,inputtime')->where(array('space_id'=>$userid))->order('id DESC')->limit('9')->select();
	}

}