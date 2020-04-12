<?php
/**
 * 会员中心内容操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-03-23
 */
 
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'member', 0);
yzm_base::load_sys_class('page','',0);

class member_content extends common{
	
	function __construct() {
		parent::__construct();
	}

	
	/**
	 * 在线投稿
	 */	
	public function init(){ 
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$this->_check_group_auth($groupid); 
		yzm_base::load_sys_class('form','',0);

		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$modelid = !$catid ? 1 : get_category($catid, 'modelid');
		if(!$modelid)  showmsg(L('illegal_operation'), 'stop');
		
		$category_data = select_category('catid', $catid, '≡ 请选择栏目 ≡', 1, 'onchange="javascript:change_model(this.value);"');
		$fieldstr = $this->_get_model_str($modelid);
		$field_check = $this->_get_model_str($modelid, true);

		include template('member', 'publish');
	}


	/**
	 * 在线投稿-发布稿件
	 */	
	public function publish(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$groupinfo = $this->_check_group_auth($groupid);	
		
		//会员中心可发布的字段
		$fields = array('title','copyfrom','catid','thumb','description','content');
	
		if(isset($_POST['dosubmit'])) {
			
			$catid = intval($_POST['catid']);
			
			//判断栏目是否禁止投稿
			$data = D('category')->field('member_publish')->where(array('catid'=>$catid))->find();
			if(!$data['member_publish']) showmsg(L('illegal_operation'), 'stop');
			
			//支持不同栏目自动实例化不同的model
			$modelid = get_category($catid, 'modelid');
			
			yzm_base::load_sys_class('form','',0);
			$field_check = $this->_get_model_str($modelid, true);
			foreach($field_check as $k => $v){
				if($v['isrequired']){
					if(empty($_POST[$k])) showmsg($v['errortips']);
				}
			}

			$fields = array_merge($fields, array_keys($field_check));
			$notfilter_field = $this->_get_notfilter_field($modelid);
			
			foreach($_POST as $_k=>$_v) {
				if(!in_array($_k, $fields)){
					unset($_POST[$_k]);
					continue;
				} 
				if(in_array($_k, $notfilter_field)) {
					$_POST[$_k] = remove_xss(strip_tags($_v, '<p><a><br><img><ul><li><div><strong><table><th><tr><td>'));
				}else{
					$_POST[$_k] = !is_array($_POST[$_k]) ? new_html_special_chars(trim_script($_v)) : $this->_content_dispose($_v);
				}
			}
			
			//会员权限-投稿免审核
			$is_adopt = strpos($groupinfo['authority'], '4') === false ? 0 : 1;
			
			$_POST['seo_title'] = $_POST['title'].'_'.get_config('site_name');
			$_POST['system'] = '0';
			$_POST['status'] = $is_adopt;	
			$_POST['listorder'] = '10';		//为内容置顶做准备
			$_POST['description'] = empty($_POST['description']) ? str_cut(strip_tags($_POST['content']),200) : $_POST['description'];
			$_POST['inputtime'] = SYS_TIME;
			$_POST['updatetime'] = SYS_TIME;
			$_POST['catid'] = $catid;
			$_POST['userid'] = $userid;	
			$_POST['username'] = $username;	
			$_POST['nickname'] = $nickname;		
			
			
			$content_tabname = D(get_model($modelid));
			$id = $content_tabname->insert($_POST);
			
			//发布到用户内容列表中
			$_POST['checkid'] = $modelid.'_'.$id;
			D('member_content')->insert($_POST);

			//如果设置了扣除积分
			if(get_config('publish_point') < 0){
				$this->_point_spend($catid, $id);
			}
			
			if(!$is_adopt){
				showmsg('发布成功，等待管理员审核！', U('not_pass'));
			}else{
				$this->_adopt($content_tabname, $catid, $id);
				showmsg('发布成功，内容已通过审核！', U('pass'));
			}
		}
		
	}
	
	
	/**
	 * 编辑稿件
	 */	
	public function edit_through(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$groupinfo = $this->_check_group_auth($groupid, false);	
		yzm_base::load_sys_class('form','',0);
		
		//会员中心可发布的字段
		$fields = array('title','copyfrom','catid','thumb','description','content');
		
		//可以根据catid获取model模型，来加载不同模板
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : showmsg(L('lose_parameters'), 'stop');  
		$id = isset($_GET['id']) ? intval($_GET['id']) : showmsg(L('lose_parameters'), 'stop');
	
		if(isset($_POST['dosubmit'])) {
			
			$_POST['catid'] = intval($_POST['catid']);
			
			//判断栏目是否禁止投稿
			$data = D('category')->field('member_publish')->where(array('catid'=>$_POST['catid']))->find();
			if(!$data['member_publish']) showmsg('该栏目不允许在线投稿！', 'stop');
			
			//根据POST传回的参数再次判断一下modelid（必须）
			$modelid = get_category($_POST['catid'], 'modelid');
			if(!$modelid){
				showmsg(L('operation_failure'), 'stop');
			}
			$content_tabname = D(get_model($modelid));			
			
			$member_content = D('member_content');
			$data = $member_content->field('username,status')->where(array('checkid' =>$modelid.'_'.$id))->find();
			//只能编辑自己发布的内容
			if(!$data || $data['username'] != $username){
				showmsg(L('illegal_operation'), 'stop');
			}
			
			$field_check = $this->_get_model_str($modelid, true);
			foreach($field_check as $k => $v){
				if($v['isrequired']){
					if(empty($_POST[$k])) showmsg($v['errortips']);
				}
			}
			$fields = array_merge($fields, array_keys($field_check));
			$notfilter_field = $this->_get_notfilter_field($modelid);
			
			foreach($_POST as $_k=>$_v) {
				if(!in_array($_k, $fields)){
					unset($_POST[$_k]);
					continue;
				}
				if(in_array($_k, $notfilter_field)) {
					$_POST[$_k] = remove_xss(strip_tags($_v, '<p><a><br><img><ul><li><div><strong><table><th><tr><td>'));
				}else{
					$_POST[$_k] = !is_array($_POST[$_k]) ? new_html_special_chars(trim_script($_v)) : $this->_content_dispose($_v);
				}
			}
			
			//会员权限-投稿免审核
			$is_adopt = strpos($groupinfo['authority'], '4') === false ? 0 : 1;

			$_POST['seo_title'] = $_POST['title'].'_'.get_config('site_name');
			$_POST['description'] = empty($_POST['description']) ? str_cut(strip_tags($_POST['content']),200) : $_POST['description'];
			$_POST['updatetime'] = SYS_TIME;
			$_POST['status'] = $is_adopt;	
			
			if($content_tabname->update($_POST, array('id' => $id))){
				$member_content->update($_POST, array('checkid' =>$modelid.'_'.$id));	//更新会员内容表
				if(!$is_adopt){
					showmsg('操作成功，等待管理员审核！', U('not_pass'));
				}else{
					showmsg('操作成功，内容已通过审核！', U('pass'));
				}
			}

		}
		
		$modelid = get_category($catid, 'modelid');
		if(!$modelid)  showmsg(L('lose_parameters'), 'stop');  
		$content_tabname = D(get_model($modelid));
		
		$data = $content_tabname->where(array('id' => $id))->find(); 
		
		$fieldstr = $this->_get_model_str($modelid, false, $data);
		$field_check = $this->_get_model_str($modelid, true);
		include template('member', 'edit_through');
	}

	
	
