<?php
/**
 * global.func.php   公共函数库
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-05-25
 */
 

/**
 * http/https请求，支持get与post
 * @param  string  $url   请求url
 * @param  string  $data  POST请求，数组不为空
 * @param  boolean $array 是否返回数组形式
 * @param  int     $timeout 设置超时时间（毫秒）
 * @param  array   $header 请求头
 * @return array|string
 */
function https_request($url, $data = '', $array = true, $timeout = 2000, $header = array()){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_NOSIGNAL, true); 
    curl_setopt($curl, CURLOPT_TIMEOUT_MS, $timeout); 

    if($data){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

	if($header){
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	}
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    debug::addmsg(array('url'=>$url, 'data'=>$data), 2);
	if($output === false) {
		$curl_error = curl_error($curl);
		return $array ? array('status'=>0, 'message'=>$curl_error) : $curl_error;
	}
    curl_close($curl);
    return $array ? json_decode($output, true) : $output;
}


/**
 * 字符截取
 * @param $string 要截取的字符串
 * @param $length 截取长度
 * @param $dot	  截取之后用什么表示
 * @param $code	  编码格式，支持UTF8/GBK
 * @return string
 */
function str_cut($string, $length, $dot = '...', $code = 'utf-8') {
	$strlen = strlen($string);
	if($strlen <= $length) return $string;
	$string = str_replace(array(' ','&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵',' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
	$strcut = '';
	if($code == 'utf-8') {
		$length = intval($length-strlen($dot)-$length/3);
		$n = $tn = $noc = 0;
		while($n < strlen($string)) {
			$t = ord($string[$n]);
			if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
				$tn = 1; $n++; $noc++;
			} elseif(194 <= $t && $t <= 223) {
				$tn = 2; $n += 2; $noc += 2;
			} elseif(224 <= $t && $t <= 239) {
				$tn = 3; $n += 3; $noc += 2;
			} elseif(240 <= $t && $t <= 247) {
				$tn = 4; $n += 4; $noc += 2;
			} elseif(248 <= $t && $t <= 251) {
				$tn = 5; $n += 5; $noc += 2;
			} elseif($t == 252 || $t == 253) {
				$tn = 6; $n += 6; $noc += 2;
			} else {
				$n++;
			}
			if($noc >= $length) {
				break;
			}
		}
		if($noc > $length) {
			$n -= $tn;
		}
		$strcut = substr($string, 0, $n);
		$strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
	} else {
		$dotlen = strlen($dot);
		$maxi = $length - $dotlen - 1;
		$current_str = '';
		$search_arr = array('&',' ', '"', "'", '“', '”', '—', '<', '>', '·', '…','∵');
		$replace_arr = array('&amp;','&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;',' ');
		$search_flip = array_flip($search_arr);
		for ($i = 0; $i < $maxi; $i++) {
			$current_str = ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
			if (in_array($current_str, $search_arr)) {
				$key = $search_flip[$current_str];
				$current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
			}
			$strcut .= $current_str;
		}
	}
	return $strcut.$dot;
}


/**
 * xss过滤函数
 *
 * @param $string
 * @return string
 */
function remove_xss($string) { 
    $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);

    $parm1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');

    $parm2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload', 'onpointerout', 'onfullscreenchange', 'onfullscreenerror', 'onhashchange', 'onanimationend', 'onanimationiteration', 'onanimationstart', 'onmessage', 'onloadstart', 'ondurationchange', 'onloadedmetadata', 'onloadeddata', 'onprogress', 'oncanplay', 'oncanplaythrough', 'onended', 'oninput', 'oninvalid', 'onoffline', 'ononline', 'onopen', 'onpagehide', 'onpageshow', 'onpause', 'onplay', 'onplaying', 'onpopstate', 'onratechange', 'onsearch', 'onseeked', 'onseeking', 'onshow', 'onstalled', 'onstorage', 'onsuspend', 'ontimeupdate', 'ontoggle', 'ontouchcancel', 'ontouchend', 'ontouchmove', 'ontouchstart', 'ontransitionend', 'onvolumechange', 'onwaiting', 'onwheel', 'onbegin');

    $parm = array_merge($parm1, $parm2); 

	for ($i = 0; $i < sizeof($parm); $i++) { 
		$pattern = '/'; 
		for ($j = 0; $j < strlen($parm[$i]); $j++) { 
			if ($j > 0) { 
				$pattern .= '('; 
				$pattern .= '(&#[x|X]0([9][a][b]);?)?'; 
				$pattern .= '|(&#0([9][10][13]);?)?'; 
				$pattern .= ')?'; 
			}
			$pattern .= $parm[$i][$j]; 
		}
		$pattern .= '/i';
		$string = preg_replace($pattern, 'xxx', $string); 
	}
	return $string;
}	


/**
 * 安全过滤函数
 *
 * @param $string
 * @return string
 */
function safe_replace($string) {
	if(!is_string($string)) return $string;
	$string = trim($string);
	$string = str_replace('%20','',$string);
	$string = str_replace('%27','',$string);
	$string = str_replace('%2527','',$string);
	$string = str_replace('*','',$string);
	$string = str_replace('"','',$string);
	$string = str_replace("'",'',$string);
	$string = str_replace(';','',$string);
	$string = str_replace('<','&lt;',$string);
	$string = str_replace('>','&gt;',$string);
	$string = str_replace("{",'',$string);
	$string = str_replace('}','',$string);
	$string = str_replace('\\','',$string);
	return $string;
}	


