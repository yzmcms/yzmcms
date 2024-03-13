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
yzm_base::load_sys_class('collection','',0);

class collection_content extends common {
	
	
	/**
	 * 采集节点列表
	 */	
	public function init(){	
		yzm_base::load_sys_class('page','',0);
		$collection_node = D('collection_node');
		$total = $collection_node->total();
		$page = new page($total, 15);
		$data = $collection_node->order('nodeid DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('collection_node_list');
	}
	

	/**
	 * 添加采集节点
	 */
 	public function add() {
 		if(isset($_POST['dosubmit'])) {
			if(!$_POST['urlpage']) showmsg('网址配置不能为空！');	
			$_POST['name']	= htmlspecialchars($_POST['name']);	
			$res = D('collection_node')->insert($_POST);
			if($res){
				showmsg(L('operation_success'), U('init'), 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}else{
			include $this->admin_tpl('collection_node_add');
		}

	}

	
	/**
	 * 编辑采集节点
	 */
 	public function edit() {
		$collection_node = D('collection_node');
		if(isset($_POST['dosubmit'])) {
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			
			$_POST['name']	= htmlspecialchars($_POST['name']);	
			if(D('collection_node')->update($_POST, array('nodeid' => $id))){
				showmsg(L('operation_success'), U('init'), 1);
			}else{
				showmsg(L('operation_failure'));
			}
			
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = $collection_node->where(array('nodeid' => $id))->find();
			include $this->admin_tpl('collection_node_edit');
		}

	}

	
	/**
	 * 删除多个采集节点
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			if(D('collection_node')->delete($_POST['id'], true)){
				showmsg(L('operation_success'), '', 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}
	
	
	/**
	 * 采集测试
	 */
	public function collection_test() {
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(!$id) showmsg(L('lose_parameters'));
		$data = D('collection_node')->where(array('nodeid' => $id))->find();
		if($data['urlpage'] == '') showmsg('网址配置不能为空！', 'stop');
		
		//目标网址
		if($data['sourcetype'] == 1){
			$url = str_replace('(*)', $data['pagesize_start'], $data['urlpage']);
		}else{
			$url = $data['urlpage'];
		}

		//定义采集列表区间
		$url_start = $data['url_start'];
		$url_end = $data['url_end'];

		if($url_start=='' || $url_end=='') showmsg('列表区域配置不能为空！', 'stop');
		
		$content = collection::get_content($url);
		$content = collection::get_sub_content($content, $url_start, $url_end);
		if($content){
			
			if($data['sourcecharset'] == 'gbk') $content = array_iconv($content);
			$content = collection::get_all_url($content, $data['url_contain'], $data['url_except']);

			//获取第一篇文章地址来测试
			$articleurl = isset($content['url'][0]) ? $content['url'][0] : '';
			if(!empty($articleurl)){

				$article = collection::get_content($articleurl);
				$article = collection::get_filter_html($article, $this->get_config($data));
				if($data['sourcecharset'] == 'gbk') $article = array_iconv($article);	
			}else{
				$article = '列表规则错误！';
			}	

		}else{
			$article = '列表规则错误！';
		}		
		
		include $this->admin_tpl('collection_test');
	}
	
	
	/**
	 * 采集网址
	 */
	public function collection_list_url() {
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(!$id) showmsg(L('lose_parameters'));
		$data = D('collection_node')->where(array('nodeid' => $id))->find();
		if(!$data || $data['urlpage'] == '') showmsg('网址配置不能为空！', 'stop');
		
		//目标网址
		if($data['sourcetype'] == 1){
			$url = array();
			for ($i = $data['pagesize_start']; $i <= $data['pagesize_end']; $i = $i + $data['par_num']) {
				$url[] = str_replace('(*)', $i, $data['urlpage']);
			}
		}else{
			$url[0] = $data['urlpage'];
		}



		//定义采集列表区间
		$url_start = $data['url_start'];
		$url_end = $data['url_end'];

		if($url_start=='' || $url_end=='') showmsg('列表区域配置不能为空！', 'stop');
		$i = $j = 0;
		
		$collection_content = D('collection_content');
		
		foreach($url as $v){
			$content = collection::get_content($v);
			$content = collection::get_sub_content($content, $url_start, $url_end);
			
			if(!$content) continue;
			
			if($data['sourcecharset'] == 'gbk') $content = array_iconv($content);
			$content = collection::get_all_url($content, $data['url_contain'], $data['url_except']);
			
			if(!empty($content['url'])) foreach($content['url'] as $k => $v){
				$r = $collection_content->field('url')->where(array('url' => $v))->find();
				if(!$r){
					$collection_content->insert(array('nodeid' => $data['nodeid'], 'status' => 0, 'url' => $v, 'title' => $content['title'][$k]));
					$j++;
				}else{
					$i++;
				} 
			}
			
		}

		showmsg('操作成功，共去除'.$i.'条重复数据，新增'.$j.'条数据！'); 

	}


	/**
	 * 采集内容
	 */
	public function collection_article_content() {
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(!$id) showmsg(L('lose_parameters'), 'stop');
		
		$collection_content = D('collection_content');

		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$total = isset($_GET['total']) ? intval($_GET['total']) : 0;

		if(!$total) $total = $collection_content->field('id, url')->where(array('nodeid' => $id, 'status' => 0))->total();
		$total_page = ceil($total/2);

		$list = $collection_content->field('id, url')->where(array('nodeid' => $id, 'status' => 0))->order('id DESC')->limit('2')->select();

		if(empty($list)) showmsg('没有找到网址列表，请先进行网址采集！', 'stop');

		$collection_node = D('collection_node');
		$data = $collection_node->field('sourcecharset,down_attachment,watermark,title_rule,title_html_rule,time_rule,time_html_rule,content_rule,content_html_rule')->where(array('nodeid' => $id))->find();


		$i = 0;
		foreach($list as $v){
			$article = collection::get_content($v['url']);
			if($data['sourcecharset'] == 'gbk') $article = array_iconv($article);	
			$article = collection::get_filter_html($article, $this->get_config($data));
			if($data['down_attachment']) $article['content'] = down_remote_img($article['content'], $this->get_baseurl($v['url']));
			$collection_content->update(array('status'=>1, 'data'=>array2string($article)), array('id'=>$v['id']));
			$i++;	
		}


		if($total_page > $page){
			showmsg('采集正在进行中，采集进度:'.($i+($page-1)*2).'/'.$total, U('collection_article_content', array('id'=>$id, 'page'=>$page+1, 'total'=>$total)), 0.1);
		}else{
			$collection_node->update(array('lastdate' => SYS_TIME), array('nodeid'=>$id));
			showmsg('采集完成！', U('collection_list'), 2);
		}

	}
	
	
	/**
	 * 内容导入
	 */
	public function collection_content_import() {
		if($_POST && is_array($_POST['id'])){
			$ids = join(',', $_POST['id']);
		}else{
			showmsg(L('lose_parameters'), 'stop');
		}
		
		include $this->admin_tpl('collection_content_import');
	}
	
	
	/**
	 * 新建内容发布方案
	 */
	public function create_programme() {
		if(!isset($_POST['dosubmit'])) showmsg(L('lose_parameters'), 'stop');
		
		$ids = safe_replace($_POST['ids']);
		
		$collection_content = D('collection_content');
		$order = $_POST['order'] =='1' ? 'id ASC' : 'id DESC';
		$res = $collection_content->field('id AS cid,status,data')->wheres(array('id'=>array('in', explode(',', $ids), 'intval')))->order($order)->select(); 
		
		$data = array();
		$data['nickname'] = safe_replace($_POST['nickname']);
		$data['copyfrom'] = safe_replace($_POST['copyfrom']);
		$data['click'] = intval($_POST['click']);
		$data['catid'] = intval($_POST['catid']);
		$data['listorder'] = 10;
		$data['issystem'] = 1;
		$data['userid'] = $_SESSION['adminid'];
		$data['username'] = $_SESSION['adminname'];
		
		$modelid = get_category($data['catid'], 'modelid');
		if(!$modelid)  showmsg(L('illegal_operation'), 'stop');
		$content_tabname = D(get_model($modelid));
			
		$i = 0;
		foreach($res as $v){
			if($v['status'] != 1) continue;
			
			$data = array_merge($data, string2array($v['data']));
			if(!$data['content']) continue;
			
			//自动提取缩略图
			if($_POST['auto_thumb']) {
				$img = match_img($data['content']);
				$data['thumb'] = $img ? thumb($img, get_config('thumb_width'), get_config('thumb_height')) : '';
			}

			//自动提取内容摘要
			if($_POST['auto_description']) {
				$data['description'] = str_cut(trim(strip_tags($data['content'])), 250);
			}
			
			$data['updatetime'] = $data['inputtime'];
			$id = $content_tabname->insert($data);
			$url = get_content_url($data['catid'], $id);

			$data['siteid'] = self::$siteid;
			$data['modelid'] = $modelid;
			$data['id'] = $id;
			$data['url'] = $url;
			D('all_content')->insert($data);
			$content_tabname->update(array('url' => $url), array('id'=>$id));
			$collection_content->update(array('status' => 2), array('id'=>$v['cid']));
			$i++;
		}
		
		showmsg('导入完成，共导入'.$i.'篇内容！', U('collection_list'));
	}
	
	
	/**
	 * 采集列表
	 */	
	public function collection_list(){	
		yzm_base::load_sys_class('page','',0);
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$nodeid = isset($_GET['nodeid']) ? intval($_GET['nodeid']) : 0;

		$where = '1 = 1';
		if(isset($_GET['dosubmit'])){
			
			$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
			$status = isset($_GET['status']) ? intval($_GET['status']) : 99;
			$keyword = isset($_GET['keyword']) ? safe_replace($_GET['keyword']) : '';
			
			if($type && $keyword){
				if($type == '1')
					$where .= ' AND nodeid = \''.$keyword.'\'';
				else
					$where .= ' AND title LIKE \'%'.$keyword.'%\'';
			}
			
			if($status != 99) {
				$where .= ' AND status = '.$status;
			}
			
		}		
		
		$collection_content = D('collection_content');
		$total = $collection_content->where($where)->total();
		$page = new page($total, 15);
		$data = $collection_content->where($where)->order('id DESC')->limit($page->limit())->select();		
		include $this->admin_tpl('collection_list');
	}
	
	
	/**
	 * 删除采集列表
	 */
	public function collection_list_del() {
		if($_POST && is_array($_POST['id'])){
			if(D('collection_content')->delete($_POST['id'], true)){
				showmsg(L('operation_success'), '', 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
	}
	
	
	/**
	 * 获取配置信息
	 */
	private function get_config($data) {
		return array(
		   'title_rule' => collection::myexp('[内容]', $data['title_rule']),
		   'title_html_rule' => collection::myexp('[|]', $data['title_html_rule']),
		   
		   'time_rule' => collection::myexp('[内容]', $data['time_rule']),
		   'time_html_rule' => collection::myexp('[|]', $data['time_html_rule']),
		   
		   'content_rule' => collection::myexp('[内容]', $data['content_rule']),
		   'content_html_rule' => collection::myexp('[|]', $data['content_html_rule']),
		);
	}
	
	
	/**
	 * 根据URL获取网站根网址
	 */
	private function get_baseurl($str){
		$arr = explode('://', $str);
		$arr2 = explode('/', $arr[1]);
		return $arr[0].'://'.$arr2[0].'/';
	}
	
}