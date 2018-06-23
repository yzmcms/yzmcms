<?php
/**
 * 会员中心积分操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-17
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'member', 0);
yzm_base::load_sys_class('page','',0);

class member_pay extends common{
	
	function __construct() {
		parent::__construct();
	}

	
	/**
	 * 入账记录
	 */	
	public function init(){ 
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		$pay = D('pay');
		$total = $pay->where(array('userid'=>$userid))->total();
		$page = new page($total, 10);
		$data = $pay->where(array('userid'=>$userid))->order('id DESC')->limit($page->limit())->select();	
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'pay');
	}
	

	
	/**
	 * 消费记录
	 */	
	public function spend_record(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		$pay_spend = D('pay_spend');
		$total = $pay_spend->where(array('userid'=>$userid))->total();
		$page = new page($total, 10);
		$data = $pay_spend->where(array('userid'=>$userid))->order('id DESC')->limit($page->limit())->select();	
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'spend_record');
	}
	
	
	/**
	 * 消费积分
	 */	
	public function spend_point(){
		if(!isset($_GET['par'])) showmsg(L('lose_parameters'), 'stop');
		$par = new_html_special_chars($_GET['par']);
		$auth = string_auth($par,'DECODE');
		if(strpos($auth,'|')===false) showmsg(L('illegal_parameters'), 'stop');
		$auth_str = explode('|', $auth);
		$flag = $auth_str[0];
		if(!preg_match('/^([0-9]+)_([0-9]+)$/', $flag)) showmsg(L('illegal_parameters'), 'stop');
		$readpoint = intval($auth_str[1]);
		$http_referer = $auth_str[2];
		M('point')->point_spend('1',$readpoint,'7',$this->memberinfo['userid'],$this->memberinfo['username'],$flag);
		showmsg('支付成功，请刷新原页面！', $http_referer, 2);
	}

}