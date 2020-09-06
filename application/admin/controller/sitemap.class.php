<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class sitemap extends common {
	
	function __construct() {
		parent::__construct();
	    $this->data = array();
	    $this->filename = 'sitemap.xml';
		$this->header = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	    $this->footer = '</urlset>';
	}	

	/**
	 * init
	 */
	public function init() {
		$is_make_xml =  $is_make_txt = false;
		if(is_file(YZMPHP_PATH.'sitemap.xml')){
			$is_make_xml = true;
			$make_xml_time = date('Y-m-d H:i:s', filemtime(YZMPHP_PATH.'sitemap.xml'));
		}
		if(is_file(YZMPHP_PATH.'sitemap.txt')){
			$is_make_txt = true;
			$make_txt_time = date('Y-m-d H:i:s', filemtime(YZMPHP_PATH.'sitemap.txt'));
		}
		$modelinfo = get_modelinfo();
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
			$pagesize = isset($_POST['pagesize']) ? intval($_POST['pagesize']) : 500;

			if($type) $this->filename = 'sitemap.txt';

			if($page == 1){

				// 第一次写入时
				@file_put_contents($this->filename, $type ? '' : $this->header);

				// 生成网站地址链接
				$item = $this->_sitemap_item(SITE_URL, SYS_TIME, 'daily', '1.0');
				$this->_add_data($item);
				
				// 生成栏目链接
				$data = D('category')->field('pclink')->order('listorder ASC,catid ASC')->select();
				foreach($data as $val){
					if(!strpos($val['pclink'], '://')) $val['pclink'] = SITE_URL.ltrim($val['pclink'], '/');
					$item = $this->_sitemap_item($val['pclink'], SYS_TIME, $changefreq, $priority);
					$this->_add_data($item);
				}
			}
			
			
			// 生成内容链接
			$table = $model ? get_model($model) : 'all_content';
			$order = $model ? 'id DESC' : 'allid DESC';
			if(!$table) return_json(array('status'=>0,'message'=>L('illegal_parameters')));

			if(!$total)  $total = D($table)->where(array('status'=>'1'))->total();
			$num = ceil($total/$pagesize);

			$page = max($page, 1);
			$offset = $pagesize*($page-1);
			$limit = $offset.','.$pagesize;
			$data = D($table)->field('updatetime,url')->where(array('status'=>'1'))->order($order)->limit($limit)->select();

			foreach($data as $val){
				if(!strpos($val['url'], '://')) $val['url'] = SITE_URL.ltrim($val['url'], '/');
				$item = $this->_sitemap_item($val['url'], $val['updatetime'], $changefreq, $priority);
				// 在兼容模式下，需要加上 htmlspecialchars 函数进行URL转义
				// $item = $this->_sitemap_item(htmlspecialchars($val['url']), $val['updatetime'], $changefreq, $priority);
				$this->_add_data($item);
			}
			
			$str = $type ? $this->_txt_format() : $this->_xml_format();
			$strlen = @file_put_contents(YZMPHP_PATH.$this->filename, $str, FILE_APPEND | LOCK_EX);
			if(!$strlen) return_json(array('status'=>0,'message'=>'生成文件 '.$this->filename.' 失败，请检查是否有写入权限！'));

			if($num<=$page || ($limit_total && ($page*$pagesize>=$limit_total))){
				if(!$type) @file_put_contents($this->filename, $this->footer, FILE_APPEND | LOCK_EX);
				return_json(array('status'=>1,'message'=>'生成文件 '.$this->filename.' 成功！'));
			}

			return_json(array('status'=>2,'message'=>'生成文件 '.$this->filename.' 成功，等待继续写入！','total'=>$total,'page'=>++$page));
		}
	}


	/**
	 * 删除地图
	 */	
	public function delete(){
		$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
		if($type) $this->filename = 'sitemap.txt';
		if(is_file($this->filename)){
			if(!@unlink($this->filename)) showmsg(L('operation_failure'));
		}
		showmsg(L('operation_success'), U('init'), 1);
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