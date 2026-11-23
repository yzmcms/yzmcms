<?php 
/**
 * collection.class.php  文章采集类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-08-16 
 */
	
class collection {
	
	public static $url;
	
	/**
	 * 获取目标网址HTML源码
	 * @param $url 目标网址url
	 * @return string
	 */
    public static function get_content($url, $depth = 0) {
		
		self::$url = $url;
		
		// 仅允许 http/https，且目标解析结果必须为公网 IP
		$target = self::_resolve_target($url);
		if (!$target) return '';

		// 限制重定向跳转次数，防止死循环
		if ($depth > 5) return '';

        $content = '';
        if (extension_loaded('curl')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); // 手动跟随，逐跳校验目标 IP
            curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            // 将连接钉死在校验通过的 IP 上，防止 DNS 重绑定
            curl_setopt($ch, CURLOPT_RESOLVE, array($target['host'].':'.$target['port'].':'.$target['ip']));
            $content = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
            safe_curl_close($ch);

            // 手动处理 3xx 跳转，每一跳都会重新做公网 IP 校验
            if ($httpCode >= 300 && $httpCode < 400 && $redirect) {
                $redirect = self::_absolutize_url($url, $redirect);
                return $redirect ? self::get_content($redirect, $depth + 1) : '';
            }
        } else {
            $context = stream_context_create(array('http' => array(
                'follow_location' => 0,
                'max_redirects'   => 0,
                'timeout'         => 15,
            )));
            $content = @file_get_contents($url, false, $context);
        }
		
