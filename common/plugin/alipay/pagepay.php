<?php 
/**
 * 支付宝电脑网站支付(扫码支付或账号支付)
 * author：袁志蒙
 * site: http://www.yzmcms.com
 */
 
yzm_base::load_common('plugin/alipay/service/AlipayTradeService.php');
yzm_base::load_common('plugin/alipay/service/AlipayTradePagePayContentBuilder.php');
yzm_base::load_model('payment', 'pay', 0);

class pagepay{
	
    /**
     * 主入口
     * @param array  $params 支付参数, 具体如下
     * @param string $params['subject'] 订单标题
     * @param string $params['out_trade_no'] 订单商户号
     * @param float  $params['total_amount'] 订单金额
     * @param float  $params['body'] 商品描述，可选
     */
    public static function pay($params){
        self::checkParams($params);

        $payRequestBuilder = new AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setSubject($params['subject']);
        $payRequestBuilder->setTotalAmount($params['total_amount']);
        $payRequestBuilder->setOutTradeNo($params['out_trade_no']);
		if(isset($params['body'])) $payRequestBuilder->setBody($params['body']);

        $config = payment::alipay_config();
        self::checkConfig($config);
        $aop = new AlipayTradeService($config);
        $aop->pagePay($payRequestBuilder, $config['return_url'],$config['notify_url']);
    }


    /**
     * 校检参数
     */
    private static function checkParams($params){
		
		if(version_compare(phpversion(), '5.5.0', '<')){
			showmsg('支付宝支持接口要求PHP版本必须>=5.5', 'stop');
		}
		
		if(!function_exists('openssl_sign')){
			showmsg('请开启openssl扩展', 'stop');
		}
		
        if (empty($params['out_trade_no'])) {
            showmsg('商户订单号(out_trade_no)必填', 'stop');
        }

        if (empty($params['subject'])) {
            showmsg('商品标题(subject)必填', 'stop');
        }

        if (floatval($params['total_amount']) <= 0) {
            showmsg('订单金额(total_amount)为大于0的数', 'stop');
        }

    }


    /**
     * 校检配置
     */
    private static function checkConfig($config){
        
        if (empty($config['app_id'])) {
            showmsg('支付宝应用ID未配置，请联系管理员！', 'stop');
        }

        if (empty($config['merchant_private_key'])) {
            showmsg('支付宝商户应用私钥未配置，请联系管理员！', 'stop');
        }

        if (empty($config['alipay_public_key'])) {
            showmsg('支付宝支付宝公钥未配置，请联系管理员！', 'stop');
        }

    }
}
