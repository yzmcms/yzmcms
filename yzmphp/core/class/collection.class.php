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
    public static function get_content($url) {
		
		self::$url = $url;
		
        $content = '';
        if (extension_loaded('curl')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            $content = @file_get_contents($url);
        }
		
		// 忽略SSRF攻击，采集时需支持302，详情联系QQ: 214243830
        return trim($content);
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