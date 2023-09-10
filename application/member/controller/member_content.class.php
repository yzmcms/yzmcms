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
	
	public function __construct() {
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
		$fields = array('title','keywords','copyfrom','catid','thumb','description','content');
	
		if(is_post()) {
			
			$catid = intval($_POST['catid']);
			
			//判断栏目是否禁止投稿
			$data = D('category')->field('member_publish')->where(array('catid'=>$catid))->find();
			if(!$data['member_publish']) return_message(L('illegal_operation'), 0);
			
			//支持不同栏目自动实例化不同的model
			$modelid = get_category($catid, 'modelid');
			
			yzm_base::load_sys_class('form','',0);
			$field_check = $this->_get_model_str($modelid, true);
			foreach($field_check as $k => $v){
				if($v['isrequired']){
					if(!isset($_POST[$k])) return_message(L('lose_parameters'), 0);
					$length = is_array($_POST[$k]) ? (empty($_POST[$k]) ? 0 : 1) : strlen($_POST[$k]);
					if(!$length) return_message($v['errortips'], 0);
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
			
			$_POST['issystem'] = '0';
			$_POST['status'] = $is_adopt;	
			$_POST['listorder'] = '10';		//为内容置顶做准备
			$_POST['description'] = empty($_POST['description']) ? str_cut(strip_tags($_POST['content']),250) : $_POST['description'];
			$_POST['inputtime'] = SYS_TIME;
			$_POST['updatetime'] = SYS_TIME;
			$_POST['catid'] = $catid;
			$_POST['userid'] = $userid;	
			$_POST['username'] = $username;	
			$_POST['nickname'] = $nickname;		
			
			
			$content_tabname = D(get_model($modelid));
			$id = $content_tabname->insert($_POST);
			
			//写入全部模型表
			$_POST['siteid'] = get_siteid();
			$_POST['modelid'] = $modelid;
			$_POST['id'] = $id;
			$allid = D('all_content')->insert($_POST);
			update_attachment($modelid, $id);

			//如果设置了扣除积分
			if(get_config('publish_point') < 0){
				$this->_point_spend($catid, $id);
			}
			
			if(!$is_adopt){
				return_message('发布成功，等待管理员审核！', 1, U('not_pass'));
			}else{
				$this->_adopt($content_tabname, $catid, $id, $allid);
				return_message('发布成功，内容已通过审核！', 1, U('pass'));
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
		$fields = array('title','keywords','copyfrom','catid','thumb','description','content');
	
		if(is_post()) {
			
			$id = isset($_POST['id']) ? intval($_POST['id']) : return_message(L('lose_parameters'), 0);
			$_POST['catid'] = intval($_POST['catid']);
			
			//判断栏目是否禁止投稿
			$data = D('category')->field('member_publish')->where(array('catid'=>$_POST['catid']))->find();
			if(!$data['member_publish']) return_message('该栏目不允许在线投稿！', 0);
			
			//根据POST传回的参数再次判断一下modelid（必须）
			$modelid = get_category($_POST['catid'], 'modelid');
			if(!$modelid){
				return_message(L('operation_failure'), 0);
			}
			$content_tabname = D(get_model($modelid));			
			
			$all_content = D('all_content');
			$data = $all_content->field('username,status,issystem')->where(array('modelid' => $modelid, 'id' => $id))->find();
			if(!$data || $data['username'] != $username || $data['issystem']){
				return_message(L('illegal_operation'), 0);
			}
			
			$field_check = $this->_get_model_str($modelid, true);
			foreach($field_check as $k => $v){
				if($v['isrequired']){
					if(!isset($_POST[$k])) return_message(L('lose_parameters'), 0);
					$length = is_array($_POST[$k]) ? (empty($_POST[$k]) ? 0 : 1) : strlen($_POST[$k]);
					if(!$length) return_message($v['errortips'], 0);
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

			$_POST['description'] = empty($_POST['description']) ? str_cut(strip_tags($_POST['content']),250) : $_POST['description'];
			$_POST['updatetime'] = SYS_TIME;
			$_POST['status'] = $is_adopt;	
			
			if($content_tabname->update($_POST, array('id' => $id))){
				$all_content->update($_POST, array('modelid' => $modelid, 'id' => $id));
				update_attachment($modelid, $id);
				if(!$is_adopt){
					return_message('操作成功，等待管理员审核！', 1, U('not_pass'));
				}else{
					return_message('操作成功，内容已通过审核！', 1, U('pass'));
				}
			}

		}

		//可以根据catid获取model模型，来加载不同模板
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : showmsg(L('lose_parameters'), 'stop');  
		$id = isset($_GET['id']) ? intval($_GET['id']) : showmsg(L('lose_parameters'), 'stop');
		
		$modelid = get_category($catid, 'modelid');
		if(!$modelid)  showmsg(L('lose_parameters'), 'stop');  
		$content_tabname = D(get_model($modelid));
		
		$data = $content_tabname->where(array('id' => $id))->find(); 
		if(!$data || $data['username'] != $username || $data['issystem']){
			showmsg(L('illegal_operation'), 'stop');
		}		
		
		$fieldstr = $this->_get_model_str($modelid, false, $data);
		$field_check = $this->_get_model_str($modelid, true);
		include template('member', 'edit_through');
	}

	
	
	/**
	 * 已通过的稿件
	 */
	public function pass(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$all_content = D('all_content');
		$total = $all_content->where(array('userid' =>$userid,'issystem' =>0,'status' =>1))->total();
		$page = new page($total, 15);
		$data = $all_content->field('modelid,catid,id,title,url,thumb,inputtime,updatetime')->where(array('userid' =>$userid,'issystem' =>0,'status' =>1))->order('updatetime DESC')->limit($page->limit())->select();
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull(false);
		include template('member', 'publish_through');
	}



	/**
	 * 未通过的稿件
	 */
	public function not_pass(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$all_content = D('all_content');
		$total = $all_content->where(array('userid' =>$userid,'issystem' =>0,'status' =>0))->total();
		$page = new page($total, 15);
		$data = $all_content->field('modelid,catid,id,title,url,thumb,inputtime,updatetime,status')->where(array('userid' =>$userid,'issystem' =>0,'status!=' =>1))->order('updatetime DESC')->limit($page->limit())->select();
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull(false);
		include template('member', 'publish_not_through');
	}
	
	
	
	/**
	 * 删除未通过的稿件
	 */
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
		$all_content = D('all_content');
		$data = $all_content->field('username,status,issystem')->where(array('modelid' => $modelid, 'id' => $id))->find();
		//只能删除自己的 且 未通过审核的
		if($data && $data['username'] == $username && $data['status'] != 1 && $data['issystem'] == 0){
			$all_content->delete(array('modelid' => $modelid, 'id' => $id));
			$content_tabname->delete(array('id' => $id));
			delete_attachment($modelid, $id);
		}
		showmsg(L('operation_success'), '', 1);
	}


	/**
	 * 我的评论
	 */
	public function comment_list(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$comment = D('comment');
		$total = $comment->where(array('userid'=>$userid))->total();
		$page = new page($total, 10);
		$data = $comment->alias('a')->field('a.id,a.inputtime,a.ip,a.content,a.status,b.title,b.url')->join('yzmcms_comment_data b ON a.commentid=b.commentid')->where(array('userid' =>$userid))->order('id DESC')->limit($page->limit())->select();
		foreach ($data as $key => $val) {
			if(strpos($val['content'], 'original_comment') !==false){
				$pos = strrpos($val['content'], '</a>');
				$val['content'] = substr($val['content'], $pos+7);
			}
			$data[$key] = $val;
		}
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull(false);
		include template('member', 'comment_list');
	}
	
	
	
	/**
	 * 删除评论
	 */
	public function comment_del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		if(!isset($_POST['fx'])) showmsg('请选择要操作的内容！');
		if(!is_array($_POST['fx'])) showmsg(L('illegal_operation'), 'stop');
		$comment = D('comment');
		foreach($_POST['fx'] as $v){
			$comment_data = $comment ->field('userid,commentid')->where(array('id'=>$v))->find();
			if(!$comment_data || $comment_data['userid']!=$userid) showmsg('该评论不存在，请返回检查！', 'stop');
			$commentid = $comment_data['commentid'];
			$comment->delete(array('id'=>$v));
			$comment->query("UPDATE yzmcms_comment_data SET `total` = `total`-1 WHERE commentid='$commentid'");
			$comment->delete(array('reply'=>$v));
		}
		showmsg(L('operation_success'), '', 1);
	}
	
	
	
	/**
	 * 收藏夹
	 */
	public function favorite(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		$favorite = D('favorite');
		$total = $favorite->where(array('userid' =>$userid))->total();
		$page = new page($total, 15);
		$data = $favorite->where(array('userid' =>$userid))->order('id DESC')->limit($page->limit())->select();
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull(false);
		include template('member', 'favorite');
	}
	
	
	
	/**
	 * 删除收藏夹
	 */
	public function favorite_del(){
		$memberinfo = $this->memberinfo;
		extract($memberinfo);
		
		if(!isset($_POST['fx'])) showmsg('请选择要操作的内容！');
		if(!is_array($_POST['fx'])) showmsg(L('illegal_operation'), 'stop');
		$favorite = D('favorite');
		foreach($_POST['fx'] as $v){
			$favorite->delete(array('id' => intval($v), 'userid' => $userid));
		}
		showmsg(L('operation_success'), '', 1);
	}

	
	/**
	 * 检查会员组权限
	 */
	private function _check_group_auth($groupid, $is_add = true){
		$memberinfo = $this->memberinfo;
		$groupinfo = get_groupinfo($groupid);
		if(strpos($groupinfo['authority'], '3') === false) 
		return_message('你没有权限投稿，请升级会员组！', 0);

		if($is_add){
			//检查用户每日最大投稿量，且VIP用户不受“每日最大投稿量”限制
			if(!$memberinfo['vip'] || $memberinfo['overduedate']<SYS_TIME){
				$total = D('all_content')->where(array('userid'=>$this->userid, 'issystem'=>0, 'inputtime>'=> strtotime(date('Y-m-d'))))->total();
				if($total >= $groupinfo['max_amount']) return_message('当前会员每日最大投稿数为 '.$groupinfo['max_amount'].' 条，如需发布更多请升级会员组！', 0); 
			}			
		}

		//如果是投稿收费则检测积分是否够用
		$publish_point = get_config('publish_point');
		if(($publish_point < 0) && ($memberinfo['point'] < abs($publish_point))){
			return_message('本次交易将扣除点 '.abs($publish_point).' 积分，您的余额不足！', 0);
		}

		return $groupinfo;
	}
	
	
	/**
	 * 获取不同模型获取HTML表单
	 */
	private function _get_model_str($modelid, $field = false, $data = array()) {
		$modelinfo = getcache($modelid.'_model');
		if($modelinfo === false){
			$modelinfo = D('model_field')->where(array('modelid' => $modelid, 'disabled' => 0))->order('listorder ASC,fieldid ASC')->select();
			setcache($modelid.'_model', $modelinfo);
		}
		
		$fields = $fieldstr = array();
		foreach($modelinfo as $val){
			if($val['isadd'] == 0) continue;
			$fieldtype = $val['fieldtype']=='decimal' ? 'input' : $val['fieldtype'];
			if($data){
				$val['defaultvalue'] = isset($data[$val['field']]) ? $data[$val['field']] : '';
			}
			$setting = $val['setting'] ? string2array($val['setting']) : 0;
			$required = $val['isrequired'] ? '<span class="required">*</span>' : '';
			$fieldstr[] = array(
				'field' => $required.$val['name'],
				'form' => form::$fieldtype($val['field'], $val['defaultvalue'], $setting)
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
	private function _adopt($content_tabname, $catid, $id, $allid){
		$url = get_content_url($catid, $id);
		$content_tabname->update(array('url' => $url), array('id' => $id));  
		D('all_content')->update(array('url' => $url), array('allid' => $allid));  
		
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