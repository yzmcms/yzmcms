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

class update_urls extends common {

	/**
	 * 获取model和category基本信息
	 */
	public function init() {
		$modelid = 0;
		$modelinfo = get_site_modelinfo();
		$select = select_category('catids[]', '0', '≡ 所有栏目 ≡', 0, 'multiple="multiple" style="height:250px;width:140px;"', false, false);
		include $this->admin_tpl('update_urls_list');
	}

	
	/**
	 * 更改model
	 */
	public function change_model() {
		$modelid = isset($_POST['modelid']) ? intval($_POST['modelid']) : 0;
		$modelinfo = get_site_modelinfo();
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
			$url_mode = get_config('url_mode');
			$site_url = get_site_url();
			
			//更新所有栏目
			if(!$catids[0]){
				 $catinfo = get_category(); 
			}else{
				$catinfo = D('category')->field('catid,catname,arrparentid,`type`,catdir,domain')->wheres(array('catid'=>array('in', $catids, 'intval')))->select();
			}
			
			foreach($catinfo as $val){
				if($val['type'] == 2 || $val['domain']) continue;  
				if($url_mode==1 || $url_mode==3){
					if(strstr($val['arrparentid'], ',')){
						$arr = explode(',', $val['arrparentid']);
						$parents_domain = get_category($arr[1], 'domain');
						$pclink = $parents_domain ? $parents_domain.$val['catdir'].'/' : $site_url.$val['catdir'].'/';
					}else{
						$pclink = $site_url.$val['catdir'].'/';
					}
				}else{
					$pclink = SITE_PATH.$val['catdir'].'/';
				}		
				$category->update(array('pclink' => $pclink), array('catid' => $val['catid']));
			}
			
			delcache('categoryinfo');
			delcache('categoryinfo_siteid_'.self::$siteid);
			return_json(array('status'=>1, 'message'=>'更新栏目URL完成！'));
		}
	}
	
	
	/**
	 * 更新内容页URL
	 */
 	public function update_content_url() {

		$modelid = isset($_POST['modelid']) ? intval($_POST['modelid']) : 0;
		$autoid = isset($_POST['autoid']) ? intval($_POST['autoid']) : 0;
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$total = isset($_POST['total']) ? intval($_POST['total']) : 0;
		$pagesize = 50;

		$modelid_arr = getcache('update_content_url_'.$_SESSION['adminid']) ? getcache('update_content_url_'.$_SESSION['adminid']) : array();
		if(!$modelid_arr){
			if($modelid){
				$modelid_arr[] = $modelid;
			}else{
				$modelinfos = get_site_modelinfo();
				foreach($modelinfos as $val) {
					$modelid_arr[] = $val['modelid'];
				}				
			}
			setcache('update_content_url_'.$_SESSION['adminid'], $modelid_arr);
		}
		if(!isset($modelid_arr[$autoid])) {
			delcache('update_content_url_'.$_SESSION['adminid']);
			return_json(array('status'=>1, 'message'=>'更新内容URL完成！'));
		}
		$modelid = $modelid_arr[$autoid];
		$tablename = get_model($modelid);
		$db = D($tablename);
		$all_content = D('all_content');
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
			$all_content->update(array('url' => $url), array('modelid' => $modelid, 'id' => $val['id']));
		}
		$rate = $num ? round(100 * ($page / $num), 2) : 100;
		$message = '【'.get_model($modelid, 'name').'】 正在更新，进度： '.$rate.'%';
		if($num > $page) {
			return_json(array('status'=>2, 'message'=>$message, 'autoid'=>$autoid, 'total'=>$total, 'page'=>++$page));
		} else {
			return_json(array('status'=>2, 'message'=>$message, 'autoid'=>++$autoid, 'total'=>0, 'page'=>1));
		}		

	}
	
}