        return trim($content);
    }


	/**
	 * 解析并校验目标地址，返回可安全连接的 host/port/ip
	 * 拒绝私网、回环、链路本地(云元数据)、保留段及 CGNAT 地址
	 * @param string $url
	 * @return array|false
	 */
	protected static function _resolve_target($url) {
		if (!is_string($url) || !preg_match('/^https?:\/\//i', $url)) return false;
		$parse = @parse_url($url);
		if (!$parse || empty($parse['host'])) return false;

		$scheme = strtolower($parse['scheme']);
		$host = strtolower(trim($parse['host'], '[]'));
		$port = isset($parse['port']) ? intval($parse['port']) : ($scheme === 'https' ? 443 : 80);

		if ($host === '' || substr($host, -1) === '.' || in_array($host, array('localhost', '127.0.0.1', '::1'), true)) {
			return false;
		}
		// 仅允许 80/443 端口，避免探测内网服务
		if (!in_array($port, array(80, 443), true)) return false;

		$ips = array();
		if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$ips[] = $host;
		} else {
			$records = @dns_get_record($host, DNS_A);
			if ($records) {
				foreach ($records as $r) {
					if (!empty($r['ip'])) $ips[] = $r['ip'];
				}
			}
			if (!$ips) {
				$tmp = gethostbyname($host);
				if ($tmp && $tmp != $host) $ips[] = $tmp;
			}
		}
		if (!$ips) return false;

		$connectIp = '';
		foreach ($ips as $ip) {
			if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) continue;
			// 任一解析结果为内网/保留地址即拒绝，防止 DNS 多记录绕过
			if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				return false;
			}
			if ((ip2long($ip) & 0xFFC00000) === ip2long('100.64.0.0')) return false;
			$connectIp = $ip;
			break;
		}
		if (!$connectIp) return false;

		return array('host' => $host, 'port' => $port, 'ip' => $connectIp);
	}


	/**
	 * 将重定向 Location 解析为绝对 URL
	 * @param string $base 当前地址
	 * @param string $location 跳转地址
	 * @return string
	 */
	protected static function _absolutize_url($base, $location) {
		$location = trim($location);
		if ($location === '') return '';
		if (preg_match('/^https?:\/\//i', $location)) return $location;

		$parse = @parse_url($base);
		if (!$parse || empty($parse['host'])) return '';

		$scheme = isset($parse['scheme']) ? strtolower($parse['scheme']) : 'http';
		$host = $parse['host'];
		$port = isset($parse['port']) ? ':'.$parse['port'] : '';

		if (substr($location, 0, 2) === '//') {
			return $scheme.':'.$location;
		}
		if (substr($location, 0, 1) === '/') {
			return $scheme.'://'.$host.$port.$location;
		}
		$path = isset($parse['path']) ? $parse['path'] : '/';
		$dir = substr($path, 0, strrpos($path, '/') + 1);
		return $scheme.'://'.$host.$port.$dir.$location;
	}



	/**
	 * 获取区间内的HTML源码
	 * @param $html 目标网址的HTML源码
	 * @param $start 区间开始的html标识
	 * @param $end 区间结束的html标识
	 * @return string
	 */		
    public static function get_sub_content($html, $start, $end) {
		if (empty($html)) return '';
        if ($start == '' || $end == '') {
            return $html;
        }

        $html = str_replace(array("\r", "\n"), "", $html);
		$start = str_replace(array("\r", "\n"), "", $start);
		$end = str_replace(array("\r", "\n"), "", $end);		
		
        $html = explode(trim($start), $html);
		if(is_array($html) && isset($html[1])) $html = explode(trim($end), $html[1]);
		return trim($html[0]);
    }
	
    
	
	/**
	 * 根据区间内的HTML源码，提取文章的URL和TITLE
	 * @param $html 区间内的HTML源码
	 * @param $url_contain 网址中必须包含
	 * @param $url_except 网址中不能包含
	 * @return array
	 */	
    public static function get_all_url($html, $url_contain='', $url_except='') {
		
		$html = str_replace(array("\r", "\n"), '', $html);
		$html = str_replace(array("</a>", "</A>"), "</a>\n", $html);
        preg_match_all('/<a ([^>]*)>([^\/a>].*)<\/a>/i', $html, $out);
		
		$data = array();
		
		foreach ($out[1] as $k=>$v) {
			if (preg_match('/href=[\'"]?([^\'" ]*)[\'"]?/i', $v, $match_out)) {
				if ($url_contain) {
					if (strpos($match_out[1], $url_contain) === false) {
						continue;
					} 
				}

				if ($url_except) {
					if (strpos($match_out[1], $url_except) !== false) {
						continue;
					} 
				}
				$url2 = $match_out[1];
				$url2 = self::url_check($url2, self::$url);
				
				$title = strip_tags($out[2][$k]);

				if(empty($url2) || empty($title)) continue;
				
				$data['url'][$k] = $url2;
				$data['title'][$k] = $title;

			} else {
				continue;
			}
		}
        
		//去除重复数据
        $arr = array();		
		$data['url'] = array_unique($data['url']);
		foreach($data['url'] as $k => $v){
			$arr['url'][] = $data['url'][$k];
			$arr['title'][] = $data['title'][$k];
		}
		
        return $arr;
    }


	/**
	 * 根据文章内容页的HTML源码，过滤并提取相关信息
	 * @param $html 文章内容页面的HTML源码
	 * @return array
	 */	
    public static function get_filter_html($html, $config = array()) {
        $data =array();
		
		//获取内容
		$data['title'] = self::replace_item(self::get_sub_content($html, $config['title_rule'][0], $config['title_rule'][1]), $config['title_html_rule']);
		
		//获取时间
		if($config['time_rule'][0]!='' && $config['time_rule'][1]!=''){
            $data['inputtime'] = self::replace_item(self::get_sub_content($html, $config['time_rule'][0], $config['time_rule'][1]), $config['time_html_rule']);	
            $data['inputtime'] = !empty($data['inputtime']) ? strtotime($data['inputtime']) : SYS_TIME;			
		}else{
			$data['inputtime'] = SYS_TIME;
		}
		

		//获取内容
		$data['content'] = self::replace_item(self::get_sub_content($html, $config['content_rule'][0], $config['content_rule'][1]), $config['content_html_rule']);

		return $data;
		
    }
	
	
	/**
	 * 根据配置项，切割数据
	 * @param $separator 以什么字符分割字符串
	 * @param $string 要处理的字符串
	 * @return array
	 */		
	public static function myexp($separator, $string){
		if(empty($string)) return array('','');
		$string = str_replace(array("\r", "\n"),'',$string);
		$arr = explode($separator, $string);
		if($arr[count($arr)-1] == '') unset($arr[count($arr)-1]);
		return $arr;
	}
	

    /**
	 * 过滤代码
	 * @param string $html  HTML代码
	 * @param array $config 过滤配置
	 * @return string
	 */
	protected static function replace_item($html, $config) {
		if (!is_array($config) || empty($config)) return $html;

		$patterns = $replace = array();
		foreach($config as $k=>$v){
			$patterns[] = '/'.str_replace('/', '\/', $v).'/i';
			$replace[] = '';
		}

		return trim(preg_replace($patterns, $replace, $html));
	}

	
	
    /**
	 * URL地址检查
	 * @param string $url      需要检查的URL
	 * @param string $baseurl  基本URL
	 * @return string
	 */
	protected static function url_check($url, $baseurl) {
        $urlinfo = parse_url($baseurl);
		if(strpos($url, '://') === false) {
			if($url[0] == '/'){
				$url = $urlinfo['scheme'].'://'.$urlinfo['host'].$url;
			}else{
				$baseurl = $urlinfo['scheme'].'://'.$urlinfo['host'].(substr($urlinfo['path'], -1, 1) === '/' ? substr($urlinfo['path'], 0, -1) : str_replace('\\', '/', dirname($urlinfo['path']))).'/';
				$url = $baseurl.$url;
			}
		}
	
		$arr = explode('://', $url);
		if(!in_array($arr[0], array('http', 'https'))) showmsg('链接地址仅允许HTTP和HTTPS协议！', 'stop');
		
		return $url;
	}
 
}