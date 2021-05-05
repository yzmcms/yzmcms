<?php
/**
 * qqapi.class.php  QQ接口Api，用于以QQ登录网站
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-12-10 
 */
 
class qqapi{

	private $appid,$appkey,$callback,$access_token,$openid;

	/**
	 * 构造方法，用于初始化QQ所需参数
	 * @param $appid, $appkey, $callback
	 */		
	public function __construct($appid, $appkey, $callback){
		$this->appid = $appid;
		$this->appkey = $appkey;
		$this->callback = $callback;
		$this->access_token= '';
		$this->openid = '';
	}

	
	/**
	 * 给外部调用的方法，供QQ登录
	 */	 		
	public function redirect_to_login() {
		//跳转到QQ登录页的接口地址, 不要更改!!
		$redirect = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=$this->appid&scope=&redirect_uri=".rawurlencode($this->callback);
		header("Location:$redirect");
	}


	/**
	 * 获得登录的 openid
	 * @param $code
	 * @return string
	 */	        
	public function get_openid($code){
		$url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=$this->appid&client_secret=$this->appkey&code=$code&redirect_uri=".rawurlencode($this->callback);
		$content = file_get_contents( $url);
		if (stristr($content,'access_token=')) {
			$params = explode('&',$content);
			$tokens = explode('=',$params[0]);
			$token  = $tokens[1];
			$this->access_token=$token;
			if ($token) {
				$url="https://graph.qq.com/oauth2.0/me?access_token=$token";
				$content=file_get_contents($url);
				$content=str_replace('callback( ','',$content);
				$content=str_replace(' );','',$content);
				$returns = json_decode($content);
				$openid = $returns->openid;
				$this->openid = $openid;
				$_SESSION["token2"]  = $openid;
			} else {
				$openid='';
			}
		} elseif (stristr($content,'error')) {
			$openid='';
		}
		return $openid;
	}

	
	/**
	 * 获取用户信息，返回一维数组
	 * @return array
	 */	
	public function get_user_info(){
		$url = "https://graph.qq.com/user/get_user_info?access_token=$this->access_token&oauth_consumer_key=$this->appid&openid=$this->openid";
		$content = file_get_contents($url);
		return json_decode($content, true);
	}
}