<?php
defined('IN_YZMPHP') or exit('Access Denied');
yzm_base::load_model('content', 'index', 0);

class index{
	
	/**
	 * 首页
	 */
	public function init() {
		$site = get_config();
		$seo_title = $site['site_name'];
		$keywords = $site['site_keyword'];
		$description = $site['site_description'];
		include template('index', 'index');
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
		
		$template = $catid==$arrchildid ? $list_template : $category_template;
		
		//单页面
		if($type == 1){
			$r = D('page')->where(array('catid'=>$catid))->find();
			extract($r);
			$template = $category_template;
		}
		
		//如果没有设置search,则为静态分页URL规则
		if(!isset($_GET['s'])) define('LIST_URL', true);			
				
		include template('index', $template);
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
		$template = $category['show_template'];
		
		$tablename = get_model($modelid);
		if(!$tablename)  showmsg(L('model_not_existent'),'stop');
		$db = D($tablename);
		$data = $db->where(array('id'=>$id))->find();
		if(!$data || $data['status'] != 1 || $data['catid'] != $catid) showmsg(L('content_not_existent'),'stop');
		extract($data);
		
		//会员组权限检测
		if($groupids_view) {
			$groupid = intval(get_cookie('_groupid'));
			if(!$groupid){
				showmsg(L('need_login'), url_referer(get_url()), 2);
			}
			if($groupid < $groupids_view) showmsg(L('insufficient_authority'), 'stop');
		}
		
		//阅读收费检测
		if($readpoint) content::check_readpoint($catid.'_'.$id, $readpoint, $paytype, $url);
		
		//SEO相关设置
		$site = get_config();
		
		//更新点击量
		$db->update('`click` = `click`+1', array('id' => $id));

		//内容分页
		if(strpos($content, '_yzm_content_page_') !== false){
			$content = content::content_page($content);
		}	
		
		//内容关键字
		if(get_config('keyword_link')){
			$content = content::keyword_content($content);
		}		
		
		//获取相同分类的上一篇/下一篇内容	
		$pre = $db->field('title,url')->where(array('id<'=>$id , 'status'=>'1' , 'catid'=>$catid))->order('id DESC')->find();
		$next = $db->field('title,url')->where(array('id>'=>$id , 'status'=>'1', 'catid'=>$catid))->order('id ASC')->find();
		$pre = $pre ? '<a href="'.$pre['url'].'">'.$pre['title'].'</a>' : L('already_is_first');
		$next = $next ? '<a href="'.$next['url'].'">'.$next['title'].'</a>' : L('already_is_last');
		
		include template('index', $template);
	}

}