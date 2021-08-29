<?php
/**
 * 支付处理类   
 * 
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-07-03
 */
class payment {
	
	/**
     * 在线支付
     * @param int $paytype 支付类型
     * @param array $params 支付参数
     */
	public function pay($paytype, $params){
		$data = D('pay_mode')->field('enabled,action')->where(array('id'=>$paytype))->find();
		if(!$data || !$data['enabled']) showmsg('支付方式不存在或已禁用！', 'stop');
		$action = $data['action'];
		if(!$action || !method_exists($this, $action)) showmsg('支付方法不存在！', 'stop');
		$this->$action($params);
	}
	
	
	/**
     * 支付宝在线支付
     * @param array $params 支付参数
     */
	public function alipay($params){
		yzm_base::load_common('plugin/alipay/pagepay.php');
		$params = array(
            'subject' => $params['desc'],
            'out_trade_no' => $params['order_sn'],
            'total_amount' => $params['money'],
            'body' => $params['desc']
        );
		pagepay::pay($params);
	}
	
	
	/**
     * 支付宝支付异步回调校验
     * @param array $params 回调参数
     * @param array $order 订单信息
     */
	public function alipay_notify($params, $order){
		if($order['status'] != 0) return false;
		yzm_base::load_common('plugin/alipay/notify.php');
		return notify::check($params, $order);
	}
	
	
	/**
     * 支付宝回调状态校验
     * @param string $status 状态
     */
	public function alipay_check_status($status){
		if($status == 'TRADE_SUCCESS') return true;
		return false;
	}
	
	
	/**
     * 获取支付宝配置
     * @param string $name
     */
	public static function alipay_config(){
		if(!$alipay = getcache('alipay')){
			$data = D('pay_mode')->where(array('action'=>'alipay'))->find();
			if(!$data) showmsg('支付方式不存在！', 'stop');
			$alipay = string2array($data['config']);
			$alipay['notify_url'] = U('pay/index/pay_callback');  //异步通知地址
			$alipay['return_url'] = U('pay/index/pay_return');    //同步跳转
			$alipay['charset'] = 'UTF-8';  //编码格式
			$alipay['sign_type'] = 'RSA2';  //签名方式
			$alipay['gatewayUrl'] = 'https://openapi.alipay.com/gateway.do';  //支付宝网关
			setcache('alipay', $alipay);
		}	
		return $alipay;		
	}
	
	
	/**
     * 更新订单状态
     * @param array $order 订单信息
     * @param array $transaction 第三方交易单号
     */	
	public function update_order($order, $transaction){
		$result = D('order')->update(array('status'=>1, 'paytime'=>SYS_TIME, 'transaction'=>$transaction), array('id'=>$order['id']));
        if($result) {
			// 充值余额/积分
			if($order['quantity']){
				$point = yzm_base::load_model('point', 'member');
				$point->point_add($order['type'], $order['quantity'], 3, $order['userid'], $order['username'], 0, $order['desc'], '', false);				
			}
            return true;
        } else {
            return false;
        }
	}
	

	/**
     * 微信在线支付
     * @param array $params 支付参数
     */
	public function wechat($params){
		yzm_base::load_common('plugin/wxpay/native.php');  //如需微信支付模块联系QQ 214243830
        $params = array(
            'body' => $params['desc'],
            'out_trade_no' => $params['order_sn'],
            'total_fee' => $params['money']*100,  //微信支付单位为 ：分
            'product_id' => $params['order_id']
        );
        if(!class_exists('native')) showmsg('微信支付模块不存在，请联系官方购买！', 'http://www.yzmcms.com/');
        $img = native::getPayImage($params);
        include template('member', 'wxpay');
	}	

}