/**
 * 获取当前页面完整URL地址
 * @return string
 */
function get_url() {
	$sys_protocal = is_ssl() ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? safe_replace($_SERVER['PHP_SELF']) : safe_replace($_SERVER['SCRIPT_NAME']);
	$path_info = isset($_SERVER['PATH_INFO']) ? safe_replace($_SERVER['PATH_INFO']) : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? safe_replace($_SERVER['REQUEST_URI']) : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.safe_replace($_SERVER['QUERY_STRING']) : $path_info);
	return $sys_protocal.HTTP_HOST.$relate_url;
}


/**
 * 获取请求ip
 * @return string
 */
function getip(){
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$ip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$ip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '127.0.0.1';
}


/**
 * 获取请求地区
 * @param $ip	IP地址
 * @param $is_array 是否返回数组形式
 * @return string|array
 */
function get_address($ip, $is_array = false){
	if($ip == '127.0.0.1') return '本地地址';
	$content = @file_get_contents('http://api.yzmcms.com/api/ip/?ip='.$ip);
	$arr = json_decode($content, true);
	if(is_array($arr) && !isset($arr['error'])){
		return $is_array ? $arr : $arr['country'].'-'.$arr['province'].'-'.$arr['city'];
	}else{
		return $is_array&&is_array($arr) ? $arr : '未知';
	}
}


/**
 * 检查IP是否匹配
 * @param  $ip_vague 要检查的IP或IP段，IP段(*)表示
 * @param  $ip       被检查IP
 * @return bool
 */
function check_ip_matching($ip_vague, $ip = ''){
	empty($ip) && $ip = getip();
	if(strpos($ip_vague,'*') === false){
		return $ip_vague == $ip;
	}
	if(count(explode('.', $ip_vague)) != 4) return false;
	$min_ip = str_replace('*', '0', $ip_vague);
	$max_ip = str_replace('*', '255', $ip_vague);
	$ip = ip2long($ip);
	if($ip>=ip2long($min_ip) && $ip<=ip2long($max_ip)){  
		return true; 
	}
	return false;
}



/**
* 产生随机字符串
* @param    int        $length  输出长度
* @param    string     $chars   可选的 ，默认为 0123456789
* @return   string     字符串
*/
function random($length, $chars = '0123456789') {
	$hash = '';
	$max = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}


/**
 * 生成随机字符串
 * @param string $lenth 长度
 * @return string 字符串
 */
function create_randomstr($lenth = 6) {
	return random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
}


/**
* 创建订单号
* @return   string     字符串
*/
function create_tradenum(){
	return date('YmdHis').random(4);
}


/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string){
	if(!is_array($string)) return addslashes($string);
	foreach($string as $key => $val) $string[$key] = new_addslashes($val);
	return $string;
}


/**
 * 返回经stripslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_stripslashes($string) {
	if(!is_array($string)) return stripslashes($string);
	foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
	return $string;
}


/**
 * 返回经htmlspecialchars处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @param $filter 需要排除的字段，格式为数组
 * @return mixed
 */
function new_html_special_chars($string, $filter = array()) {
	if(!is_array($string)) return htmlspecialchars($string,ENT_QUOTES,'utf-8');
	foreach($string as $key => $val){
		$string[$key] = $filter&&in_array($key, $filter) ? $val : new_html_special_chars($val, $filter);
	}
	return $string;
}


/**
 * 兼容PHP低版本的json_encode
 * @param  array   $array
 * @param  integer $options
 * @param  integer $depth 
 * @return string|false
 */
function new_json_encode($array, $options = 0, $depth = 0){
	if(version_compare(PHP_VERSION,'5.4.0','<')) {
	    $jsonstr = json_encode($array);
	}else{
	    $jsonstr = $depth ? json_encode($array, $options, $depth) : json_encode($array, $options);
	}   
	return $jsonstr;
}


/**
 * 转义 javascript 代码标记
 *
 * @param $str
 * @return string
 */
