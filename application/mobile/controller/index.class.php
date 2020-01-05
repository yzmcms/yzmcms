<?php
/**
 * YzmCMS 手机模块
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-10-31
 */
 
defined('IN_YZMPHP') or exit('Access Denied');

class index{
	
	
	public function __construct() {
		//设置手机模块模板风格
		set_module_theme('default');
	}
	
	
	/**
	 * 首页
	 */
	public function init() {
		$site = get_config();
		$seo_title = $site['site_name'];
		$keywords = $site['site_keyword'];
		$description = $site['site_description'];
		include template('mobile', 'index');
	}
	
	
	/**
	 * 栏目列表页
	 */
	public function lists() {
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		if(!$catid) showmsg(L('missing_parameter'),'stop');
		$catinfo = get_category($catid);
		if(!$catinfo) showmsg(L('category_not_existent'),'stop');
		extract($catinfo);
		
		//外部链接
		if($type == 2) showmsg(L('is_jump'), $pclink, 1);
		
		//SEO相关设置
		$site = get_config();
		$seo_title = $seo_title ? $seo_title : $catname.'_'.$site['site_name'];
		$keywords = $seo_keywords ? $seo_keywords : $site['site_keyword'];
		$description = $seo_description ? $seo_description : $site['site_description'];
		
		$title = $catname;
		//手机模板暂时就做这一个，不要问我为什么，因为没时间~~
		$template = 'category_article';
		
		//单页面
		if($type == 1){
			$r = D('page')->where(array('catid'=>$catid))->find();
			extract($r);
			$template = 'category_page';
		}
		
		include template('mobile', $template);
	}
	
	
	/**
	 * 内容页
	 */
	public function show() {
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(!$catid || !$id) showmsg(L('missing_parameter'),'stop');
		
		$category = get_category($catid);
		if(!$category) showmsg(L('category_not_existent'),'stop');
		$modelid = $category['modelid'];
		
		$tablename = get_model($modelid);
		if(!$tablename)  showmsg(L('model_not_existent'),'stop');
		$db = D($tablename);
		$data = $db->where(array('id'=>$id))->find();
		if(!$data || $data['status'] != 1) showmsg(L('content_not_existent'),'stop');
		extract($data);
		
		//会员组权限和阅读收费检测，手机端直接提示用PC打开浏览
		if($groupids_view || $readpoint) {
			showmsg(L('insufficient_authority_pc'), 'stop');
		}
		
		//SEO相关设置
		$site = get_config();
		
		//更新点击量
		$db->update('`click` = `click`+1', array('id' => $id));

		//内容分页
		if(strpos($content, '_yzmcms_content_page_') !== false){
			$content = $this->_content_page($content);
		}	
		
		//获取相同分类的上一篇/下一篇内容	
		$pre = $db->field('id,catid,title')->where(array('id<'=>$id , 'status'=>'1' , 'catid'=>$catid))->order('id DESC')->find();
		$next = $db->field('id,catid,title')->where(array('id>'=>$id , 'status'=>'1', 'catid'=>$catid))->order('id ASC')->find();
		$pre = $pre ? '<a href="'.U('mobile/index/show', array('catid'=>$pre['catid'],'id'=>$pre['id'])).'">'.$pre['title'].'</a>' : L('already_is_first');
		$next = $next ? '<a href="'.U('mobile/index/show', array('catid'=>$next['catid'],'id'=>$next['id'])).'">'.$next['title'].'</a>' : L('already_is_last');
		
		include template('mobile', 'show_article');
	}
	
	
	/**
	 * 手机留言板
	 */
	public function guestbook() {
		
		$title = '留言反馈';
		//SEO相关设置
		$site = get_config();
		$seo_title = '留言反馈_'.$site['site_name'];
		$keywords = $site['site_keyword'];
		$description = $site['site_description'];
		include template('mobile', 'guestbook');
	}


	/**
	 * 内容分页
	 */
	private function _content_page($content) {
		$arr = explode('_yzmcms_content_page_', $content);
		$page = isset($_GET['page']) ? max(intval($_GET['page']), 1) : 1;
		$total_page = count($arr);
		$off = $page-1<$total_page ? $page-1 : $total_page-1;

		$pages = '<div id="page">';
		yzm_base::load_sys_class('page','',0);
		$page = new page($total_page, 1);
		$pages .= $page->getpre().$page->getlist().$page->getnext();
		$pages .= '</div>';

		return $arr[$off].$pages;
	}

}