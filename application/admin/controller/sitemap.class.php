<?php
// +----------------------------------------------------------------------
// | Site:  [ http://www.yzmcms.com]
// +----------------------------------------------------------------------
// | Copyright: 袁志蒙工作室，并保留所有权利
// +----------------------------------------------------------------------
// | Author: YuanZhiMeng <214243830@qq.com>
// +---------------------------------------------------------------------- 
// | Explain: 这不是一个自由软件,您只能在不用于商业目的的前提下对程序代码进行修改和使用，不允许对程序代码以任何形式任何目的的再发布！
// +----------------------------------------------------------------------

defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class sitemap extends common {

	public $root_dir;
	public $data;
	public $filename;
	public $header;
	public $footer;
	
	public function __construct() {
		parent::__construct();
		$this->root_dir = $this->_get_dir();
	    $this->data = array();
	    $this->filename = 'sitemap.xml';
		$this->header = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	    $this->footer = '</urlset>';
	}	

	/**
	 * init
	 */
	public function init() {
		$is_make_xml = $is_make_txt = false;
		if(is_file($this->root_dir.'sitemap.xml')){
			$is_make_xml = true;
			$make_xml_time = date('Y-m-d H:i:s', filemtime($this->root_dir.'sitemap.xml'));
		}
		if(is_file($this->root_dir.'sitemap.txt')){
			$is_make_txt = true;
			$make_txt_time = date('Y-m-d H:i:s', filemtime($this->root_dir.'sitemap.txt'));
		}
		$site_url = get_site_url();
		$modelinfo = get_site_modelinfo();
		$sitemap = get_cookie('sitemap');
		$changefreq = $sitemap ? $sitemap['changefreq'] : 'weekly';
		$priority = $sitemap ? $sitemap['priority'] : '0.8';
		include $this->admin_tpl('sitemap');
	}


	/**
	 * 生成地图
	 */
	public function make_sitemap() {
		if(is_post()){
			$model = isset($_POST['model']) ? intval($_POST['model']) : 0;
			$limit_total = isset($_POST['limit_total']) ? intval($_POST['limit_total']) : 0;
			$changefreq = isset($_POST['changefreq']) ? safe_replace($_POST['changefreq']) : 'weekly';
			$priority = isset($_POST['priority']) ? $_POST['priority'] : 0.8;
			$type = isset($_POST['type']) ? intval($_POST['type']) : 0;
			$total = isset($_POST['total']) ? intval($_POST['total']) : 0;
			$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
			$pagesize = isset($_POST['pagesize']) ? intval($_POST['pagesize']) : 300;

			set_cookie('sitemap', array('changefreq'=>$changefreq, 'priority'=>$priority), 2592000);

			if($type) $this->filename = 'sitemap.txt';
			$site_url = get_site_url();

			if($page == 1){

				// 第一次写入时
				@file_put_contents($this->root_dir.$this->filename, $type ? '' : $this->header);

				// 生成首页链接
				$item = $this->_sitemap_item($site_url, SYS_TIME, 'daily', '1.0');
				$this->_add_data($item);
				
				// 生成栏目链接
				$data = D('category')->field('pclink')->where(array('siteid'=>self::$siteid))->order('listorder ASC,catid ASC')->select();
				foreach($data as $val){
					if(!strpos($val['pclink'], '://')) $val['pclink'] = $site_url.ltrim($val['pclink'], '/');
					$item = $this->_sitemap_item($val['pclink'], SYS_TIME, $changefreq, $priority);
					$this->_add_data($item);
				}

				// 生成TAG链接
				$data = D('tag')->field('id')->where(array('siteid'=>self::$siteid))->order('id DESC')->limit(300)->select();
				foreach($data as $val){
					$item = $this->_sitemap_item(tag_url($val['id'], $site_url), SYS_TIME, $changefreq, $priority);
					$this->_add_data($item);
				}
			}
			
			
			// 生成内容链接
			$table = $model ? get_model($model) : 'all_content';
			$order = $model ? 'id DESC' : 'allid DESC';
			$where = $model ? array('status'=>1) : array('siteid'=>self::$siteid,'status'=>1);
			if(!$table) return_json(array('status'=>0,'message'=>L('illegal_parameters')));

			if(!$total) {
				$total = D($table)->where($where)->total();
				$total = $limit_total ? min($limit_total,$total) : $total;
			}
			$num = ceil($total/$pagesize);

			$page = max($page, 1);
			$offset = $pagesize*($page-1);
			$limit = $offset.','.$pagesize;
			$data = D($table)->field('updatetime,url')->where($where)->order($order)->limit($limit)->select();

			foreach($data as $val){
				if(!strpos($val['url'], '://')) $val['url'] = $site_url.ltrim($val['url'], '/');
				$item = $this->_sitemap_item($val['url'], $val['updatetime'], $changefreq, $priority);
				// 在兼容模式下，需要加上 htmlspecialchars 函数进行URL转义
				// $item = $this->_sitemap_item(htmlspecialchars($val['url']), $val['updatetime'], $changefreq, $priority);
				$this->_add_data($item);
			}
			
			$str = $type ? $this->_txt_format() : $this->_xml_format();
			$strlen = @file_put_contents($this->root_dir.$this->filename, $str, FILE_APPEND | LOCK_EX);
			if(!$strlen) return_json(array('status'=>0,'message'=>'生成文件 '.$this->filename.' 失败，请检查是否有写入权限！'));

			if($num <= $page){
				if(!$type) @file_put_contents($this->root_dir.$this->filename, $this->footer, FILE_APPEND | LOCK_EX);
				return_json(array('status'=>1,'message'=>'生成文件 '.$this->filename.' 成功！'));
			}

			$rate = round(100 * ($page*$pagesize / $total), 2);
			return_json(array('status'=>2,'message'=>'正在生成中，进度'.$rate.'%……','total'=>$total,'page'=>++$page));
		}
	}


	/**
	 * 删除地图
	 */	
	public function delete(){
		$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
		if($type) $this->filename = 'sitemap.txt';
		if(is_file($this->root_dir.$this->filename)){
			if(!@unlink($this->root_dir.$this->filename)) return_json(array('status'=>0, 'message'=>L('operation_failure')));
		}
		return_json(array('status'=>1, 'message'=>L('operation_success')));
	}
	

	/**
	 * 生成xml格式
	 */	
	private function _xml_format(){
		$str = '';
		foreach($this->data as $val){				
				$str .= "<url>\n\t<loc>".$val['loc']."</loc>\n";
				$str .= "\t<lastmod>".date('Y-m-d', $val['lastmod'])."</lastmod>\n";
				$str .= "\t<changefreq>".$val['changefreq']."</changefreq>\n";
				$str .= "\t<priority>".$val['priority']."</priority>\n";
				$str .= "</url>\n";				
		}
		return $str;
	}
	

	/**
	 * 生成txt格式
	 */	
	private function _txt_format(){
		$str = '';
		foreach($this->data as $val){				
				$str .= $val['loc']."\n";			
		}
		return $str;
	}


	/**
	 * 获取当前地图根目录
	 */	
	private function _get_dir() {
		$root_dir = YZMPHP_PATH;
        if(self::$siteid){
        	$root_dir = YZMPHP_PATH.'sites'.DIRECTORY_SEPARATOR.get_site(self::$siteid, 'dirname').DIRECTORY_SEPARATOR;
        }
        return $root_dir;
    }	
	

	/**
	 * 添加数据
	 */	
	private function _add_data($new_item) {
        $this->data[] = $new_item;
    }
	
	
	/**
	 * 创建地图格式
	 */	
	private function _sitemap_item($loc, $lastmod = '', $changefreq = '', $priority = '') {
		$data = array();
		$data['loc'] =  $loc;
		$data['lastmod'] =  $lastmod;
		$data['changefreq'] =  $changefreq;
		$data['priority'] =  $priority;
		return $data;
    } 
	
}