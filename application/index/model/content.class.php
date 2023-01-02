<?php
/**
 * 内容模型处理   
 * 
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2020-03-01
 */

class content {
	
	/**
	 * 阅读收费检测
	 */
	public static function check_readpoint($data) {
		$userid = intval(get_cookie('_userid'));
		if(!$userid) return false;

		//检查是否是作者自己
		if(!$data['issystem'] && $data['userid']==$userid) return true;

		//检查一个月内是否支付过
		$data = D('pay_spend')->field('creat_time')->where(array('userid'=>$userid,'remarks'=>$data['catid'].'_'.$data['id']))->order('id DESC')->find();
		if($data && $data['creat_time']+2592000 > SYS_TIME) {
			return true;
		}
		
		$data = D('member')->field('vip,overduedate')->where(array('userid'=>$userid))->find();
		
		//检查是否为vip会员
		if($data['vip']){
			if($data['overduedate'] > SYS_TIME)	return true; 
			D('member')->update(array('vip'=>0), array('userid'=>$userid));
		}
		
		return false;
	}


	/**
	 * 内容分页处理
	 */
	public static function content_page($content, $page, &$page_section) {
		$arr = explode('_yzm_content_page_', $content);
		$page = max($page, 1);
		$total_page = count($arr);
		$off = $page-1<$total_page ? $page-1 : $total_page-1;
		$page_section = '_'.L('section').($off+1).L('page');

		$pages = '<div id="page">';
		if(URL_MODEL == 3){
			$pages .= $page<=1 ? '<a href="?page=1" class="nopage">'.L('pre_page').'</a>' : '<a href="?page='.($page-1).'" class="prepage">'.L('pre_page').'</a>';
			for($i=1; $i<=$total_page; $i++){  
				$class = $i==$page ? ' curpage' : '';
				$pages.='<a href="?page='.$i.'" class="listpage'.$class.'">'.$i.'</a>'; 
			}
			$pages .= $page>=$total_page ? '<a href="?page='.$page.'" class="nopage">'.L('next_page').'</a>' : '<a href="?page='.($page+1).'" class="prepage">'.L('next_page').'</a>';						
		}else{
			yzm_base::load_sys_class('page','',0);
			$page = new page($total_page, 1);
			$pages .= $page->getpre().$page->getlist().$page->getnext();		
		}
		$pages .= '</div>';

		return $arr[$off].$pages;
	}


	/**
	 * 内容关键字处理
	 */
	public static function keyword_content($content) {
		$keyword_replacenum = get_config('keyword_replacenum');
		$search = "/(alt\s*=\s*|title\s*=\s*|src\s*=\s*)[\"|\'](.+?)[\"|\']/is";
		$content = preg_replace_callback($search, array('content', '_base64_encode'), $content);
		$linkdatas = get_content_keyword();
		if($linkdatas) {
			$word = $replacement = array();
			foreach($linkdatas as $v) {
				$word1[] = '/(?!(<a.*?))' . preg_quote($v['keyword'], '/') . '(?!.*<\/a>)/s';
				$word2[] = $v['keyword'];		
			
				$replacement[] = '<a href="'.$v['url'].'" target="_blank" class="yzm-keyword-link">'.$v['keyword'].'</a>';
			}
			if($keyword_replacenum) {
				$content = preg_replace($word1, $replacement, $content, $keyword_replacenum);
			} else {
				$content = str_replace($word2, $replacement, $content);
			}
		}
		$content = preg_replace_callback($search, array('content', '_base64_decode'), $content);
		return $content;
	}


	public static function _base64_encode($matches) {
		return $matches[1]."\"".base64_encode($matches[2])."\"";
	}
	

	public static function _base64_decode($matches) {
		return $matches[1]."\"".base64_decode($matches[2])."\"";
	}
}
