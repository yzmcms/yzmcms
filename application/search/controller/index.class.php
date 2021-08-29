<?php
/**
 * YzmCMS内容搜索模块 - 商业用途请购买官方授权
 * @license          http://www.yzmcms.com
 * @lastmodify       2020-07-28
 */

defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_sys_class('page','',0);

class index{

	private $offset,$module;

	public function __construct() {
		$this->offset = get_config('search_page') ? intval(get_config('search_page')) : 10;
		$this->module = 'index';
		if(isset($_GET['is_wap'])){
			set_module_theme(get_config('site_wap_theme'));
			$this->module = 'mobile';
		}
	}

	/**
	 * 普通搜索
	 */
	public function init(){	
		$site = get_config();
		
		if(!is_string($_GET['q'])) showmsg(L('illegal_parameters'), 'stop');
		$q = str_replace('%', '', new_html_special_chars(strip_tags(trim($_GET['q']))));
		if(strlen($q) < 2 || strlen($q) > 30){
			showmsg('你输入的字符长度需要是 2 到 30 个字符！', 'stop');
		}
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
		$where = "`status` = 1 AND `title` LIKE '%$q%'";

		if($modelid){
			$dbname = get_model($modelid);
			if(!$dbname) showmsg('模型不存在！', 'stop');
			$db = D($dbname);
			$total = $db->where($where)->total();
			$page = new page($total, $this->offset);
			$search_data = $db->field('id,title,description,inputtime,updatetime,click,thumb,nickname,url,catid,flag,color')->where($where)->order('id DESC')->limit($page->limit())->select();
		}else{
			$db = D('all_content');
			$total = $db->where($where)->total();
			$page = new page($total, $this->offset);
			$data = $db->field('modelid,id')->where($where)->order('allid DESC')->limit($page->limit())->select();
			$search_data = array();
			foreach ($data as $value) {
				$res = D(get_model($value['modelid']))->field('id,title,description,inputtime,updatetime,click,thumb,nickname,url,catid,flag,color')->where(array('id'=>$value['id']))->find();
				if(!$res) continue;
				$search_data[] = $res;
			}

		}

		$seo_title = '‘'.$q.'’的搜索结果_'.$site['site_name'];
		$keywords = $q.','.$site['site_keyword'];
		$description = $site['site_description'];
		
		$pages = '<span class="pageinfo">共<strong>'.$page->total().'</strong>页<strong>'.$total.'</strong>条记录</span>'.$page->getfull(false);
		include template($this->module, 'search');	
	}
	
	
	/**
	 * TAG标签搜索
	 */
	public function tag(){	
		$site = get_config();
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;	
		$data = D('tag')->field('tag,remarks')->where(array('id'=>$id))->find();
		if(!$data) showmsg('TAG标签不存在！', 'stop');
		D('tag')->update('`click` = `click`+1', array('id' => $id));
		$q = $data['tag'];	
			
		$tag_content = D('tag_content');
		$seo_title = $q.'_'.$site['site_name'];
		$keywords = $q.','.$site['site_keyword'];
		$description = $data['remarks'] ? $data['remarks'] : $site['site_description'];		
	
		$total = $tag_content->where(array('tagid'=>$id))->total();
		$page = new page($total, $this->offset);
		$data = $tag_content->field('modelid,aid')->where(array('tagid'=>$id))->order('modelid ASC,aid DESC')->limit($page->limit())->select();
		$search_data = array();
		foreach ($data as $value) {
			$res = D(get_model($value['modelid']))->field('id,title,description,inputtime,updatetime,click,thumb,nickname,url,catid,flag,color')->where(array('id'=>$value['aid'],'status'=>1))->find();
			if(!$res) continue;
			$search_data[] = $res;
		}
		
		$pages = '<span class="pageinfo">共<strong>'.$page->total().'</strong>页<strong>'.$total.'</strong>条记录</span>'.$page->getfull(false);
		include template($this->module, 'search');	
	}
	
	
	/**
	 * 文章归档搜索
	 */
	public function archives(){	
		$site = get_config();
	
		$pubtime = isset($_GET['pubtime']) ? intval($_GET['pubtime']) : showmsg(L('lose_parameters'), 'stop');  
		$date = date('Y-m', $pubtime);
		$starttime = strtotime($date); 
		$endtime = strtotime("$date +1 month");
		if(!$starttime || !$endtime) showmsg(L('illegal_operation'), 'stop');  
		
		$q = date('Y年m月', $starttime);
		$seo_title = $q.'归档_'.$site['site_name'];
		$keywords = $q.',文章归档,'.$site['site_keyword'];
		$description = $site['site_description'];
		
		//文章归档暂时只调用文章模型的
		$db = D('article');
		$where = '`status` = 1 AND inputtime BETWEEN '.$starttime.' AND '.$endtime;
		$total = $db->where($where)->total();
		$page = new page($total, $this->offset);
		$search_data = $db->field('id,title,description,inputtime,updatetime,click,thumb,nickname,url,catid,flag,color')->where($where)->order('id DESC')->limit($page->limit())->select();
		
		$pages = '<span class="pageinfo">共<strong>'.$page->total().'</strong>页<strong>'.$total.'</strong>条记录</span>'.$page->getfull(false);
		include template($this->module, 'search');	
	}

}