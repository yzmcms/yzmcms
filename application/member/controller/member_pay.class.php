<?php
/**
 * 会员中心财务中心
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-06-29
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
		$page = new page($total, 15);
		$data = $pay->where(array('userid'=>$userid))->order('id DESC')->limit($page->limit())->select();	
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'pay');
	}
	
	
	
	/**
	 * 在线充值
	 */	
	public function pay(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		$data = D('pay_mode')->field('`id`,`name`,`logo`,`desc`,`version`')->where(array('enabled'=>0))->order('id ASC')->select();
		include template('member', 'point_pay');
	}	
	
	
	/**
	 * 生成订单
	 */	
	public function create_order(){
		if(isset($_POST['dosubmit'])){
			if(empty($_SESSION['code']) || strtolower($_POST['code'])!=$_SESSION['code']){
				$_SESSION['code'] = '';
				showmsg(L('code_error'));
			}
			$_SESSION['code'] = '';
				
			$paytype = intval($_POST['paytype']);
			if(!$paytype) showmsg('请选择支付方式！', 'stop');
			$money = floatval($_POST['money']);
			if($money < 0.1) showmsg('最小支付0.1元人民币！', 'stop');
			if(isset($_POST['type']) && $_POST['type']==2){
				$type = 2;
				$quantity = $money;
				$desc = '余额充值：'.$quantity.'元';
			}else{
				$type = 1;
				$quantity = get_config('rmb_point_rate')*$money;
				$desc = '积分充值：'.$quantity.'点';
			}
			
			$data = array();
			$data['order_sn'] = create_tradenum();
			$data['userid'] = $this->memberinfo['userid'];
			$data['username'] = $this->memberinfo['username'];
			$data['addtime'] = SYS_TIME;
			$data['paytype'] = $paytype;
			$data['money'] = $money;
			$data['quantity'] = $quantity;
			$data['ip'] = getip();
			$data['desc'] = $desc;
			$data['type'] = $type;
			$order_id = D('order')->insert($data);
			$payment = yzm_base::load_model('payment', 'pay');
			$payment->pay($paytype, array('order_id'=>$order_id, 'order_sn'=>$data['order_sn'], 'money'=>$money, 'desc'=>$desc));
		}
		
	}


	/**
	 * 订单记录
	 */	
	public function order_list(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		$order = D('order');
		$total = $order->where(array('userid'=>$userid))->total();
		$page = new page($total, 15);
		$data = $order->where(array('userid'=>$userid))->order('id DESC')->limit($page->limit())->select();	
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'order_list');
	}
	
	
	/**
	 * 订单付款
	 */	
	public function order_pay(){
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$data = D('order')->where(array('id'=>$id))->find();
		if(!$data || $data['userid']!=$this->memberinfo['userid']) showmsg(L('lose_parameters'), 'stop');
		$payment = yzm_base::load_model('payment', 'pay');
		$payment->pay($data['paytype'], array('order_id'=>$id, 'order_sn'=>$data['order_sn'], 'money'=>$data['money'], 'desc'=>$data['desc']));
	}	
	

	
	/**
	 * 消费记录
	 */	
	public function spend_record(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		$pay_spend = D('pay_spend');
		$total = $pay_spend->where(array('userid'=>$userid))->total();
		$page = new page($total, 15);
		$data = $pay_spend->where(array('userid'=>$userid))->order('id DESC')->limit($page->limit())->select();	
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'spend_record');
	}
	
	
	/**
	 * 消费余额/积分
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
		$paytype = intval($auth_str[2]);
		$http_referer = $auth_str[3];
		M('point')->point_spend($paytype,$readpoint,'7',$this->memberinfo['userid'],$this->memberinfo['username'],$flag);
		showmsg('支付成功，请刷新原页面！', $http_referer, 2);
	}


	/**
	 * ajax异步获取订单状态
	 */	
	public function get_order_status(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		if(is_post()){
			$order_sn = isset($_POST['out_trade_no']) ? $_POST['out_trade_no'] : return_json(array('status'=>0, 'message'=>L('lose_parameters')));
			$data = D('order')->field('status,userid')->where(array('order_sn' => $order_sn))->find();
			if($data['userid'] != $userid) return_json(array('status'=>0, 'message'=>L('illegal_parameters')));
			return_json(array('status'=>$data['status'], 'message'=>L('operation_success')));
		}
	}

}