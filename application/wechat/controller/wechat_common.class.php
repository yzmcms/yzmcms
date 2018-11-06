<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class wechat_common extends common{
	
    protected $appid;
	protected $secret;
	

	function __construct() {
		
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

			$access_token = $this->https_request($url);
			
			if(isset($access_token['errcode'])) showmsg('获取access_token失败！errmsg：'.$access_token['errmsg'], 'stop');
			
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
					$jsonstr[$key] = urlencode(my_json_encode($value));
				}
			}  
			$jsonstr = urldecode(json_encode($jsonstr)); 
			$jsonstr = str_replace(']"', ']', str_replace('"[', '[', $jsonstr)); 
		}else{
			$jsonstr = json_encode($array, JSON_UNESCAPED_UNICODE);  //必须PHP5.4+  
		}	
		return $jsonstr;
	}



    /**
     *  https请求，支持get与post
     */
	protected function https_request($url, $data = '', $array = true){
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if($data){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $array ? json_decode($output, true) : $output;
	}


}