function trim_script($str) {
	if(is_array($str)){
		foreach ($str as $key => $val){
			$str[$key] = trim_script($val);
		}
 	}else{
 		$str = preg_replace ( '/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str );
		$str = preg_replace ( '/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str );
		$str = preg_replace ( '/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str );
		$str = str_replace ( 'javascript:', 'javascript：', $str );
 	}
	return $str;
}


/**
* 将字符串转换为数组
*
* @param	string	$data	字符串
* @return	array	返回数组格式，如果，data为空，则返回空数组
*/
function string2array($data) {
	$data = trim($data);
	if(empty($data)) return array();
	
	if(version_compare(PHP_VERSION,'5.4.0','<')) $data = stripslashes($data);
	$array = json_decode($data, true);
	return is_array($array) ? $array : array();
}


/**
* 将数组转换为字符串
*
* @param	array	$data		数组
* @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
* @return	string	返回字符串，如果，data为空，则返回空
*/
function array2string($data, $isformdata = 1) {
	if(empty($data)) return '';
	
	if($isformdata) $data = new_stripslashes($data);
	if(version_compare(PHP_VERSION,'5.4.0','<')){
		return addslashes(json_encode($data));
	}else{
		return json_encode($data, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT);
	}
}


/**
 * 兼容低版本的array_column
 * @param  $array      多维数组
 * @param  $column_key 需要返回值的列
 * @param  $index_key  可选。作为返回数组的索引/键的列。
 * @return array       返回一个数组，数组的值为输入数组中某个单一列的值。
 */
function yzm_array_column($array, $column_key, $index_key = null){
	if(function_exists('array_column')) return array_column($array, $column_key, $index_key);

    $result = array();
	foreach ($array as $key => $value) {
		if(!is_array($value)) continue;
        if($column_key){
        	if(!isset($value[$column_key])) continue;
        	$tmp = $value[$column_key];
        }else{
        	$tmp = $value;
        }
        if ($index_key) {
        	$key = isset($value[$index_key]) ? $value[$index_key] : $key;
        }
        $result[$key] = $tmp;
    }
    return $result;
}



/**
 * 判断email格式是否正确
 * @param $email
 * @return bool
 */
function is_email($email) {
	if(!is_string($email)) return false;
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}


/**
 * 判断手机格式是否正确
 * @param $mobile
 * @return bool
 */
function is_mobile($mobile) {
	return is_string($mobile) && preg_match('/1[3456789]{1}\d{9}$/',$mobile);
}


/**
 * 检测输入中是否含有错误字符
 *
 * @param string $string 要检查的字符串名称
 * @return bool
 */
function is_badword($string) {
	$badwords = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n","#");
	foreach($badwords as $value){
		if(strpos($string, $value) !== false) {
			return true;
		}
	}
	return false;
}


/**
 * 检查用户名是否符合规定
 *
 * @param string $username 要检查的用户名
 * @return 	boolean
 */
function is_username($username) {
	if(!is_string($username)) return false;
	$strlen = strlen($username);
	if(is_badword($username) || !preg_match("/^[a-zA-Z0-9_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+$/", $username)){
		return false;
	} elseif ( $strlen > 30 || $strlen < 3 ) {
		return false;
	}
	
	//新增用户名不全是数字时，不能以数字开头
	if(preg_match('/^\d*$/', $username)){
		return true;
	}
	if(preg_match('/^\d\S/', $username)){
		return false;
	}
	
	return true;
}



/**
 * 检查密码长度是否符合规定
 *
 * @param STRING $password
 * @return 	boolean
 */
function is_password($password) {
	$strlen = is_string($password) ? strlen($password) : 0;
	if($strlen >= 6 && $strlen <= 20) return true;
	return false;
}


/**
 * 取得文件扩展
 *
 * @param $filename 文件名
 * @return string
 */
function fileext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
}


/**
 * 是否为图片格式
 * @return bool
 */
function is_img($ext) {
	return in_array(strtolower($ext), array('png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'ico'));
}



/**
 * IE浏览器判断
 * @return bool
 */
function is_ie() {
	$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if((strpos($useragent, 'opera') !== false) || (strpos($useragent, 'konqueror') !== false)) return false;
	if(strpos($useragent, 'msie ') !== false) return true;
	return false;
}


/**
 * 判断字符串是否为utf8编码，英文和半角字符返回ture
 * @param $string
 * @return bool
 */
function is_utf8($string) {
	return preg_match('%^(?:
					[\x09\x0A\x0D\x20-\x7E] # ASCII
					| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
					| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
					| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
					| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
					| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
					| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
					| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
					)*$%xs', $string);
}



/**
 * 文件下载
 * @param $filepath 文件路径
 * @param $filename 文件名称
 * @return null
 */
function file_down($filepath, $filename = '') {
	if(!$filename) $filename = basename($filepath);
	if(is_ie()) $filename = rawurlencode($filename);
	$filetype = fileext($filename);
	$filesize = sprintf("%u", filesize($filepath));
	if(ob_get_length() !== false) @ob_end_clean();
	header('Pragma: public');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: pre-check=0, post-check=0, max-age=0');
	header('Content-Transfer-Encoding: binary');
	header('Content-Encoding: none');
	header('Content-type: '.$filetype);
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Content-length: '.$filesize);
	readfile($filepath);
	exit;
}


/**
* 传入日期格式或时间戳格式时间，返回与当前时间的差距，如1分钟前，2小时前，5月前，3年前等
* @param $date 分两种日期格式"2015-09-12 14:16:12"或时间戳格式"1386743303"
* @param int $type 1为时间戳格式，$type = 2为date时间格式
* @return string
*/
function format_time($date = 0, $type = 1) {
	if($type == 2) $date = strtotime($date);
    $second = SYS_TIME - $date;
    $minute = floor($second / 60) ? floor($second / 60) : 1; 
    if ($minute >= 60 && $minute < (60 * 24)) { 
        $hour = floor($minute / 60); 
    } elseif ($minute >= (60 * 24) && $minute < (60 * 24 * 30)) { 
        $day = floor($minute / ( 60 * 24)); 
    } elseif ($minute >= (60 * 24 * 30) && $minute < (60 * 24 * 365)) { 
        $month = floor($minute / (60 * 24 * 30));
    } elseif ($minute >= (60 * 24 * 365)) { 
        $year = floor($minute / (60 * 24 * 365)); 
    }
    if (isset($year)) {
        return $year . '年前';
    } elseif (isset($month)) {
        return $month . '月前';
    } elseif (isset($day)) {
        return $day . '天前';
    } elseif (isset($hour)) {
        return $hour . '小时前';
    } elseif (isset($minute)) {
        return $minute . '分钟前';
    }
}		


/**
 * 转换字节数为其他单位
 * @param  int	$size	字节大小
 * @param  int	$prec	小数点后的位数
 * @return string	返回大小
 */
function sizecount($size, $prec = 2) {
	$size = floatval($size);
	$arr = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
	$pos = 0;
	while ($size >= 1024) {
	    $size /= 1024;
	    $pos++;
	}
	return round($size, $prec).' '.$arr[$pos];
}


/**
 * 对数据进行编码转换
 * @param array|string $data       数组或字符串
 * @param string $input     需要转换的编码
 * @param string $output    转换后的编码
 * @return string|array
 */
function array_iconv($data, $input = 'gbk', $output = 'utf-8') {
	if (!is_array($data)) {
		return iconv($input, $output, $data);
	} else {
		foreach ($data as $key=>$val) {
			if(is_array($val)) {
				$data[$key] = array_iconv($val, $input, $output);
			} else {
				$data[$key] = iconv($input, $output, $val);
			}
		}
		return $data;
	}
}


/**
* 字符串加密/解密函数
* @param	string	$txt		字符串
* @param	string	$operation	ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
* @param	string	$key		密钥：数字、字母、下划线
* @param	string	$expiry		过期时间
* @return	string
*/
function string_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key != '' ? $key : C('auth_key'));
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(strtr(substr($string, $ckey_length), '-_', '+/')) : sprintf('%010d', $expiry ? $expiry + SYS_TIME : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || intval(substr($result, 0, 10)) - SYS_TIME > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.rtrim(strtr(base64_encode($result), '+/', '-_'), '=');
	}
}


/**
 * 获取内容中的图片
 * @param string $content 内容
 * @return string
 */
function match_img($content){
    preg_match("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png|webp))\\2/i", $content, $match);
    return isset($match[3]) ? $match[3] : ''; 
}


/**
 * 获取远程图片并把它保存到本地, 确定您有把文件写入本地服务器的权限
 * @param string $content 文章内容
 * @param string $targeturl 可选参数，对方网站的网址，防止对方网站的图片使用"/upload/1.jpg"这样的情况
 * @return string $content 处理后的内容
 */
function grab_image($content, $targeturl = ''){
	preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png|webp))\\2/i", $content, $img_array);
	$img_array = isset($img_array[3]) ? array_unique($img_array[3]) : array();
	
	if($img_array) {
		$path =  C('upload_file').'/'.date('Ym/d');
		$urlpath = SITE_PATH.$path;
		$imgpath =  YZMPHP_PATH.$path;
		if(!is_dir($imgpath)) @mkdir($imgpath, 0777, true);
	}
	
	foreach($img_array as $value){
		$val = $value;		
		if(strpos($value, 'http') === false){
			if(!$targeturl) continue;
			$value = $targeturl.$value;
		}	
		if(strpos($value, '?')){ 
			$value = explode('?', $value);
			$value = $value[0];
		}
		if(substr($value, 0, 4) != 'http'){
			continue;
		}
		$ext = fileext($value);
		if(!is_img($ext)) continue;
		$imgname = date('YmdHis').rand(100,999).'.'.$ext;
		$filename = $imgpath.'/'.$imgname;
		$urlname = $urlpath.'/'.$imgname;
		
		ob_start();
		@readfile($value);
		$data = ob_get_contents();
		ob_end_clean();
		$data && file_put_contents($filename, $data);
	 
		if(is_file($filename)){                         
			$content = str_replace($val, $urlname, $content);
		}
	}
	return $content;        
}


