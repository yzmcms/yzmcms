<?php 
/**
 * 支付宝异步支付回调处理类
 * author：袁志蒙
 * site: http://www.yzmcms.com
 */
 
yzm_base::load_common('plugin/alipay/service/AlipayTradeService.php');
yzm_base::load_model('payment', 'pay', 0);

class notify{
    /**
     * 异步通知校检, 包括验签和数据库信息与通知信息对比
     *
     * @param array  $params 回调参数
     * @param array  $order 订单信息
     */
    public static function check($params, $order){
        $config = payment::alipay_config();
        $alipaySevice = new AlipayTradeService($config);
        $signResult = $alipaySevice->check($params);

        $paramsResult = self::checkParams($params, $order, $config['app_id']);

        if($signResult && $paramsResult) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断两个数组是否一致
     */
    public static function checkParams($params, $order, $app_id){
        $notifyArr = array(
            'out_trade_no' => $params['out_trade_no'],
            'total_amount' => $params['total_amount'],
            'app_id'       => $params['app_id'],
        );
        $paramsArr = array(
            'out_trade_no' => $order['out_trade_no'],
            'total_amount' => $order['total_amount'],
            'app_id'       => $app_id,
        );
        $result = array_diff_assoc($paramsArr, $notifyArr);
        return empty($result) ? true : false;
    }
}