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
		$select = select_category('catids[]', '0', '≡ 所有栏目 ≡', 0, 'multiple="multiple" style="height:200px;width:140px;"', false, false);
		include $this->admin_tpl('update_urls_list');
	}

	
	/**
	 * 更改model
	 */
	public function change_model() {
		$modelid = isset($_POST['modelid']) ? intval($_POST['modelid']) : 0;
		$modelinfo = get_modelinfo();
		$select = select_category('catids[]', '0', '≡ 所有栏目 ≡', 0, 'multiple="multiple" style="height:200px;width:140px;"', false, false, $modelid);
		
		include $this->admin_tpl('update_urls_list');
	}
	
	
	/**
	 * 更新栏目URL
	 */
	public function update_category_url() {
 		if(isset($_POST['dosubmit'])) {
			$catids = isset($_POST['catids']) && is_array($_POST['catids']) ? $_POST['catids'] : array('0');
			
			$category = D('category');
			//根据系统URL规则生成栏目URL
			$url_rule = get_config('url_rule');
			
			//更新所有栏目
			if(!$catids[0]){
				 $catinfo = get_category(); 
			}else{
				$catids = join(',', array_map('intval', $catids));
				$catinfo = D('category')->field('catid,catname,type,category_urlrule,catdir')->where("catid IN ($catids)")->select();
			}
			
			foreach($catinfo as $val){
				if($val['type'] == 2) continue;  //如果是外部链接则跳过	

				//如果系统设置是伪静态模式
				if($url_rule){
					$pclink = URL_MODEL == 1 ? SITE_URL.'index.php?s=/'.$val['catdir'].'/' : SITE_URL.$val['catdir'].'/';
				}else{  
					$pclink = U('index/index/lists','catid='.$val['catid']);
				}				
				
				$category->update(array('pclink' => $pclink), array('catid' => $val['catid']));
			}
			
			delcache('categoryinfo');
			showmsg(L('operation_success'),'',1);
		}
	}
	
	
	/**
	 * 更新内容页URL
	 */
 	public function update_content_url() {

		$modelid = isset($_POST['modelid']) ? intval($_POST['modelid']) : (isset($_GET['modelid']) ? intval($_GET['modelid']) : 0);
		
		$modelinfo = get_modelinfo();
		//根据系统URL规则生成内容URL
		$url_rule = get_config('url_rule');
		
		//当选择所有模型时，则更新所有内容
		if(!$modelid){
			$i = isset($_GET['i']) ? intval($_GET['i']) : 0;
			$num = count($modelinfo);
			if($i >= $num) showmsg('更新完成！', U('init'), 1); 
			$tablename = $modelinfo[$i]['tablename'];
			if(!$tablename) showmsg('模型错误，请检查!', U('init'));
			
			$db = D($tablename);
			$r = $db->field('catid,id')->limit('1000')->select();   //防止数据过多，暂且取前1000条
			foreach($r as $val){
				$url = $this->get_url($url_rule, $val['catid'], $val['id']);	
				$db->update(array('url' => $url), array('id' => $val['id']));
			}
			
			showmsg($modelinfo[$i]['name'].'更新完成...', U('update_content_url', array('i'=>++$i, 'modelid'=>0)), 1);
			
		}else{
			$modelarr = array();
			foreach($modelinfo as $val){
				$modelarr[$val['modelid']] = $val;
			}
			if(!isset($modelarr[$modelid])) showmsg('模型不存在！');
			$db = D($modelarr[$modelid]['tablename']);
			$r = $db->field('catid,id')->limit('1000')->select();  //防止数据过多，暂且取前1000条
			foreach($r as $val){
				$url = $this->get_url($url_rule, $val['catid'], $val['id']);	
				$db->update(array('url' => $url), array('id' => $val['id']));
			}
			showmsg($modelarr[$modelid]['name'].'更新完成！', U('init'), 1); 
		}

	}
	
	
	/**
	 * 获取内容页URL
	 */
	private function get_url($url_rule, $catid, $id){
		
		//如果系统设置是伪静态模式
		if($url_rule){
			$catinfo = get_category($catid);
			$url = URL_MODEL == 1 ? SITE_URL.'index.php?s=/'.$catinfo['catdir'].'/'.$id.C('url_html_suffix') : SITE_URL.$catinfo['catdir'].'/'.$id.C('url_html_suffix');
		}else{  
			$url = U('index/index/show',array('catid'=>$catid,'id'=>$id));
		}
				
		return $url;
	}
	
}