/**
 * 生成缩略图函数
 * @param  $imgurl 图片路径
 * @param  $width  缩略图宽度
 * @param  $height 缩略图高度
 * @param  $autocut 是否自动裁剪 默认不裁剪，当高度或宽度有一个数值为0时，自动关闭
 * @param  $smallpic 无图片是默认图片路径
 * @return string
 */
function thumb($imgurl, $width = 300, $height = 200 ,$autocut = 0, $smallpic = 'nopic.jpg') {
	global $image;
	$upload_url = SITE_PATH.C('upload_file').'/';
	$upload_path = YZMPHP_PATH.C('upload_file').'/';
	if(empty($imgurl)) return STATIC_URL.'images/'.$smallpic;
	if(!strpos($imgurl, '://')) $imgurl = SERVER_PORT.HTTP_HOST.$imgurl;
	$imgurl_replace= str_replace(SITE_URL.C('upload_file').'/', '', $imgurl); 
	if(!extension_loaded('gd') || strpos($imgurl_replace, '://')) return $imgurl;
	if(!is_file($upload_path.$imgurl_replace)) return STATIC_URL.'images/'.$smallpic;

	list($width_t, $height_t, $type, $attr) = getimagesize($upload_path.$imgurl_replace);
	if($width>=$width_t || $height>=$height_t) return $imgurl;

	$newimgurl = dirname($imgurl_replace).'/thumb_'.$width.'_'.$height.'_'.basename($imgurl_replace);

	if(is_file($upload_path.$newimgurl)) return $upload_url.$newimgurl;

	if(!is_object($image)) {
		yzm_base::load_sys_class('image','','0');
		$image = new image(1);
	}
	return $image->thumb($upload_path.$imgurl_replace, $upload_path.$newimgurl, $width, $height, '', $autocut) ? $upload_url.$newimgurl : $imgurl;
}


