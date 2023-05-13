<?php
/**
 * yzm_tpl.class.php  yzm_tpl模板解析类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-03-13 
 */

class yzm_tpl {
	
    private $template_tag_left = '{';     //模板左标签
    private $template_tag_right = '}';    //模板右标签
	
	
    /**
     *  构造方法
     *  @return 
     */	
    public function __construct() {
		
    }	
	

	/**
	 * 解析模板
	 *
	 * @param $str	模板内容
	 * @return string
	 */
	public function tpl_replace($str) {
		$str = preg_replace("/".$this->template_tag_left."m:include\s+(.+)".$this->template_tag_right."/", "<?php include template(\\1); ?>", $str );
		$str = preg_replace("/".$this->template_tag_left."php\s+(.+)\s*".$this->template_tag_right."/", "<?php \\1?>", $str );
		$str = preg_replace("/".$this->template_tag_left."if\s+(.+?)".$this->template_tag_right."/", "<?php if(\\1) { ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."else".$this->template_tag_right."/", "<?php } else { ?>", $str );
		$str = preg_replace("/".$this->template_tag_left."elseif\s+(.+?)".$this->template_tag_right."/", "<?php } elseif (\\1) { ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."\/if".$this->template_tag_right."/", "<?php } ?>", $str);

		$str = preg_replace("/".$this->template_tag_left."for\s+(.+?)".$this->template_tag_right."/", "<?php for(\\1) { ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."\/for".$this->template_tag_right."/", "<?php } ?>",$str);

		$str = preg_replace("/".$this->template_tag_left."\+\+(.+?)".$this->template_tag_right."/", "<?php ++\\1; ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."\-\-(.+?)".$this->template_tag_right."/", "<?php --\\1; ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."(.+?)\+\+".$this->template_tag_right."/", "<?php \\1++; ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."(.+?)\-\-".$this->template_tag_right."/", "<?php \\1--; ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."loop\s+(\S+)\s+(\S+)".$this->template_tag_right."/", "<?php if(is_array(\\1)) foreach(\\1 as \\2) { ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."loop\s+(\S+)\s+(\S+)\s+(\S+)".$this->template_tag_right."/", "<?php if(is_array(\\1)) foreach(\\1 as \\2 => \\3) { ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."\/loop".$this->template_tag_right."/", "<?php } ?>", $str);
		$str = preg_replace("/".$this->template_tag_left."([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))".$this->template_tag_right."/", "<?php echo \\1;?>", $str); 
		$str = preg_replace("/".$this->template_tag_left."\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))".$this->template_tag_right."/", "<?php echo \\1;?>", $str);
		$str = preg_replace("/".$this->template_tag_left."(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)".$this->template_tag_right."/", "<?php echo \\1;?>", $str); 
		$str = preg_replace("/".$this->template_tag_left."([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)".$this->template_tag_right."/s", "<?php echo \\1;?>", $str); 
		$str = preg_replace("/".$this->template_tag_left."(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)->(\w+)(.*?)".$this->template_tag_right."/", "<?php echo \\1->\\2\\3;?>", $str);
		$str = preg_replace_callback("/".$this->template_tag_left."(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)".$this->template_tag_right."/s",  array($this, 'addquote'), $str); 
		$str = preg_replace_callback("/".$this->template_tag_left."m:(\w+)\s+([^}]+)".$this->template_tag_right."/i", array($this, 'yzm_tag_callback'), $str);

		$str = "<?php defined('IN_YZMPHP') or exit('No permission resources.'); ?>" . $str;
		return $str;
	}	

	
	public static function yzm_tag_callback($matches) {
		return self::yzm_tag($matches[1],$matches[2], $matches[0]);;
	}
	
	
	/**
	 * 解析标签
	 */	
	public static function yzm_tag($action, $data, $html) {
		preg_match_all("/([a-z]+)\=[\"]?([^\"]+)[\"]?/i", stripslashes($data), $matches, PREG_SET_ORDER);
		$datas = array();
		foreach ($matches as $v) {
			$datas[$v[1]] = $v[2];
		}
		$cache = isset($datas['cache']) ? intval($datas['cache']) : 0;
		$return = isset($datas['return']) && trim($datas['return']) ? trim($datas['return']) : 'data';
		$str = '';
		if ($cache && !isset($datas['page'])) {
			$str = '$tag_cache_name = \'tag_cache_\'.md5(implode(\'&\','.self::arr_to_html($datas).'));if(!$'.$return.' = getcache($tag_cache_name)){';
		}
		$str .= '$tag = yzm_base::load_sys_class(\'yzm_tag\');';
		$str .= 'if(method_exists($tag, \''.$action.'\')) {';
		$str .= '$'.$return.' = $tag->'.$action.'('.self::arr_to_html($datas).');';
		if(isset($datas['page'])) $str .= '$pages = $tag->pages();';		
		$str .= '}';
		if ($cache && !isset($datas['page'])) {
			$str .= 'if(!empty($'.$return.')){setcache($tag_cache_name, $'.$return.', '.$cache.');}';
			$str .= '}';
		}
		return '<?php '.$str.'?>';
	}
	
	
	/**
	 * 转义 // 为 /
	 *
	 * @param $var	转义的字符
	 * @return 转义后的字符
	 */
	public function addquote($matches) {
		$var = '<?php echo '.$matches[1].';?>';
		return str_replace ( "\\\"", "\"", preg_replace ( "/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var ) );
	}	

	
	/**
	 * 转换数据为HTML代码
	 * @param array $data 数组
	 */
	private static function arr_to_html($data) {
		if (is_array($data)) {
			$str = 'array(';
			foreach ($data as $key=>$val) {
				if (is_array($val)) {
					$str .= "'$key'=>".self::arr_to_html($val).",";
				} else {
					if (in_array($key, array('where', 'sql'))) {
						$str .= "'$key'=>\"".$val."\",";
					}else{
						if (strpos($val, '$')===0) {
							$str .= "'$key'=>$val,";
						} else {
							$str .= "'$key'=>'".new_addslashes($val)."',";
						}						
					}
				}
			}
			return $str.')';
		}
		return false;
	}
}
