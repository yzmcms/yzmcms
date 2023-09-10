<?php
/**
 * 管理员后台订单管理
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-06-29
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class order extends common{
	
	public $paytype = array('1' => '支付宝', '2' => '微信');  //支付类型
	public $order_status = array('0' => '未付款', '1' => '已付款');  //订单状态
	
	/**
	 * 订单列表
	 */	
	public function init(){ 
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','username','money','addtime','paytime','status','paytype')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$order = D('order');
		$total = $order->total();
		$money_total = $order->where(array('type'=>2))->total();
		$point_total = $total-$money_total;
		$money_sum = $order->field('SUM(quantity)')->where(array('type'=>2))->one();
		$money_success = $order->field('SUM(quantity)')->where(array('status'=>1, 'type'=>2))->one();
		$point_sum = $order->field('SUM(quantity)')->where(array('type'=>1))->one();
		$point_success = $order->field('SUM(quantity)')->where(array('status'=>1, 'type'=>1))->one();
		$page = new page($total, 15);
		$data = $order->order("$of $or")->limit($page->limit())->select();			
		include $this->admin_tpl('order_list');
	}
	
	
	
	/**
	 * 订单记录搜索
	 */	
	public function order_search(){ 
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','username','money','addtime','paytime','status','paytype')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$order = D('order');
		$where = '1=1';
		if(isset($_GET['dosubmit'])){

			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';
			$status = isset($_GET["status"]) ? intval($_GET["status"]) : 99;
			$t_type = isset($_GET["t_type"]) ? intval($_GET["t_type"]) : 0;
			$type = isset($_GET["type"]) ? intval($_GET["type"]) : 1;
			
			if($status != 99) $where .= ' AND status = '.$status;

			if($t_type){
				$where .= ' AND `type` = '.$t_type;
			}
				
			if($searinfo){
				if($type == '1')
					$where .= ' AND username LIKE \'%'.$searinfo.'%\'';
				else
					$where .= ' AND order_sn = \''.$searinfo.'\'';
			}
			
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where .= " AND `addtime` >= '".strtotime($_GET["start"])."' AND `addtime` <= '".strtotime($_GET["end"])."' ";
			}			
		}
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $order->where($where)->total();

		// 根据充值类型分情况
		if($t_type){
			if($t_type == 2){
				$money_total = $total;
				$point_total = 0;
				$money_sum = $order->field('SUM(quantity)')->where($where)->one();
				if($status==99){
					$money_success = $order->field('SUM(quantity)')->where('status = 1 AND '.$where)->one();
				}elseif($status==1){
					$money_success = $money_sum;
				}else{
					$money_success = 0;
				}
				$point_sum = 0;
				$point_success = 0;
			}else{
				$money_total = 0;
				$point_total = $total;
				$money_sum = 0;
				$money_success = 0;
				$point_sum = $order->field('SUM(quantity)')->where($where)->one();
				if($status==99){
					$point_success = $order->field('SUM(quantity)')->where('status = 1 AND '.$where)->one();
				}elseif($status==1){
					$point_success = $point_sum;
				}else{
					$point_success = 0;
				}
			}
		}else{
			$money_total = $order->where($where.' AND `type` = 2')->total();
			$point_total = $total-$money_total;
			$money_sum = $order->field('SUM(quantity)')->where($where.' AND `type` = 2')->one();
			if($status==99){
				$money_success = $order->field('SUM(quantity)')->where('status = 1 AND `type` = 2 AND '.$where)->one();
			}elseif($status==1){
				$money_success = $money_sum;
			}else{
				$money_success = 0;
			}
			$point_sum = $order->field('SUM(quantity)')->where($where.' AND `type` = 1')->one();
			if($status==99){
				$point_success = $order->field('SUM(quantity)')->where('status = 1 AND `type` = 1 AND '.$where)->one();
			}elseif($status==1){
				$point_success = $point_sum;
			}else{
				$point_success = 0;
			}
		}
		$page = new page($total, 15);
		$data = $order->where($where)->order("$of $or")->limit($page->limit())->select();					
		include $this->admin_tpl('order_list');
	}
	
	
	/**
	 * 订单改价
	 */		
	public function change_price(){
		if(is_post()){
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$money = isset($_POST['money']) ? floatval($_POST['money']) : 0;
			if($money < 0.01) return_json(array('status'=>0,'message'=>'支付金额不能小于0.01元'));
			
			if(D('order')->update(array('money' => $money), array('id' => $id))){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}			
		}		
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$data = D('order')->where(array('id'=>$id))->find();
		include $this->admin_tpl('change_price');
	}
	
	
	/**
	 * 订单详情
	 */		
	public function order_details(){		
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$data = D('order')->where(array('id'=>$id))->find();
		include $this->admin_tpl('order_details');
	}
	
	
	/**
	 * 删除
	 */	
	public function del(){ 
		if(!isset($_GET['yzm_csrf_token']) || !check_token($_GET['yzm_csrf_token'])) return_message(L('token_error'), 0);
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		D('order')->delete(array('id'=>$id));
		showmsg(L('operation_success'),'',1);
	}
	

}