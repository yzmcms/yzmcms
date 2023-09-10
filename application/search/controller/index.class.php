<?php
/**
 * YzmCMS内容搜索模块 - 商业用途请购买官方授权
 * @license          http://www.yzmcms.com
 * @lastmodify       2020-07-28
 */

defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_sys_class('page','',0);

class index{

	private $siteid,$siteinfo,$module;

	public function __construct() {
		$this->siteid = get_siteid();
		$this->module = 'index';
		$this->siteinfo = array();
		$this->_set_theme();
	}

	/**
	 * 普通搜索
	 */
	public function init(){	
		$site = array_merge(get_config(), $this->siteinfo);
		
		if(!isset($_GET['q']) || !is_string($_GET['q'])) showmsg(L('illegal_parameters'), 'stop');
		$q = str_replace('%', '', new_html_special_chars(strip_tags(trim($_GET['q']))));
		if(strlen($q) < 2 || strlen($q) > 30){
			showmsg('你输入的字符长度需要是 2 到 30 个字符！', 'stop');
		}
		$siteid = $this->siteid;
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;

		list($seo_title, $keywords, $description) = get_site_seo($siteid, '‘'.$q.'’的搜索结果', $q);
		include template($this->module, 'search');	
	}
	
	
	/**
	 * TAG标签搜索
	 */
	public function tag(){	
		$site = array_merge(get_config(), $this->siteinfo);
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;	
		$data = D('tag')->field('siteid,tag,seo_title,seo_keywords,seo_description')->where(array('id'=>$id))->find();
		if(!$data || $data['siteid']!=$this->siteid) showmsg('TAG标签不存在！', 'stop');
		D('tag')->update('`click` = `click`+1', array('id' => $id));
		$q = $data['tag'];	
			
		$siteid = $this->siteid;
		$modelid = $catid = 0;
		list($seo_title, $keywords, $description) = get_site_seo($siteid, $q, $q);	
		$seo_title = $data['seo_title'] ? $data['seo_title'] : $seo_title;
		$keywords = $data['seo_keywords'] ? $data['seo_keywords'] : $keywords;
		$description = $data['seo_description'] ? $data['seo_description'] : $description;	
	
		include template($this->module, 'search');	
	}
	
	
	/**
	 * 文章归档搜索
	 */
	public function archives(){	
		$site = array_merge(get_config(), $this->siteinfo);
		
		$siteid = $this->siteid;
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$pubtime = isset($_GET['pubtime']) ? intval($_GET['pubtime']) : showmsg(L('lose_parameters'), 'stop');  
		if(!$pubtime) showmsg(L('illegal_parameters'), 'stop');  
		$date = date('Y-m', $pubtime);
		$starttime = strtotime($date); 
		$endtime = strtotime("$date +1 month");
		if(!$starttime || !$endtime) showmsg(L('illegal_operation'), 'stop');  
		
		$q = date('Y年m月', $starttime);
		list($seo_title, $keywords, $description) = get_site_seo($this->siteid, $q.'归档', $q.',文章归档');	
	
		include template($this->module, 'search');	
	}


	/**
	 * 设置站点模板
	 */
	private function _set_theme(){
		$ismobile = ismobile() || isset($_GET['is_wap']) ? true : false;
		if($this->siteid){
			$this->siteinfo = get_site($this->siteid);
			if($ismobile && $this->siteinfo['site_wap_theme']){
				$this->module = 'mobile';
				set_module_theme($this->siteinfo['site_wap_theme']);
			}else{
				set_module_theme($this->siteinfo['site_theme']);
			}
		}else{
			if($ismobile && get_config('site_wap_open')) {
				$this->module = 'mobile';
				set_module_theme(get_config('site_wap_theme'));
			}
		}
	}

}