/**
 * 水印添加
 * @param $source 原图片路径
 * @param $target 生成水印图片途径，默认为空，覆盖原图
 * @return string
 */
function watermark($source, $target = '') {
	global $image_w;
	if(empty($source)) return $source;
	if(strpos($source, '://')) $source = str_replace(SERVER_PORT.HTTP_HOST, '', $source);
	if(!extension_loaded('gd') || strpos($source, '://')) return $source;
	
	if(!is_object($image_w)){
		yzm_base::load_sys_class('image','','0');
		$image_w = new image(1,1);
	}

	if(SITE_PATH == '/'){
		$source = YZMPHP_PATH.$source;
		$target = $target ? YZMPHP_PATH.$target : $source;
		$image_w->watermark($source, $target);
		return str_replace(YZMPHP_PATH, '', $target);
	}else{
		$source = YZMPHP_PATH.str_replace(SITE_PATH, '', $source);
		$target = $target ? YZMPHP_PATH.str_replace(SITE_PATH, '', $target) : $source;
		$image_w->watermark($source, $target);
		return SITE_PATH.str_replace(YZMPHP_PATH, '', $target);
	}
}


/**
 * 以httponly方式开启SESSION
 * @return bool
 */
function new_session_start(){
	// session_save_path(YZMPHP_PATH.'cache/sessions');
	ini_set('session.cookie_httponly', true);
	$session_name = session_name();
	if (isset($_COOKIE[$session_name]) && !preg_match('/^[-,a-zA-Z0-9]{1,128}$/', $_COOKIE[$session_name])) {
        unset($_COOKIE[$session_name]);
    } 
	return session_start();
}


/**
 * 设置 cookie
 * @param string $name     变量名
 * @param string $value    变量值
 * @param int $time    过期时间
 * @param boolean $httponly  
 */
function set_cookie($name, $value = '', $time = 0, $httponly = false) {
	$time = $time > 0 ? SYS_TIME + $time : $time;
	$name = C('cookie_pre').$name;
	$value = is_array($value) ? 'in_yzmphp'.string_auth(json_encode($value),'ENCODE',md5(YZMPHP_PATH.C('db_pwd'))) : string_auth($value,'ENCODE',md5(YZMPHP_PATH.C('db_pwd')));
	$httponly = $httponly ? $httponly : C('cookie_httponly');
	setcookie($name, $value, $time, C('cookie_path'), C('cookie_domain'), C('cookie_secure'), $httponly);
	$_COOKIE[$name] = $value;
}


/**
 * 获取 cookie
 * @param string $name     	  变量名，如果没有传参，则获取所有cookie
 * @param string $default     默认值，当值不存在时，获取该值
 * @return string
 */
function get_cookie($name = '', $default = '') {
	if(!$name) return $_COOKIE;
	$name = C('cookie_pre').$name;
	if(isset($_COOKIE[$name])){
		if(strpos($_COOKIE[$name],'in_yzmphp')===0){
			$_COOKIE[$name] = substr($_COOKIE[$name],9);
			return json_decode(MAGIC_QUOTES_GPC?stripslashes(string_auth($_COOKIE[$name],'DECODE',md5(YZMPHP_PATH.C('db_pwd')))):string_auth($_COOKIE[$name],'DECODE',md5(YZMPHP_PATH.C('db_pwd'))), true);
        }
		return string_auth(safe_replace($_COOKIE[$name]),'DECODE',md5(YZMPHP_PATH.C('db_pwd')));
	}else{
		return $default;
	}	
}


/**
 * 删除 cookie
 * @param string $name     变量名，如果没有传参，则删除所有cookie
 * @return bool
 */
function del_cookie($name = '') {	
	if(!$name){
		foreach($_COOKIE as $key => $val) { 
			setcookie($key, '', SYS_TIME - 3600, C('cookie_path'), C('cookie_domain'), C('cookie_secure'), C('cookie_httponly'));
			unset($_COOKIE[$key]);
		}		
	}else{
		$name = C('cookie_pre').$name;
		if(!isset($_COOKIE[$name])) return true;
		setcookie($name, '', SYS_TIME - 3600, C('cookie_path'), C('cookie_domain'), C('cookie_secure'), C('cookie_httponly'));
		unset($_COOKIE[$name]);
	}
	return true;
}


/**
 * 新版获取配置参数
 * @param string $key  要获取的配置荐，支持格式例如 database 或 database.host
 * @param string $default  默认配置。当获取配置项目失败时该值发生作用。
 * @return mixed
 */
function config($key = '', $default = '') {
    $k = explode('.', $key);
    static $cfg = array(); 
    if (!isset($cfg[$k[0]])) {
        $path = YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.$k[0].'.php';
        if (is_file($path)) { 
            $cfg[$k[0]] = include $path;
        }else{
            return $default;
        }
    }
    return count($k)==1 ? $cfg[$k[0]] : (isset($cfg[$k[0]][$k[1]]) ? $cfg[$k[0]][$k[1]] : $default);
}


/**
 * 用于实例化一个model对象
 * @param string $classname 模型名
 * @param string $m 模块
 * @return object
 */	
function M($classname, $m = ''){
	return yzm_base::load_model($classname, $m);
}



