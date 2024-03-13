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
yzm_base::load_common('lib/content_form'.EXT, 'admin');
yzm_base::load_sys_class('page','',0);

class content extends common {
	
	private $content;
	public function __construct() {
		parent::__construct();
		$this->content = M('content_model');
	}

	/**
	 * 内容列表
	 */
	public function init() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','catid','click','username','updatetime','status','is_push')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$modelinfo = $this->content->modelarr;
		$default_model = get_default_model();
		$content_db = D($default_model['tablename']);
		$modelid = $default_model['modelid'];
		$catid = 0; 
		$where = $this->_all_priv() ? array() : array('userid'=>$_SESSION['adminid']);
		$total = $content_db->where($where)->total();
		$page = new page($total, 15);
		$data = $content_db->where($where)->order("$of $or")->limit($page->limit())->select();	
		include $this->admin_tpl('content_list');
	}


	/**
	 * 内容搜索
	 */
	public function search() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','catid','click','username','updatetime','status','is_push')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$modelinfo = $this->content->modelarr;
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$readpoint = isset($_GET['readpoint']) ? intval($_GET['readpoint']) : 0;
		$content_db = D($this->content->tabname);
		$where = $this->_all_priv() ? '1=1' : 'userid='.$_SESSION['adminid'];
		if(isset($_GET['dosubmit'])){	
		
			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;

			if(isset($_GET['status']) && $_GET['status'] != '99'){
				$where .= ' AND status = '.intval($_GET['status']);
			}	

			if($catid){
				$where .= ' AND catid='.$catid;
			}

			if($readpoint < 99){
				$where .= $readpoint ? ' AND readpoint>0' : ' AND readpoint=0';
			}

			if(isset($_GET['start']) && $_GET['start'] && $_GET['end']){		
				$where .= ' AND updatetime BETWEEN '.strtotime($_GET['start'].' 00:00:00').' AND '.strtotime($_GET['end'].' 23:59:59');
			}

			if(isset($_GET['flag']) && $_GET['flag'] != '0'){
				$where .= ' AND FIND_IN_SET('.intval($_GET['flag']).',flag)';
			}

			if($searinfo){
				if ($type == '1') {
				    $where .= ' AND `title` LIKE \'%'.$searinfo.'%\'';
				} elseif($type == '2') {
				    $where .= ' AND `username` LIKE \'%'.$searinfo.'%\'';
				} elseif($type == '3') {
				    $where .= ' AND `keywords` LIKE \'%'.$searinfo.'%\'';
				} elseif($type == '4') {
				    $where .= ' AND `description` LIKE \'%'.$searinfo.'%\'';
				}else {
				    $where .= ' AND id = '.intval($searinfo);
				}
			}

		}
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $content_db->where($where)->total();
		$page = new page($total, 15);
		$data = $content_db->where($where)->order("$of $or")->limit($page->limit())->select();		
		include $this->admin_tpl('content_list');
	}
	
	
	/**
	 * 添加内容
	 */
	public function add() {

		if(is_post()) {
			$r = $this->content->content_add($_POST);
			if(!$r) showmsg(L('operation_failure'), 'stop');

			if(isset($_POST['continuity'])){
				showmsg(L('successfully_published_continue'), U('content/add',array('modelid'=>$this->content->modelid)), 1);
			}else{
				echo '<script type="text/javascript">setTimeout("parent.location.reload()",1000);</script>';
				showmsg(L('successfully_published_content'), U('content/search',array('modelid'=>$this->content->modelid)), 2);
			}
		}else{
			$catid = isset($_GET['catid']) ? intval($_GET['catid']) : intval(get_cookie('catid'));
			$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
			$content_form = new content_form($modelid);
			$string = $content_form->content_add();
			$member_group = get_groupinfo();
			include $this->admin_tpl('content_add');
		}
	}
	
	
	/**
	 * 修改内容
	 */
	public function edit() {
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$content_db = D($this->content->tabname);
		$data = $content_db->where(array('id'=>$id))->find();
		if(!$this->_all_priv() && $data['userid']!==$_SESSION['adminid']){
			showmsg(L('illegal_operation'), 'stop');
		}
		if(is_post()) {
			if($this->content->modelid != get_category($_POST['catid'], 'modelid')) showmsg('不允许修改为其他模型的栏目！', 'stop');
			$r = $this->content->content_edit($_POST, $id);
			if($r){
				echo '<script type="text/javascript">setTimeout("parent.location.reload()",1000);</script>';
				showmsg(L('operation_success'), U('content/search',array('modelid'=>$this->content->modelid)), 2);
			}else{
				showmsg(L('operation_failure'), 'stop');
			}
		}else{
			$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
			$tags = D('tag_content')->alias('a')->field('tag')->join('yzmcms_tag b ON a.tagid=b.id', 'LEFT')->where(array('modelid'=>$modelid, 'aid'=> $id))->order('id ASC')->select();
			$content_form = new content_form($_GET['modelid']);
			$string = $content_form->content_edit($data);
			$member_group = get_groupinfo();
			if(!$data['issystem']) $allid = D('all_content')->field('allid')->where(array('modelid'=>$this->content->modelid, 'id'=>$id))->one();
			include $this->admin_tpl('content_edit');	
		}
	}
	
	
	/**
	 * 删除内容
	 */
	public function del() {
		if($_POST && is_array($_POST['ids'])){
			$all_priv = $this->_all_priv();
			foreach($_POST['ids'] as $id){
				if(!$all_priv){
					$userid = D($this->content->tabname)->field('userid')->where(array('id'=>$id))->one();
					if($userid!==$_SESSION['adminid']) continue;
				}
				$this->content->content_delete($id); 
			}
		}
		return_json(array('status'=>1,'message'=>L('operation_success')));
	}


	/**
	 * 数据快速修改
	 */
	public function public_fast_edit(){
		$id = input('post.pk', 0, 'intval');
		$value = input('post.value', 0, 'intval');
		if(!$id) return_json(array('status'=>0, 'message'=>L('illegal_parameters')));	
		$db = D($this->content->tabname);
		$db->update(array('click'=>$value), array('id'=>$id));
		return_json(array('status'=>1, 'message'=>L('operation_success')));	
	}


	/**
	 * 百度主动推送
	 */
	public function baidu_push() {
		if($_POST && is_array($_POST['ids'])){
			if(empty($_POST['ids'])) return_json(array('status'=>0,'message'=>L('lose_parameters')));
			$content = D($this->content->tabname);
			$data = $content->field('url,is_push')->wheres(array('id'=>array('in', $_POST['ids'], 'intval')))->select();
			$urls = array();
			$site_url = get_site_url();
			foreach ($data as $value) {
				if($value['is_push']) continue;
				$urls[] = strpos($value['url'], '://') ? $value['url'] : rtrim($site_url, '/').$value['url'];
			}

			if(!empty($urls)){
				if(self::$siteid){
					$baidu_push_token = get_site(self::$siteid, 'baidu_push_token');
					if(!$baidu_push_token) return_json(array('status'=>0,'message'=>'百度推送token为空，请到站点管理中配置！'));
				}else{
					$baidu_push_token = get_config('baidu_push_token');
					if(!$baidu_push_token) return_json(array('status'=>0,'message'=>'百度推送token为空，请到系统管理中配置！'));
				}
				
				$parse_url = parse_url($site_url);
				$api_url = 'http://data.zz.baidu.com/urls?site='.$parse_url['host'].'&token='.$baidu_push_token;
				$ch = curl_init();
				$options =  array(
				    CURLOPT_URL => $api_url,
				    CURLOPT_POST => true,
				    CURLOPT_RETURNTRANSFER => true,
				    CURLOPT_POSTFIELDS => implode("\n", $urls),
				    CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
				);
				curl_setopt_array($ch, $options);
				$result = curl_exec($ch);
				curl_close($ch);
				$result = json_decode($result, true);
				if(isset($result['success'])){
					$content->wheres(array('id'=>array('in', $_POST['ids'], 'intval')))->update(array('is_push' => 1));
					return_json(array('status'=>1,'message'=>'成功推送'.$result['success'].'条URL地址！'));
				}else{
					return_json(array('status'=>0,'message'=>'推送失败，错误码：'.$result['error']));
				}
			}
			return_json(array('status'=>0,'message'=>'没有数据被推送！'));
		}
	}

	
	
	/**
	 * 移动分类
	 */
	public function remove() {
		if(is_post()) {
			$ids = safe_replace($_POST['ids']);
			$ids_arr = explode(',', $ids);
			$catid = intval($_POST['catid']);
			$db = D($this->content->tabname);
			foreach ($ids_arr as $id) {
				$data = array('catid'=>$catid);
				$res = $db->field('flag')->where(array('id' => $id))->find();
				if($res && !strstr($res['flag'], '7')) $data['url'] = get_content_url($catid, $id);

				$db->update($data, array('id'=>$id));
				D('all_content')->update($data, array('modelid'=>$this->content->modelid, 'id'=>$id));
			}
			return_json(array('status' => 1, 'message' => L('operation_success')));
		}else{
			$modelid = $this->content->modelid;
			include $this->admin_tpl('content_remove');	
		}
	}



	/**
	 * 复制内容
	 */
	public function copy() {
		if(is_post()) {
			$ids = safe_replace($_POST['ids']);
			$ids_arr = array_reverse(explode(',', $ids));
			$catid = intval($_POST['catid']);
			$target_modelid = get_category($catid, 'modelid');
			if(!$target_modelid) return_json(array('status' => 0, 'message' => '模型错误，请检查！'));
			$db = D($this->content->tabname);
			$target_db = D(get_model($target_modelid));
			$all_content = D('all_content');
			foreach ($ids_arr as $id) {
				$id = intval($id);
				$res = $db->where(array('id' => $id))->find();
				if(!$res) continue;
				$res['catid'] = $catid;
				$res['is_push'] = 0;
				$target_id = $target_db->insert($res);
				if(!strstr($res['flag'], '7')){
					$target_db->update(array('url' => get_content_url($catid, $target_id)), array('id' => $target_id));
					$res['url'] = get_content_url($catid, $target_id);
				}
				$res['siteid'] = self::$siteid;
				$res['modelid'] = $target_modelid;
				$res['id'] = $target_id;
				$all_content->insert($res);
			}
			return_json(array('status' => 1, 'message' => L('operation_success')));
		}else{
			$modelid = $this->content->modelid;
			include $this->admin_tpl('content_remove');	
		}
	}
	

	/**
	 * 内容属性变更
	 */
	public function attribute_operation(){
		if(is_post()) {
			$op = isset($_POST['op']) ? intval($_POST['op']) : return_json(array('status' => 0, 'message' => '请选择属性变更类型！'));
			$ids = safe_replace($_POST['ids']);
			$ids_arr = explode(',', $ids);
			$ids_arr = array_map('intval', $ids_arr);
			$flag = isset($_POST['flag']) && is_array($_POST['flag']) ? $_POST['flag'] : array();
			if(!$flag) return_json(array('status' => 0, 'message' => '请选择要操作的属性！'));

			$db = D($this->content->tabname);
			foreach($ids_arr as $id){
				$data_flag = $db->field('flag')->where(array('id' => $id))->one();
				if($op){
					$new_flag = $data_flag ? array_unique(array_merge(explode(',', $data_flag), $flag)) : $flag;
				}else{
					$new_flag = $data_flag ? array_diff(explode(',', $data_flag), $flag) : array();
				}
				sort($new_flag);

				$where['listorder'] = array_search(1, $new_flag)!==false ? 1 : 10;
				$where['flag'] = join(',', $new_flag);

				$db->update($where, array('id' => $id));
			}
			return_json(array('status' => 1, 'message' => L('operation_success')));
			
		}else{
			$t = 1;
			$modelid = $this->content->modelid;
			include $this->admin_tpl('attribute_operation');	
		}
	}


	/**
	 * 内容状态变更
	 */
	public function status_operation(){
		if(is_post()) {
			$ids = safe_replace($_POST['ids']);
			$ids_arr = explode(',', $ids);
			$status = isset($_POST['status']) ? intval($_POST['status']) : return_json(array('status' => 0, 'message' => '请选择内容状态！'));
			$edit_updatetime = isset($_POST['edit_updatetime']) ? intval($_POST['edit_updatetime']) : 1;
			$new_data = $edit_updatetime ? array('status'=>$status, 'updatetime'=>SYS_TIME) : array('status'=>$status);
			$db = D($this->content->tabname);
			foreach ($ids_arr as $id) {
				$issystem = $db->field('issystem')->where(array('id' => $id))->one();
				if(!$issystem) continue;
				$db->update($new_data, array('id'=>$id));
				D('all_content')->update($new_data, array('modelid'=>$this->content->modelid, 'id'=>$id));
			}
			return_json(array('status' => 1, 'message' => L('operation_success')));
			
		}else{
			$t = 2;
			$modelid = $this->content->modelid;
			include $this->admin_tpl('attribute_operation');	
		}
	}


	/**
	 * 检查管理员是否具有管理所有内容权限
	 */
	private function _all_priv(){
		if($_SESSION['roleid'] == 1) return true;
		$res = D('admin_role_priv')->field('roleid')->where(array('roleid'=>$_SESSION['roleid'],'m'=>'admin','c'=>'content','a'=>'all_content'))->find();
		return $res ? true : false;
	}
	

}