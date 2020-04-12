<?php
/**
 * 管理员后台会员操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-01-22
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class member extends common{

	
	/**
	 * 会员列表
	 */	
	public function init(){
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('userid','username','email','groupid','regip','lastdate','loginnum','experience','amount','point')) ? $of : 'userid';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$groupid = 0;
		$member_group = get_groupinfo();
		$member = D('member');
		$total = $member->total();
		$page = new page($total, 15);
		$data = $member->order("$of $or")->limit($page->limit())->select();			
		include $this->admin_tpl('member_list');
	}
	

	
	/**
	 * 用户搜索
	 */
	public function search() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('userid','username','email','groupid','regip','lastdate','loginnum','experience','amount','point')) ? $of : 'userid';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$member = D('member');
		$member_group = get_groupinfo();
		$where = '1=1';
		if(isset($_GET['dosubmit'])){	
			$status = isset($_GET["status"]) ? intval($_GET["status"]) : 99;
			$groupid = isset($_GET["groupid"]) ? intval($_GET["groupid"]) : 0;
			$searinfo = isset($_GET["searinfo"]) ? safe_replace($_GET["searinfo"]) : '';
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;
			
			if($searinfo != ''){
				if($type == '1')
					$where .= ' AND username LIKE \'%'.$searinfo.'%\'';
				elseif($type == '2')
					$where .= ' AND userid = \''.$searinfo.'\'';
				else
					$where .= ' AND email LIKE \'%'.$searinfo.'%\'';
			}
			
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where .= " AND `regdate` >= '".strtotime($_GET['start'])."' AND `regdate` <= '".strtotime($_GET['end'])."' ";
			}
			
			if($status != 99) {
				$where .= ' AND status = '.$status;
			}
			
			if($groupid) {
				$where .= ' AND groupid = '.$groupid;
			}			
		}
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $member->where($where)->total();
		$page = new page($total, 15);
		$data = $member->where($where)->order("$of $or")->limit($page->limit())->select();		
		include $this->admin_tpl('member_list');
	}	

	
	
	/**
	 * 添加用户
	 */	
	public function add(){ 
		if(isset($_POST['dosubmit'])){
			
			if(!is_username($_POST['username'])) return_json(array('status'=>0,'message'=>'用户名格式不正确！'));
			if(!is_email($_POST['email'])) return_json(array('status'=>0,'message'=>'邮箱格式不正确！'));
			if(D('member')->where(array('username' => $_POST['username']))->find()) return_json(array('status'=>0,'message'=>'用户名已存在！'));

			$data['username'] = $_POST['username'];
			$data['password'] = password($_POST['password']);
			$data['email'] = $_POST['email'];
			$data['groupid'] = intval($_POST['groupid']);
			$data['point'] = intval($_POST['point']);
			$data['status'] = 1;
			$data['regdate'] = SYS_TIME;
			$data['regip'] = getip();
			//根据用户组获取最小经验
			$data['experience'] = $this->get_experience($data['groupid']); 
			
			if(isset($_POST['vip']) && $_POST['overduedate']!=''){
				$data['vip'] = 1;
				$data['overduedate'] = strtotime($_POST['overduedate']);
			}
			
			$userid = D('member')->insert($data, true);
			if($userid){
				D('member_detail')->insert(array('userid' => $userid, 'nickname' => $_POST['nickname']), true, false); //插入附表
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>L('operation_failure')));
			}
			
		}
		$member_group = get_groupinfo();
		include $this->admin_tpl('member_add');
	}

	
	
	
	/**
	 * 修改资料
	 */	
	public function edit(){ 
		$userid = isset($_GET['userid']) ? intval($_GET['userid']) : 0;
		if(isset($_POST['dosubmit'])){
			if($_POST['password'] == ''){
				unset($_POST['password']);
			}else{
				$_POST['password'] = password($_POST['password']);
			}
			if(isset($_POST['del_userpic']) && $_POST['del_userpic'] == '1'){		
				if($_POST['userpic'] != ''){					
					$userpic = YZMPHP_PATH.ltrim($_POST['userpic'], SITE_PATH);
					if(in_array(fileext($userpic), array('jpg', 'jpeg', 'png', 'gif')) && is_file($userpic)) @unlink($userpic); 
					$_POST['userpic'] = '';					
				}
			}
			
			if(isset($_POST['vip']) && $_POST['overduedate']!=''){
				$_POST['vip'] = 1;
				$_POST['overduedate'] = strtotime($_POST['overduedate']);
			}else{
				$_POST['vip'] = 0;
			}
			
			D('member')->update($_POST, array('userid' => $userid), true);
			D('member_detail')->update($_POST, array('userid' => $userid), true);
			showmsg(L('operation_success'), U('init'), 1);
		}
		
		
		$data1 = D('member')->where(array('userid' => $userid))->find();
		$data2 = D('member_detail')->where(array('userid' => $userid))->find();
		$data = array_merge($data1, $data2);
		$member_group = get_groupinfo();
		if($data['area'] == '') $data['area'] = '||';
		list($cmbProvince,$cmbCity,$cmbArea) = explode('|',$data['area']); //分配地区
		//安全问题
		$problemarr = array('你最喜欢的格言什么？','你家乡的名称是什么？','你读的小学叫什么？','你的父亲叫什么名字？','你的母亲叫什么名字？','你的配偶叫什么名字？','你最喜欢的歌曲是什么？');
		include $this->admin_tpl('member_edit');
	}
	
	
	
	/**
	 * 修改密码
	 */	
	public function password(){ 
		if(isset($_POST['dosubmit'])){
			$userid = isset($_POST['userid']) ? intval($_POST['userid']) : 0;
			if(!is_password($_POST['password'])) return_json(array('status'=>0,'message'=>'密码格式不正确！'));
			$password = password($_POST['password']);
			if(D('member')->update(array('password' => $password), array('userid' => $userid))){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}
		$userid = isset($_GET['userid']) ? intval($_GET['userid']) : 0;
		$data = D('member')->field('username')->where(array('userid' => $userid))->find();
		include $this->admin_tpl('password');
	}
	
	
	
	
	/**
	 * 删除用户
	 */	
	public function del(){ 
		if($_POST && is_array($_POST['ids'])){
			$member = D('member');
			$member_detail = D('member_detail');
			foreach($_POST['ids'] as $val){
				$member->delete(array('userid'=>$val));
				$member_detail->delete(array('userid'=>$val));
			}
			showmsg(L('operation_success'),'',1);
		}
	}


	
	/**
	 * 待审核用户列表
	 */	
	public function check(){ 
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('userid','username','email','groupid','regip','lastdate','loginnum','experience','point')) ? $of : 'userid';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$member = D('member');
		$total = $member->where(array('status'=>0))->total();
		$page = new page($total, 15);
		$data = $member->where(array('status'=>0))->order("$of $or")->limit($page->limit())->select();			
		include $this->admin_tpl('member_check');
	}
	

	/**
	 * 通过审核
	 */	
	public function adopt(){ 
		if($_POST && is_array($_POST['ids'])){
			$member = D('member');
			foreach($_POST['ids'] as $val){
				$member->update(array('status' => '1'), array('userid' => $val));
			}
			showmsg(L('operation_success'),'',1);
		}
	}

	
	
	/**
	 * 锁定用户
	 */	
	public function lock(){ 
		if($_POST && is_array($_POST['ids'])){
			$member = D('member');
			foreach($_POST['ids'] as $val){
				$member->update(array('status' => '2'), array('userid' => $val));
			}
			showmsg('锁定用户成功！');
		}
	}
	
	
	
	/**
	 * 解锁用户
	 */	
	public function unlock(){ 
		if($_POST && is_array($_POST['ids'])){
			$member = D('member');
			foreach($_POST['ids'] as $val){
				$member->update(array('status' => '1'), array('userid' => $val));
			}
			showmsg('解锁会员成功！');
		}
	}
	
	
	/**
	 * 入账记录
	 */	
	public function pay(){ 
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','userid','username','money','ip','creat_time')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$pay = D('pay');
		$total = $pay->total();
		$page = new page($total, 15);
		$data = $pay->order("$of $or")->limit($page->limit())->select();			
		include $this->admin_tpl('pay_list');
	}
	
	
	
	/**
	 * 入账记录搜索
	 */	
	public function pay_search(){ 
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','userid','username','money','ip','creat_time')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$pay = D('pay');
		$where = '1=1';
		if(isset($_GET['dosubmit'])){

			$searinfo = isset($_GET["searinfo"]) ? safe_replace($_GET["searinfo"]) : '';
			$capital_type = isset($_GET["capital_type"]) ? $_GET["capital_type"] : 1;
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;
			
			$where .= $capital_type == '1' ? ' AND `type`=1' : ' AND `type`=2';
				
			if($searinfo != ''){
				if($type == '1')
					$where .= ' AND username LIKE \'%'.$searinfo.'%\'';
				else
					$where .= ' AND trade_sn = \''.$searinfo.'\'';
			}
			
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where .= " AND `creat_time` >= '".strtotime($_GET["start"])."' AND `creat_time` <= '".strtotime($_GET["end"])."' ";
			}			
		}
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $pay->where($where)->total();
		$page = new page($total, 15);
		$data = $pay->where($where)->order("$of $or")->limit($page->limit())->select();					
		include $this->admin_tpl('pay_list');
	}
	
	
	
	/**
	 * 入账记录删除
	 */	
	public function pay_del(){ 
		if($_POST && is_array($_POST['ids'])){
			$pay = D('pay');
			foreach($_POST['ids'] as $val){
				$pay->delete(array('id'=>$val));
			}
			showmsg(L('operation_success'),'',1);
		}
	}
	
	
	/**
	 * 消费记录
	 */	
	public function pay_spend(){ 
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','userid','username','money','ip','creat_time')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$pay_spend = D('pay_spend');
		$total = $pay_spend->total();
		$page = new page($total, 15);
		$data = $pay_spend->order("$of $or")->limit($page->limit())->select();			
		include $this->admin_tpl('pay_spend_list');
	}
	
	
	
	/**
	 * 消费记录搜索
	 */	
	public function pay_spend_search(){ 
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','userid','username','money','ip','creat_time')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$pay_spend = D('pay_spend');
		$where = '1=1';
		if(isset($_GET['dosubmit'])){	
		
			$searinfo = isset($_GET["searinfo"]) ? safe_replace($_GET["searinfo"]) : '';
			$capital_type = isset($_GET["capital_type"]) ? $_GET["capital_type"] : 1;
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;
			
			$where .= $capital_type == '1' ? ' AND `type`=1' : ' AND `type`=2';
			
			if($searinfo != ''){
				if($type == '1')
					$where .= ' AND username LIKE \'%'.$searinfo.'%\'';
				else
					$where .= ' AND trade_sn = \''.$searinfo.'\'';
			}
			
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where .= " AND `creat_time` >= '".strtotime($_GET["start"])."' AND `creat_time` <= '".strtotime($_GET["end"])."' ";
			}			
		}
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $pay_spend->where($where)->total();
		$page = new page($total, 15);
		$data = $pay_spend->where($where)->order("$of $or")->limit($page->limit())->select();					
		include $this->admin_tpl('pay_spend_list');
	}
	
	
	
	/**
	 * 消费记录删除
	 */	
	public function pay_spend_del(){ 
		if($_POST && is_array($_POST['ids'])){
			$pay_spend = D('pay_spend');
			foreach($_POST['ids'] as $val){
				$pay_spend->delete(array('id'=>$val));
			}
			showmsg(L('operation_success'),'',1);
		}
	}
	

	/**
	 * 会员统计
	 */	
	public function member_count(){ 
		//统计开始时间
		$starttime = strtotime(date('Y-m-d'))-10*24*3600;

		//统计结束时间
		$endtime = strtotime(date('Y-m-d'));

		$where = "regdate > $starttime";  //无需加结束条件，否则统计不到今日数据
		$data = D('member')->field("COUNT(*) AS num,FROM_UNIXTIME(regdate, '%Y-%m-%d') AS gap")->where($where)->group('gap')->select();
		$arr = array();
		foreach ($data as $val){
			$arr[$val['gap']] = intval($val['num']);
		}

		for($i=$starttime; $i<=$endtime; $i = $i+24*3600){
			$num = isset($arr[date('Y-m-d',$i)]) ? $arr[date('Y-m-d',$i)] : 0;				
			$result['day'][] = date('Y-m-d',$i);
			$result['num'][] = $num;
		}
		$result = json_encode($result);
		include $this->admin_tpl('member_count');
	}
	
	
	
	/**
	 * 在线充值
	 */	
	public function recharge(){ 
		if(isset($_POST['dosubmit'])) {
			$username = isset($_POST['username']) && is_username($_POST['username']) ? trim($_POST['username']) : showmsg(L('user_name_format_error'));
			
			$userinfo = D('member')->field('userid,email')->where(array('username'=>$username))->find();
			
			if($userinfo){
				
				if($_POST['unit']) {
					M('point')->point_add($_POST['type'], floatval($_POST['money']), 4, $userinfo['userid'], $username, 0, $_POST['remarks'], $_SESSION['adminname'], false);
				}else{
					M('point')->point_spend($_POST['type'], floatval($_POST['money']), 4, $userinfo['userid'], $username, $_POST['remarks']);
				}
				
				//发送e-mail通知会员 
				if(isset($_POST['sendemail'])){
					$type = $_POST['type'] == '1' ? '积分' : '元';
					$content = '您的账户于'.date('Y-m-d H:i:s',SYS_TIME).'成功充值'.floatval($_POST['money']).$type.'，详情请登录会员中心查看。';
					sendmail($userinfo['email'], '充值到账通知', $content);
				}
				
				$op = $_POST['unit'] == '1' ? 'pay' : 'pay_spend';
				showmsg(L('operation_success'), U($op), 2);
			}else{
				showmsg(L('operation_failure'));
			}
		    
		}else{	
			include $this->admin_tpl('recharge');
		}
	}

	
	
	/**
	 * 根据用户组获取最小经验
	 */	
	private function get_experience($groupid){ 
		if($groupid == 1) return 0;
		$member_group = get_groupinfo();
		if($member_group[$groupid-1]){
			return $member_group[$groupid-1]['experience']+1;
		}
		return 0;
	}

}