/**
 * 用于实例化一个数据表对象  如：D('admin');
 * @param $tabname	 表名称
 * @return object
 */	
function D($tabname){
	static $_tables  = array();
	if(isset($_tables[$tabname])) return $_tables[$tabname];
	yzm_base::load_sys_class('db_factory', '', 0);
	$object = db_factory::get_instance()->connect($tabname);
	$_tables[$tabname] = $object;
	return $object;
}



/**
 * URL组装 支持不同URL模式
 * @param string $url URL表达式，格式：'[模块/控制器/方法]'
 * @param string|array $vars 传入的参数，支持字符串和数组
 * @param boolean $domain 是否显示域名，默认根据URL模式自动展示
 * @param string|boolean $suffix 伪静态后缀，默认为true表示获取配置值
 * @return string
 */
function U($url='', $vars='', $domain=null, $suffix=true) {	
	$url = trim($url, '/');
	$arr = explode('/', $url);
	$num = count($arr);

	$string = SITE_PATH;
	if(URL_MODEL == 0){
		$string .= 'index.php?';
		if($num == 3){
			$string .= 'm='.$arr[0].'&c='.$arr[1].'&a='.$arr[2];
		}elseif($num == 2){
			$string .= 'm='.ROUTE_M.'&c='.$arr[0].'&a='.$arr[1];
		}else{
			$string .= 'm='.ROUTE_M.'&c='.ROUTE_C.'&a='.$arr[0];
		}

		if($vars){
			if(is_array($vars)) $vars = http_build_query($vars);
			$string .= '&'.$vars;
		}
	}else{
		if(URL_MODEL == 1) $string .= 'index.php?s=';
		if(URL_MODEL == 4) $string .= 'index.php/';
		
		if($num == 3){
			$string .= $url;
		}elseif($num == 2){
			$string .= ROUTE_M.'/'.$url;
		}else{
			$string .= ROUTE_M.'/'.ROUTE_C.'/'.$url;
		}

		if($vars){
			if(!is_array($vars)) parse_str($vars, $vars);			
            foreach ($vars as $var => $val){
                if(!is_array($val) && trim($val) !== '') $string .= '/'.urlencode($var).'/'.urlencode($val);
            } 
		}
        $string .= $suffix === true ? C('url_html_suffix') : $suffix;		
	}

	$string = $domain===null&&URL_MODEL==3 ? SERVER_PORT.HTTP_HOST.$string : ($domain ? SERVER_PORT.HTTP_HOST.$string : $string);
	
	return $string;
}


/**
 * 获取配置参数
 * @param string $key  要获取的配置荐
 * @param string $default  默认配置。当获取配置项目失败时该值发生作用。
 * @return mixed
 */
function C($key = '', $default = '') {
    static $configs = array(); 
	if (isset($configs['config'])) {
		if (empty($key)) {
			return $configs['config'];
		} elseif (isset($configs['config'][$key])) {
			return $configs['config'][$key];
		} else {
			return $default;
		}
	}
	$path = YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
	if (is_file($path)) { 
		$configs['config'] = include $path;
	}
	if (empty($key)) {
		return $configs['config'];
	} elseif (isset($configs['config'][$key])) {
		return $configs['config'][$key];
	} else {
		return $default;
	}
}



/**
 * 获取和设置语言定义
 * @param	string		$language	语言变量
 * @param	string		$module     模块名
 * @return	string		语言字符
 */
function L($language = '', $module = ''){
	static $_lang = array();
	if(empty($language)) return $_lang;
        
	$lang = C('language');
	$module = empty($module) ? ROUTE_M : $module;
	if(!$_lang) { 
		$sys_lang = require(YP_PATH.'language'.DIRECTORY_SEPARATOR.$lang.'.lang.php');
		$module_lang = array();
		if(is_file(APP_PATH.$module.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.$lang.'.lang.php')){
			$module_lang = require(APP_PATH.$module.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'language'.DIRECTORY_SEPARATOR.$lang.'.lang.php');
		}
		$_lang = array_merge($sys_lang, $module_lang);
	}
	if(array_key_exists($language,$_lang)) {
		return $_lang[$language];
	}
	
	return $language;
}


/**
 * 打印各种类型的数据，调试程序时使用。
 * @param mixed $var 变量，支持传入多个
 * @return null
 */
function P($var){
	foreach(func_get_args() as $value){
		echo '<pre style="background:#18171B;color:#EBEBEB;border-radius:3px;padding:5px 8px;margin:8px 0;font:12px Menlo, Monaco, Consolas, monospace;word-wrap:break-word;white-space:pre-wrap">';
		var_dump($value);
		echo '</pre>';
	}
	return null;
}


/**
 * 用于临时屏蔽debug信息
 * @return null
 */	
function debug(){
	defined('DEBUG_HIDDEN') or define('DEBUG_HIDDEN', true);
}


/**
 * 用于设置模块的主题
 * @return null
 */	
function set_module_theme($theme = 'default'){
	defined('MODULE_THEME') or define('MODULE_THEME', $theme);
}


/**
 * 写入缓存
 * @param $name 缓存名称
 * @param $data 缓存数据
 * @param $timeout 过期时间
 * @return    int
 */
function setcache($name, $data, $timeout=0) {
	yzm_base::load_sys_class('cache_factory','',0);
	$cache = cache_factory::get_instance()->get_cache_instances();
	return $cache->set($name, $data, $timeout);
}


