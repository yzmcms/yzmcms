<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class update_urls extends common {

	/**
	 * 获取model和category基本信息
	 */
	public function init() {
		$modelid = 0;
		$modelinfo = get_modelinfo();
		$select = select_category('catids[]', '0', '≡ 所有栏目 ≡', 0, 'multiple="multiple" style="height:250px;width:140px;"', false, false);
		include $this->admin_tpl('update_urls_list');
	}

	
	/**
	 * 更改model
	 */
	public function change_model() {
		$modelid = isset($_POST['modelid']) ? intval($_POST['modelid']) : 0;
		$modelinfo = get_modelinfo();
		$select = select_category('catids[]', '0', '≡ 所有栏目 ≡', 0, 'multiple="multiple" style="height:250px;width:140px;"', false, false, $modelid);
		
		include $this->admin_tpl('update_urls_list');
	}
	
	
	/**
	 * 更新栏目URL
	 */
	public function update_category_url() {
 		if(is_post()) {
			$catids = isset($_POST['catids']) && is_array($_POST['catids']) ? $_POST['catids'] : array('0');
			
			$category = D('category');
			//根据系统URL规则生成栏目URL
			$url_mode = get_config('url_mode');
			
			//更新所有栏目
			if(!$catids[0]){
				 $catinfo = get_category(); 
			}else{
				$catids = join(',', array_map('intval', $catids));
				$catinfo = D('category')->field('catid,catname,`type`,category_urlrule,catdir')->where("catid IN ($catids)")->select();
			}
			
			foreach($catinfo as $val){
				if($val['type'] == 2) continue;  //如果是外部链接则跳过	
				$pclink = $url_mode ? get_config('site_url').$val['catdir'].'/' : SITE_PATH.$val['catdir'].'/';			
				$category->update(array('pclink' => $pclink), array('catid' => $val['catid']));
			}
			
			delcache('categoryinfo');
			showmsg('更新完成！', '', 2);
		}
	}
	
	
	/**
	 * 更新内容页URL
	 */
 	public function update_content_url() {

		$modelid = isset($_POST['modelid']) ? intval($_POST['modelid']) : (isset($_GET['modelid']) ? intval($_GET['modelid']) : 0);
		$autoid = isset($_GET['autoid']) ? intval($_GET['autoid']) : 0;
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$total = isset($_GET['total']) ? intval($_GET['total']) : 0;
		$pagesize = 50;

		$modelid_arr = getcache('update_content_url_'.$_SESSION['adminid']);
		if(!$modelid_arr){
			if($modelid){
				$modelid_arr[] = $modelid;
			}else{
				$modelinfos = get_modelinfo();
				foreach($modelinfos as $val) {
					$modelid_arr[] = $val['modelid'];
				}				
			}
			setcache('update_content_url_'.$_SESSION['adminid'], $modelid_arr);
		}
		if(!isset($modelid_arr[$autoid])) {
			delcache('update_content_url_'.$_SESSION['adminid']);
			showmsg('更新完成！', U('init'), 2);
		}
		$modelid = $modelid_arr[$autoid];
		$tablename = get_model($modelid);
		$db = D($tablename);
		$offset = $pagesize*($page-1);
		$order = 'id ASC';
		
		if(!$total)  $total = $db->total();
		$num = ceil($total/$pagesize);
		$limit = $offset.','.$pagesize;
		$data = $db->field('catid, id, flag')->order($order)->limit($limit)->select();
		foreach($data as $val) {
			if(strstr($val['flag'], '7')) continue;
			$url = get_content_url($val['catid'], $val['id']);	
			$db->update(array('url' => $url), array('id' => $val['id']));
		}
		$rate = $num ? floor(100 * ($page / $num)) : 100;
		$message = '【'.get_model($modelid, 'name').'】 正在更新，进度： '.$rate.'%';
		if($num > $page) {
			showmsg($message, U(ROUTE_A, array('autoid'=>$autoid, 'total'=>$total, 'page'=>++$page)), 0.1);
		} else {
			showmsg($message, U(ROUTE_A, array('autoid'=>++$autoid)), 0.1);
		}		

	}
	
}