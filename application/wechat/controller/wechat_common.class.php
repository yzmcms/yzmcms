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
yzm_base::load_controller('common', 'admin', 0);

class wechat_common extends common{
	
    protected $appid;
	protected $secret;
	

	public function __construct() {
		
		parent::__construct();
		
		$this->appid = get_config('wx_appid');
		$this->secret = get_config('wx_secret');
		
		if($this->appid == '' || $this->secret == ''){
			showmsg('请先配置微信AppID和AppSecret！', U('config/init'));
		}
	}

	
    public function init(){
		
    }

	
	
    /**
     *  获取access_token
     */	
	protected function get_access_token(){
		
		if(!$access_token = getcache('wechat_access_token')){
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->secret;

			$access_token = https_request($url);
			
			if(isset($access_token['errcode'])) {
				if(is_ajax()){
					return_json(array('status'=>0,'message'=>'获取access_token失败！ErrMsg：'.$access_token['errmsg']));
				}
				showmsg('获取access_token失败！ErrMsg：'.$access_token['errmsg'], 'stop');
			}
			
			setcache('wechat_access_token', $access_token, 7000);
		}	
        	
		return $access_token['access_token'];
    }	
	
	
    /**
     *  不转义中文的json_encode
     */	
	protected function my_json_encode($array){
		if(version_compare(PHP_VERSION,'5.4.0','<')) {
			foreach($array as $key => $value){  
				if(!is_array($value)){
					$jsonstr[$key] = urlencode($value);
				}else{
					$jsonstr[$key] = urlencode($this->my_json_encode($value));
				}
			}  
			$jsonstr = urldecode(json_encode($jsonstr)); 
			$jsonstr = str_replace(']"', ']', str_replace('"[', '[', $jsonstr)); 
		}else{
			$jsonstr = json_encode($array, JSON_UNESCAPED_UNICODE);  //必须PHP5.4+  
		}	
		return $jsonstr;
	}

}