/**
 * 读取缓存
 * @param string $name 缓存名称
 * @return    string
 */
function getcache($name) {
	yzm_base::load_sys_class('cache_factory','',0);
	$cache = cache_factory::get_instance()->get_cache_instances();
	return $cache->get($name);
}


/**
 * 删除缓存
 * @param string $name 缓存名称
 * @param $flush 是否清空所有缓存
 * @return    bool
 */
function delcache($name, $flush = false) {
	yzm_base::load_sys_class('cache_factory','',0);
	$cache = cache_factory::get_instance()->get_cache_instances();
	return !$flush ? $cache->delete($name) : $cache->flush();
}


/**
 * 模板调用
 * @param  string $module   模块名
 * @param  string $template 模板名称
 * @param  string $theme    强制模板风格
 * @return void           
 */
function template($module = '', $template = 'index', $theme = ''){
	if(!$module) $module = 'index';
	$template_c = YZMPHP_PATH.'cache'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR;
	$theme = !$theme ? (!defined('MODULE_THEME') ? C('site_theme') : MODULE_THEME) : $theme;
	$template_path = APP_PATH.$module.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$theme.DIRECTORY_SEPARATOR;
    $filename = $template.'.html';
	$tplfile = $template_path.$filename;   
	if(!is_file($tplfile)) {
		$template = APP_DEBUG ? str_replace(YZMPHP_PATH, '', $tplfile) : basename($tplfile);
		showmsg($template.L('template_does_not_exist'), 'stop');			                      
	}	
	if(!is_dir(YZMPHP_PATH.'cache'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR)){
		@mkdir(YZMPHP_PATH.'cache'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR, 0777, true);
	}
	$template = basename($template).'_'.md5($template_path.$template);	
	$template_c = $template_c.$template.'.tpl.php'; 		
	if(!is_file($template_c) || filemtime($template_c) < filemtime($tplfile)) {
		$yzm_tpl = yzm_base::load_sys_class('yzm_tpl');
		$compile = $yzm_tpl->tpl_replace(@file_get_contents($tplfile));
		file_put_contents($template_c, $compile);
	}
	return $template_c;
}


/**
 * 下发队列任务
 * @param  string $job    队列任务类名称
 * @param  array  $params 传入的参数
 * @param  string $queue  队列名称
 * @return string|false   任务id
 */
function dispatch($job, $params = array(), $queue = ''){
    $res = yzm_base::load_job($job, 0);
    if(!$res) return $res;

    $object = new $job($params);
    yzm_base::load_sys_class('queue_factory','',0);

    $data = array(
        'uuid' => md5(create_randomstr()),
        'job' => $job,
        'object' => serialize($object),
        'attempts' => 0,
        'create_time' => SYS_TIME
    );
    queue_factory::get_instance()->lpush($queue ? $queue : trim(C('queue_name')), $data);
    return $data['uuid'];
}


/**
 *  提示信息页面跳转
 *
 * @param     string  $msg      消息提示信息
 * @param     string  $gourl    跳转地址,stop为停止
 * @param     int     $limittime  限制时间
 * @return    void
 */
function showmsg($msg, $gourl = '', $limittime = 3){
	application::showmsg($msg, $gourl, $limittime);
	if(APP_DEBUG){
		debug::stop();
		debug::message();
	}
	exit;
}


/**
 * 根据请求方式自动返回信息
 * @param   $message 
 * @param   $status  
 * @param   $url  
 * @return  void           
 */
function return_message($message, $status = 1, $url = ''){
	$data = array('status'=>$status, 'message'=>$message);
	if($url) $data['url'] = $url;
	is_ajax() && return_json($data);
	showmsg($message, $url ? $url : ($status ? '' : 'stop'));
}


/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code){
    static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded',
            550 => 'Can not connect to MySQL server'
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:'.$code.' '.$_status[$code]);
    }
}	


/**
 * 生成验证key
 * @param $prefix   前缀
 * @return string
 */
function make_auth_key($prefix) {
	return md5($prefix.YZMPHP_PATH.C('auth_key'));
}


/**
 * 返回json数组，默认提示 “数据未修改！” 
 * @param $arr
 * @param $show_debug
 * @return string
 */
function return_json($arr = array(), $show_debug = false){
	header("X-Powered-By: YZMPHP/YzmCMS.");
    header('Content-Type:application/json; charset=utf-8');
    if(!$arr) $arr = array('status'=>0,'message'=>L('data_not_modified'));
	if(APP_DEBUG || $show_debug) $arr = array_merge($arr, debug::get_debug());
    exit(new_json_encode($arr, JSON_UNESCAPED_UNICODE));
}


/**
 * 记录日志
 * @param $message 日志信息
 * @param $filename 文件名称
 * @param $ext 文件后缀
 * @param $path 日志路径
 * @return bool
 */
function write_log($message, $filename = '', $ext = '.log', $path = '') {
	$message = is_array($message) ? new_json_encode($message, JSON_UNESCAPED_UNICODE) : $message;
	$message = date('H:i:s').' '.$message."\r\n";
	if(!$path) $path = YZMPHP_PATH.'cache/syslog';
	if(!is_dir($path)) @mkdir($path, 0777, true);
	
	if(!$filename) $filename = date('Ymd').$ext;
	
	return error_log($message, 3, $path.DIRECTORY_SEPARATOR.$filename);
}


