<?php 
/**
 * 支付宝统一收单交易退款接口
 * author：袁志蒙
 * site: http://www.yzmcms.com
 */
 
yzm_base::load_common('plugin/alipay/service/AlipayTradeService.php');
yzm_base::load_common('plugin/alipay/service/AlipayTradeRefundContentBuilder.php');
yzm_base::load_model('payment', 'pay', 0);

class refund{
	
    /**
     * 退款方法
     * @param  array $params 退款参数, 具体如下
     * @param string $params['trade_no']/$params['out_trade_no'] 商户订单号或支付宝单号其中之一
     * @param string $params['out_request_no'] 商户退款号(可选, 如部分退款，则此参数必传)
     * @param float  $params['refund_amount'] 退款金额
     * @param string $params['refund_reason'] 退款的原因说明 ,选填
     */
    public static function exec($params){
        self::checkParams($params);

        $RequestBuilder = self::builderParams($params);

        $config = payment::alipay_config();
        $aop    = new AlipayTradeService($config);

        $response = $aop->Refund($RequestBuilder);
        $response = json_decode(json_encode($response), true);

        if (!empty($response['code']) && $response['code'] != '10000') {
            showmsg('交易退款接口出错, 错误码: '.$response['code'].' 错误原因: '.$response['sub_msg'], 'stop');
        }

        return $response;
    }

    /**
     * 校检参数
     */
    private static function checkParams($params){
        if (empty($params['trade_no']) && empty($params['out_trade_no'])) {
            showmsg('商户订单号(out_trade_no)和支付宝单号(trade_no)不得通知为空', 'stop');
        }

        if (floatval(trim($params['refund_amount'])) <= 0) {
            showmsg('退款金额(refund_amount)为大于0的数', 'stop');
        }
    }

    /**
     * 构造请求参数
     */
    private static function builderParams($params){
        $RequestBuilder = new AlipayTradeRefundContentBuilder();

        if (isset($params['trade_no'])) {
            $RequestBuilder->setTradeNo($params['trade_no']);
        } else {
            $RequestBuilder->setOutTradeNo($params['out_trade_no']);
        }

        if (!empty($params['out_request_no'])) {
            $RequestBuilder->setOutRequestNo($params['out_request_no']);
        }

        $RequestBuilder->setRefundAmount($params['refund_amount']);
		
		if(isset($params['refund_reason'])){
			$RequestBuilder->setRefundReason($params['refund_reason']);
		}
		
        return $RequestBuilder;
    }
}