	//已通过的稿件
	public function pass(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$member_content = D('member_content');
		$total = $member_content->where(array('userid' =>$userid,'status' =>1))->total();
		$page = new page($total, 15);
		$res = $member_content->field('checkid,catid,title,inputtime,updatetime')->where(array('userid' =>$userid,'status' =>1))->order('updatetime DESC')->limit($page->limit())->select();
		$data = array();
		foreach($res as $val) {
			list($val['modelid'], $val['id']) = explode('_', $val['checkid']);
			$val['url'] = U('index/index/show', array('catid'=>$val['catid'],'id'=>$val['id']));
			$data[] = $val;
		}
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'publish_through');
	}



	//未通过的稿件
	public function not_pass(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$member_content = D('member_content');
		$total = $member_content->where(array('userid' =>$userid,'status' =>0))->total();
		$page = new page($total, 15);
		$res = $member_content->field('checkid,catid,title,inputtime,updatetime,status')->where(array('userid' =>$userid,'status!=' =>1))->order('updatetime DESC')->limit($page->limit())->select();
		$data = array();
		foreach($res as $val) {
			list($val['modelid'], $val['id']) = explode('_', $val['checkid']);
			$data[] = $val;
		}
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'publish_not_through');
	}
	
	
	
	//删除未通过的稿件
	public function del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : showmsg(L('lose_parameters'), 'stop');
		$id = isset($_GET['id']) ? intval($_GET['id']) : showmsg(L('lose_parameters'), 'stop');
		
		$modelid = get_category($catid, 'modelid');
		if(!$modelid){
			showmsg(L('operation_failure'), 'stop');
		}
		$content_tabname = D(get_model($modelid));
		$member_content = D('member_content');
		$data = $member_content->field('username,status')->where(array('checkid' =>$modelid.'_'.$id))->find();
		//只能删除自己的 且 未通过审核的
		if($data && $data['username'] == $username && $data['status'] != 1){
			$member_content->delete(array('checkid' =>$modelid.'_'.$id));	//删除会员内容表
			$content_tabname->delete(array('id' => $id));	 //删除model内容表
		}
		showmsg(L('operation_success'));
	}
	
	
	
	//收藏夹
	public function favorite(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$favorite = D('favorite');
		$total = $favorite->where(array('userid' =>$userid))->total();
		$page = new page($total, 15);
		$data = $favorite->where(array('userid' =>$userid))->order('id DESC')->limit($page->limit())->select();
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('member', 'favorite');
	}
	
	
	
	//删除收藏夹
	public function favorite_del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		if(!isset($_POST['fx'])) showmsg('您没有选择项目！');
		if(!is_array($_POST['fx'])) showmsg(L('illegal_operation'), 'stop');
		$favorite = D('favorite');
		foreach($_POST['fx'] as $v){
			$favorite->delete(array('id' => intval($v), 'userid' => $userid));
		}
		showmsg(L('operation_success'));
	}

	
	//检查会员组权限
	private function _check_group_auth($groupid, $is_add = true){
		$memberinfo = $this->memberinfo;
		$groupinfo = get_groupinfo($groupid);
		if(strpos($groupinfo['authority'], '3') === false) 
		showmsg('你没有权限投稿，请升级会员组！', 'stop'); 

		if($is_add){
			//检查用户每日最大投稿量，且VIP用户不受“每日最大投稿量”限制
			if(!$memberinfo['vip'] || $memberinfo['overduedate']<SYS_TIME){
				$total = D('member_content')->where(array('userid'=>$this->userid, 'inputtime>'=> strtotime(date('Y-m-d'))))->total();
				if($total >= $groupinfo['max_amount']) showmsg('当前会员每日最大投稿数为 '.$groupinfo['max_amount'].' 条，如需发布更多请升级会员组', 'stop'); 
			}			
		}

		//如果是投稿收费则检测积分是否够用
		$publish_point = get_config('publish_point');
		if(($publish_point < 0) && ($memberinfo['point'] < abs($publish_point))){
			showmsg('本次交易将扣除点 '.abs($publish_point).' 积分，您的余额不足！', 'stop');
		}

		return $groupinfo;
	}
	
	
	//获取不同模型获取HTML表单
	private function _get_model_str($modelid, $field = false, $data = array()) {
		$modelinfo = getcache($modelid.'_model');
		if($modelinfo === false){
			$modelinfo = D('model_field')->where(array('modelid' => $modelid, 'disabled' => 0))->order('listorder ASC,fieldid ASC')->select();
			setcache($modelid.'_model', $modelinfo);
		}
		
		$fields = $fieldstr = array();
		foreach($modelinfo as $val){
			if($val['isadd'] == 0) continue;
			$fieldtype = $val['fieldtype'];
			if($data){
				$val['defaultvalue'] = isset($data[$val['field']]) ? $data[$val['field']] : '';
			}
			$setting = $val['setting'] ? string2array($val['setting']) : 0;
			$required = $val['isrequired'] ? '<span class="red">*</span>' : '';
			$fieldstr[] = array(
				'field' => $val['name'],
				'form' => form::$fieldtype($val['field'], $val['defaultvalue'], $setting).$required
			);
			$fields[$val['field']] = $val['isrequired'] ? array('isrequired'=>1, 'fieldtype'=>$fieldtype, 'errortips'=>$val['errortips'] ? $val['errortips'] : $val['name'].'不能为空！') : array('isrequired'=>0);
		}
		
		return $field ? $fields : $fieldstr;
	}
	

	/**
	 * 获取模型非过滤字段
	 */	
	private function _get_notfilter_field($modelid) {
		$arr = array('content');
		$data = D('model_field')->field('field,fieldtype')->where(array('modelid' => $modelid))->select();
		foreach($data as $val){
			if($val['fieldtype'] == 'editor' || $val['fieldtype'] == 'editor_mini') $arr[] = $val['field'];
		}

		return $arr;
	} 
	
	
	/**
	 * 内容处理
	 * @param $content 
	 */	
	private function _content_dispose($content) {
		$is_array = false;
		foreach($content as $val){
			if(is_array($val)) $is_array = true;
			break;
		}
		if(!$is_array) return safe_replace(implode(',', $content));
		
		//这里认为是多文件上传
		$arr = array();
		foreach($content['url'] as $key => $val){
			$arr[$key]['url'] = safe_replace($val);
			$arr[$key]['alt'] = safe_replace($content['alt'][$key]);
		}		
		return array2string($arr);
	}
	
	
	/**
	 * 内容通过审核
	 * @param $content_tabname 
	 * @param $catid 
	 * @param $id 
	 */		
	private function _adopt($content_tabname, $catid, $id){
		$url = get_content_url($catid, $id);
		$content_tabname->update(array('url' => $url), array('id' => $id));  
		
		//投稿奖励积分和经验
		$publish_point = get_config('publish_point');
		if($publish_point > 0){
			M('point')->point_add(1, $publish_point, 2, $this->memberinfo['userid'], $this->memberinfo['username'], $this->memberinfo['experience'], $catid.'_'.$id);
		}
	}


	/**
	 * 扣除积分
	 * @param $catid 
	 * @param $id 
	 */	
	private function _point_spend($catid, $id){
		$publish_point = get_config('publish_point');
		M('point')->point_spend(1, abs($publish_point), 11, $this->memberinfo['userid'], $this->memberinfo['username'], $catid.'_'.$id);
		return true;
	}
}