/**
 * 记录错误日志
 * @param $err_arr 错误信息
 * @param $path 日志路径
 * @return bool
 */
function write_error_log($err_arr, $path = '') {
	if(!C('error_log_save') || defined('CLOSE_WRITE_LOG')) return false;
	$err_arr = is_array($err_arr) ? $err_arr : array($err_arr);
	$message[] = date('Y-m-d H:i:s');
	$message[] = get_url();
	$message[] = getip();
	if(isset($_POST) && !empty($_POST)) $message[] = new_json_encode($_POST, JSON_UNESCAPED_UNICODE);
	$message = array_merge($message, $err_arr);
	$message = join(' | ', $message)."\r\n";
	if(!$path) $path = YZMPHP_PATH.'cache';
	if(!is_dir($path)) @mkdir($path, 0777, true);
	$file = $path.DIRECTORY_SEPARATOR.'error_log.php';
	if(is_file($file) && filesize($file)>20971520){
		@rename($file, $path.DIRECTORY_SEPARATOR.'error_log'.date('YmdHis').rand(100,999).'.php') ;
	}
	if(!is_file($file)){
		error_log("<?php exit;?>\r\n", 3, $file);
	}
	return error_log($message, 3, $file);
}


/**
 * URL重定向
 * @param string $url 重定向的URL地址
 * @param integer $time 重定向的等待时间（秒）
 * @param string $msg 重定向前的提示信息
 * @return void
 */
function redirect($url, $time=0, $msg='') {
    if (empty($msg))
        $msg    = '系统将在'.$time.'秒之后自动跳转到'.$url.'！';
    if (!headers_sent()) {
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header('refresh:'.$time.';url='.$url);
            echo($msg);
        }
        exit();
    } else {
        $str    = '<meta http-equiv="Refresh" content="'.$time.';URL='.$url.'">';
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}


/**
 * 获取输入数据
 * @param string $key 获取的变量名
 * @param mixed $default 默认值
 * @param string $function 处理函数
 * @return mixed
 */
function input($key = '', $default = '', $function = ''){
	if ($pos = strpos($key, '.')) {
		list($method, $key) = explode('.', $key, 2);
		if (!in_array($method, array('get', 'post', 'request'))) {
			$key    = $method . '.' . $key;
			$method = 'param';
		}
	} else {
		$method = 'param';
	}

	$method = strtolower($method);

	if ($method == 'get') {
		return empty($key) ? $_GET : (isset($_GET[$key]) ? ($function ? $function($_GET[$key]) : $_GET[$key]) : $default);
	} elseif ($method == 'post') {
		$_POST = $_POST ? $_POST : (file_get_contents('php://input') ? json_decode(file_get_contents('php://input'), true) : array());
		return empty($key) ? $_POST : (isset($_POST[$key]) ? ($function ? $function($_POST[$key]) : $_POST[$key]) : $default);
	} elseif ($method == 'request') {
		return empty($key) ? $_REQUEST : (isset($_REQUEST[$key]) ? ($function ? $function($_REQUEST[$key]) : $_REQUEST[$key]) : $default);
	} elseif ($method == 'param') {
		$param = array_merge($_GET, is_array($_POST)?$_POST:array(), $_REQUEST);
		return empty($key) ? $param : (isset($param[$key]) ? ($function ? $function($param[$key]) : $param[$key]) : $default);
	} else {
		return false;
	}
}


/**
 * 判断是否SSL协议
 * @return bool
 */
function is_ssl() {
    if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
        return true;
    }elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }elseif(isset($_SERVER['REQUEST_SCHEME']) && ('https' == strtolower($_SERVER['REQUEST_SCHEME']))) {
        return true;
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ('https' == strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']))) {
        return true;
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_SCHEME']) && ('https' == strtolower($_SERVER['HTTP_X_FORWARDED_SCHEME']))) {
        return true;
    }
    return false;
}


/**
 * 判断是否为POST请求
 * @return bool
 */
function is_post(){
	return 'POST' == $_SERVER['REQUEST_METHOD'];
}


/**
 * 判断是否为GET请求
 * @return bool
 */
function is_get(){
	return 'GET' == $_SERVER['REQUEST_METHOD'];
}


/**
 * 判断是否为PUT请求
 * @return bool
 */
function is_put(){
	return 'PUT' == $_SERVER['REQUEST_METHOD'];
}


/**
 * 判断是否为AJAX请求
 * @return bool
 */
function is_ajax(){
	return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest' ? true : false;
}


/**
 * 创建TOKEN，确保已经开启SESSION
 * @param bool $isinput 是否返回input
 * @return string
 */
function creat_token($isinput = true){
	if(!isset($_SESSION['yzm_csrf_token'])) $_SESSION['yzm_csrf_token'] = create_randomstr(8);
	return $isinput ? '<input type="hidden" name="token" value="'.$_SESSION['yzm_csrf_token'].'">' : $_SESSION['yzm_csrf_token'];
}


/**
 * 验证TOKEN，确保已经开启SESSION
 * @param string $token 
 * @param bool $delete
 * @return bool
 */
function check_token($token, $delete=false){
	if(!$token || !isset($_SESSION['yzm_csrf_token']) || $token!=$_SESSION['yzm_csrf_token']) return false;
	if($delete) unset($_SESSION['yzm_csrf_token']);
	return true;
}