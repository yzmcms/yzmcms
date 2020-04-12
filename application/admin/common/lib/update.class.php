<?php
/**
 * 重要：请不要修改或删除本文件，否则系统无法正确接收系统漏洞或升级信息！！！
 */   
class update{
	
	public static function mysql_varsion() {
        return D('admin')->version();     
    }
	
	public static function notice_url($action = 'notice') {	
		$pars = array('action'=>$action,'siteurl'=>urlencode(SITE_URL),'sitename'=>urlencode(get_config('site_name')),'version'=>YZMCMS_VERSION,'software'=>urlencode($_SERVER['SERVER_SOFTWARE']),'os'=>PHP_OS,'php'=>phpversion(),'mysql'=>self::mysql_varsion(),'browser'=>urlencode($_SERVER['HTTP_USER_AGENT']),'username'=>urlencode($_SESSION['adminname']),);
		$data = http_build_query($pars);
        return base64_decode('aHR0cDovL3d3dy55em1jbXMuY29tL25vdGljZS91cGRhdGUucGhwPw==').$data;     
    }

    public static function check(){
    	$curl = curl_init(update::notice_url());
		curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT_MS, 1500);	
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);	
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$s = curl_exec($curl);
		curl_close($curl);
		echo $s;exit();
    }
}

function system_information($data) {
	$notice_url = U('public_home', 'up=1');
	$string = base64_decode('PHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiPiQoIiNib2R5IikucmVtb3ZlQ2xhc3MoImRpc3BsYXkiKTs8L3NjcmlwdD48ZGl2IGlkPSJ5em1jbXNfbm90aWNlIj48L2Rpdj48c2NyaXB0IHR5cGU9InRleHQvamF2YXNjcmlwdCIgc3JjPSJOT1RJQ0VfVVJMIj48L3NjcmlwdD4=');
	echo $data.str_replace('NOTICE_URL',$notice_url,$string);
}