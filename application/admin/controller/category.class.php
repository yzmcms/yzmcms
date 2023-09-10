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

class category extends common {
	
	private $db;
	public function __construct() {
		parent::__construct();
		$this->db = D('category');
	}
	
	
	/**
	 * 栏目列表
	 */
	public function init() {
		$modelinfo = get_site_modelinfo();
        $modelarr = array();
		foreach($modelinfo as $val){
			$modelarr[$val['modelid']] = $val['name'];
		}

		$category_show_status = isset($_COOKIE['category_show_status_'.self::$siteid]) ? json_decode($_COOKIE['category_show_status_'.self::$siteid], true) : array();
		$tree_toggle = 0;
		$childid_hide = '';
		if($category_show_status) foreach($category_show_status as $k=>$v){
			if($v == '1') {
				$childid_hide .= get_category($k, 'arrchildid', true).',';
				$tree_toggle = 1;
			}else{
				$tree_toggle = 0;
			}
		}
		$arrchildid_arr = explode(',', $childid_hide);

		$tree = yzm_base::load_sys_class('tree');
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$data = $this->db->field('catid AS id,catname AS name,parentid,`type`,modelid,listorder,member_publish,pclink,domain,display')->where(array('siteid'=>self::$siteid))->order('listorder ASC,catid ASC')->select();
		$array = array();
		foreach($data as $v) {
			if($v['type']=="0"){ 
				$string = 'yzm_open_full("添加内容", "'.U('content/add', array('modelid'=>$v['modelid'],'catid'=>$v['id'])).'")'; 
				$v['catlink'] = "javascript:;' onclick='".$string;
			}elseif($v['type']=="1"){ 
				$v['catlink'] = U('page_content', array('catid'=>$v['id']));
			}else{ 
				$v['catlink'] = $v['pclink']."'  target='_blank";
			}
			
			$icon = '&#xe653;';
			$action = '2';
			if($category_show_status && isset($category_show_status[$v['id']]) && $category_show_status[$v['id']]=='1'){
				$icon = '&#xe652;';
				$action = '1';
			}
			$show_status = in_array($v['id'], $arrchildid_arr) ? ' tr_hide' : '';
			$v['class'] = $v['parentid'] ? 'child'.$show_status : 'top';
			$v['parentoff'] = $v['parentid'] ? '' : '<i class="yzm-iconfont parentid" catid="'.$v['id'].'" action="'.$action.'">'.$icon.'</i> ';
			$v['domain'] = $v['domain'] ? '<div title="绑定域名：'.$v['domain'].'" style="color:#0194ff;font-size:12px" class="yzm-iconfont">&#xe64a; 域名</div>' : '';
			$v['cattype'] = $v['type']=="0" ? '普通栏目' : ($v['type']=="1" ? '<span style="color:green">单页面</span>' : '<span style="color:red">外部链接</span>');
			$v['catmodel'] = $v['modelid']&&isset($modelarr[$v['modelid']]) ? $modelarr[$v['modelid']] : '无';
			$v['display'] = $v['display'] ? '<span class="yzm-status-enable" data-field="display" data-id="'.$v['id'].'" onclick="yzm_change_status(this,\''.U('public_change_status').'\')"><i class="yzm-iconfont">&#xe81f;</i>是</span>' : '<span class="yzm-status-disable" data-field="display" data-id="'.$v['id'].'" onclick="yzm_change_status(this,\''.U('public_change_status').'\')"><i class="yzm-iconfont">&#xe601;</i>否</span>';
			$v['member_publish'] = $v['member_publish'] ? '<span class="yzm-status-enable" data-field="member_publish" data-id="'.$v['id'].'" onclick="yzm_change_status(this,\''.U('public_change_status').'\')"><i class="yzm-iconfont">&#xe81f;</i>是</span>' : '<span class="yzm-status-disable" data-field="member_publish" data-id="'.$v['id'].'" onclick="yzm_change_status(this,\''.U('public_change_status').'\')"><i class="yzm-iconfont">&#xe601;</i>否</span>';
			$v['string'] = '<a title="增加子类" href="javascript:;" onclick="yzm_open(\'增加栏目\',\''.U('add',array('modelid'=>$v['modelid'],'type'=>$v['type'],'catid'=>$v['id'])).'\',800,500)" class="btn-mini btn-primary ml-5" style="text-decoration:none">增加子类</a> 
			<a title="编辑栏目" href="javascript:;" onclick="yzm_open(\'编辑栏目\',\''.U('edit',array('type'=>$v['type'],'catid'=>$v['id'])).'\',800,500)" class="btn-mini btn-success ml-5" style="text-decoration:none">编辑</a> 
			<a title="删除" href="javascript:;" onclick="yzm_confirm(\''.U('delete',array('type'=>$v['type'],'catid'=>$v['id'])).'\', \'确定要删除【'.$v['name'].'】吗？\', 1)" class="btn-mini btn-danger ml-5" style="text-decoration:none">删除</a>';
			
			$array[] = $v;
		}	
		$str  = "<tr class='text-c \$class'>
					<td><input type='text' class='input-text listorder' name='listorder[]' value='\$listorder'><input type='hidden' name='catid[]' value='\$id'></td>
					<td>\$id</td>
					<td class='text-l'>\$parentoff\$spacer<a href='\$catlink' class='yzm_text_link'>\$name</a></td>
					<td>\$cattype</td>
					<td>\$catmodel</td>
					<td><a href='\$pclink' target='_blank'> <i class='yzm-iconfont yzm-iconlianjie'></i> 访问</a> \$domain</td>
					<td>\$display</td>
					<td>\$member_publish</td>
					<td class='td-manage'>\$string</td>
				</tr>";
		$tree->init($array);
		$categorys = $tree->get_tree(0, $str);		

		include $this->admin_tpl('category_list');
	}
	

	/**
	 * 排序栏目
	 */
	public function order() {
		if(isset($_POST['catid']) && is_array($_POST['catid'])){
			foreach($_POST['catid'] as $key=>$val){
				$this->db->update(array('listorder'=>$_POST['listorder'][$key]),array('catid'=>intval($val)));
			}
			$this->delcache();
		}
		showmsg(L('operation_success'), '' ,1);
	}

	
	/**
	 * 删除栏目
	 */
	public function delete() {
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
		
		$data = $this->db->field('arrparentid,arrchildid')->where(array('catid'=>$catid))->find();
		if(strpos($data['arrchildid'],',')) return_json(array('status'=>0,'message'=>'请先删除该分类下的子分类！'));
		
		$allid = D('all_content')->field('allid')->where(array('catid'=>$catid))->one();
		if($allid) return_json(array('status'=>0,'message'=>'请将该分类下的内容删除或移动到其他分类！'));
		if($this->db->delete(array('catid'=>$catid))){
			 if($type==1) D('page')->delete(array('catid' => $catid)); 
			 $this->repairs($data['arrparentid']);
			 $this->delcache();
			 return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			 return_json(array('status'=>0,'message'=>L('operation_failure')));
		}
	}
	
	
	/**
	 * 添加栏目
	 */
	public function add() {	
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : get_default_model('modelid');
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$type = isset($_GET['type']) ? intval($_GET['type']) : intval($_POST['type']);

		if(isset($_POST['dosubmit'])) { 

			if($_POST['domain']) $this->set_domain();

			$_POST['catname'] = trim($_POST['catname']);
			$_POST['catdir'] = trim($_POST['catdir'], ' /');

			if($type != 2){   //非外部链接
				$res = $this->db->where(array('siteid'=>self::$siteid, 'catdir'=>$_POST['catdir']))->find();
				if($res) return_json(array('status'=>0,'message'=>'栏目目录已存在！'));	
			}		


			if(!$_POST['mobname']) $_POST['mobname'] = $_POST['catname']; 
			if($_POST['parentid']=='0') {
				$_POST['arrparentid'] = '0';
			}else{
				$data = $this->db->field('arrparentid,arrchildid,domain')->where(array('catid'=>$_POST['parentid']))->find();
				$_POST['arrparentid'] = $data["arrparentid"].','.$_POST['parentid'];
			}
			
			$_POST['siteid'] = self::$siteid;
			$catid = $this->db->insert($_POST, true);

			if($type != 2){   //非外部链接
			
				if($type == 1){   //单页类型
					$arr = array();
					$arr['catid'] = $catid;	
					$arr['title'] = $_POST['catname'];										
					$arr['description'] = $_POST['seo_description'];										
					$arr['updatetime'] = SYS_TIME;										
					D('page')->insert($arr, false, false); 
				}
			
				//根据系统设置生成URL
				$domain = isset($data['domain']) ? $data['domain'] : '';
				$_POST['pclink'] = isset($_POST['domain']) && !empty($_POST['domain']) ? $_POST['domain'] : $this->get_category_url($domain, $_POST['catdir']);
				
			}					
			
			$this->db->update(array('arrchildid' => $catid, 'pclink' => $_POST['pclink']), array('catid' => $catid));  //更新本类的子分类及更新URL
			if($_POST['parentid']!='0') $this->repairs($_POST['arrparentid']);
			if($_POST['domain']) $this->set_domain();
			$this->delcache();
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			$modelinfo = get_site_modelinfo();
			$parent_temp = $this->db->field('category_template,list_template,show_template,pclink')->where(array('catid'=>$catid))->find();
			$parent_dir = $parent_temp ? str_replace(SITE_URL, '', $parent_temp['pclink']) : '';
			
			if($type == 0){
				$default_model = $modelid ? get_model($modelid, false) : get_default_model();
				$category_temp = $this->select_template('category_temp', 'category_', $default_model);
				$list_temp = $this->select_template('list_temp', 'list_', $default_model);
				$show_temp = $this->select_template('show_temp', 'show_', $default_model);
				$tablename = $default_model ? $default_model['alias'] : '模型别名';
				include $this->admin_tpl('category_add');
			}else if($type == 1){
				$page_data = D('model')->field('modelid,alias')->where(array('type'=>2))->order('modelid ASC')->find();
				$alias = $page_data ? $page_data['alias'] : 'page';
				$category_temp = $this->select_template('category_temp', 'category_', $alias);
				$tablename = $alias;
				include $this->admin_tpl('category_page');
			}else{
				include $this->admin_tpl('category_link');
			}			
		}
		
	}



	/**
	 * 批量添加
	 */
	public function adds() {
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : get_default_model('modelid');
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		if(isset($_POST['dosubmit'])) { 
			$type = isset($_POST['type']) ? intval($_POST['type']) : 0;
			$catnames = explode("\r\n", $_POST['catnames']);	
			if($_POST['parentid']=='0') {
				$_POST['arrparentid'] = '0';
			}else{
				$data = $this->db->field('arrparentid,arrchildid,domain')->where(array('catid'=>$_POST['parentid']))->find();
				$_POST['arrparentid'] = $data["arrparentid"].','.$_POST['parentid'];
			}
			foreach ($catnames as $key => $val) {
				if(!$val) continue;
				if(strpos($val, '|')){
					list($_POST['catname'], $_POST['catdir']) = explode('|', $val);
				}
				$_POST['catname'] = trim($_POST['catname']);
				$_POST['catdir'] = trim($_POST['catdir']);

				$res = $this->db->field('catid')->where(array('siteid'=>self::$siteid, 'catdir'=>$_POST['catdir']))->one();
				if($res) continue;

				$_POST['mobname'] = $_POST['catname'];
				$_POST['siteid'] = self::$siteid;
				$catid = $this->db->insert($_POST, true);
				if($type == 1){   //单页类型
					$arr = array();
					$arr['catid'] = $catid;					
					$arr['title'] = $_POST['catname'];									
					$arr['updatetime'] = SYS_TIME;										
					D('page')->insert($arr, false, false); 
				}
				//根据系统设置生成URL
				$domain = isset($data['domain']) ? $data['domain'] : '';
				$_POST['pclink'] = $this->get_category_url($domain, $_POST['catdir']);;					

				$this->db->update(array('arrchildid' => $catid, 'pclink' => $_POST['pclink']), array('catid' => $catid));  //更新本类的子分类及更新URL
				if($_POST['parentid']!='0') $this->repairs($_POST['arrparentid']);
			}	

			$this->delcache();
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			$modelinfo = get_site_modelinfo();
			$default_model = get_default_model();
			$category_temp = $this->select_template('category_temp', 'category_', $default_model);
			$list_temp = $this->select_template('list_temp', 'list_', $default_model);
			$show_temp = $this->select_template('show_temp', 'show_', $default_model);
			$parent_temp = $this->db->field('category_template,list_template,show_template,pclink')->where(array('catid'=>$catid))->find();
			$parent_dir = $parent_temp ? str_replace(SITE_URL, '', $parent_temp['pclink']) : '';
			$tablename = $default_model ? $default_model['alias'] : '模型别名';
			include $this->admin_tpl('category_adds');			
		}
		
	}
	


	/**
	 * 编辑栏目
	 */
	public function edit() {				

		if(isset($_POST['dosubmit'])) {
			if($_POST['domain']) $this->set_domain();

			$catid = isset($_POST['catid']) ? strval(intval($_POST['catid'])) : 0;
			$_POST['catname'] = trim($_POST['catname']);
			$_POST['catdir'] = trim($_POST['catdir'], ' /');
			
			if($_POST['parentid']=='0') {
				$_POST['arrparentid'] = '0';
			}else{
				$data = $this->db->field('arrparentid,arrchildid,domain')->where(array('catid'=>$_POST['parentid']))->find();
				if(strpos($data['arrparentid'],$catid) !== false || $_POST['parentid']==$catid) return_json(array('status'=>0,'message'=>'不能将类别移动到自己或自己的子类别中！'));
				$_POST['arrparentid'] = $data["arrparentid"].','.$_POST['parentid'];
			}
			
			//如果有修改分类的动作
			if($_POST['arrparentid']!=$_POST['cpath']){
				$_POST['cpath'] = safe_replace($_POST['cpath']);
				$_POST['arrparentid'] = safe_replace($_POST['arrparentid']);
				$cpath = $_POST['cpath'].','.$catid;	//和现有的操作的分类id相连
				$this->db->query("UPDATE yzmcms_category SET arrparentid=REPLACE(arrparentid, '{$_POST['cpath']}','{$_POST['arrparentid']}') WHERE arrparentid like '{$cpath}%'"); 
			}
			
			// 根据系统设置生成URL
			if($_POST['type'] < 2){
				$domain = isset($data['domain']) ? $data['domain'] : '';
				$_POST['pclink'] = isset($_POST['domain']) && !empty($_POST['domain']) ? $_POST['domain'] : $this->get_category_url($domain,$_POST['catdir']);
			}

			if($this->db->update($_POST, array('catid' => $catid), true)){		
				if($_POST['arrparentid']!=$_POST['cpath']) $this->repairs($_POST['arrparentid'], $_POST['cpath']);
				if($_POST['domain']) $this->set_domain();
				$this->delcache();
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$type = isset($_GET['type']) ? intval($_GET['type']) : 0;
			$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
			$data = $this->db->where(array('catid' => $catid))->find();
			if(!$data) showmsg('栏目不存在！', 'stop');
			
			$modelinfo = get_site_modelinfo();
			$parent_temp = $this->db->field('category_template,list_template,show_template,pclink')->where(array('catid'=>$data['parentid']))->find();
			$parent_dir = $parent_temp ? str_replace(SITE_URL, '', $parent_temp['pclink']) : '';
			
			if($type == 0){
				$default_model = get_model($data['modelid'], false);
				$category_temp = $this->select_template('category_temp', 'category_', $default_model);
				$list_temp = $this->select_template('list_temp', 'list_', $default_model);
				$show_temp = $this->select_template('show_temp', 'show_', $default_model);
				$tablename = $default_model ? $default_model['alias'] : '模型别名';
				include $this->admin_tpl('category_edit');
			}else if($type == 1){
				$page_data = D('model')->field('modelid,alias')->where(array('type'=>2))->order('modelid ASC')->find();
				$alias = $page_data ? $page_data['alias'] : 'page';
				$category_temp = $this->select_template('category_temp', 'category_', $alias);
				$tablename = $alias;
				include $this->admin_tpl('category_page_edit');
			}else{
				include $this->admin_tpl('category_link_edit');
			}			
		}
		
	}


	/**
	 * 单页内容
	 */
	public function page_content() {
		yzm_base::load_sys_class('form','',0);
		yzm_base::load_common('lib/page_form'.EXT, 'admin');
		$page_form = new page_form();

		if(is_post()) {
			$catid = isset($_POST['catid']) ? intval($_POST['catid']) : showmsg(L('illegal_operation'), 'stop');
			$notfilter_field = $page_form->get_notfilter_field();
			foreach($_POST as $_k=>$_v) {
				if(!in_array($_k, $notfilter_field)) {
					$_POST[$_k] = !is_array($_POST[$_k]) ? new_html_special_chars($_v) : $page_form->content_dispose($_v);
				}
			}
			
			$_POST['updatetime'] = SYS_TIME;
			if(D('page')->update($_POST, array('catid' => $catid))){		
				showmsg(L('operation_success'), U('init'), 1);
			}else{
				showmsg(L('operation_failure'));
			}

		}
		
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : showmsg(L('illegal_operation'), 'stop');
		$data = D('page')->where(array('catid' => $catid))->find();
		
		$string = $page_form->content_edit($data);
		include $this->admin_tpl('page_content');
	}


	/**
	 * 获取栏目模板
	 */
	public function public_category_template(){
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
		$default_model = $modelid ? get_model($modelid, 'alias') : 'page';
		$data = array(
			'category_template' => $this->select_template('category_temp', 'category_', $default_model),
			'list_template' => $this->select_template('list_temp', 'list_', $default_model),
			'show_template' => $this->select_template('show_temp', 'show_', $default_model),
			'tablename' => $default_model
		);
		return_json($data);
	}


	/**
	 * ajax修改字段
	 */
	public function public_change_status() {
		if(is_post()){
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$field = isset($_POST['field']) ? trim($_POST['field']) : '';
			$value = isset($_POST['value']) ? intval($_POST['value']) : 0;

			if(!in_array($field, array('display','member_publish'))) return_json(array('status'=>0,'message'=>L('illegal_parameters')));
			if($this->db->update(array($field => $value), array('catid' => $id))){
				$this->delcache();
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}
	}
	

	/**
	 * 模板选择
	 * 
	 * @param $style  风格
	 * @param $pre 模板前缀
	 * @param $model 模型
	 */
	private function select_template($style, $pre='', $model=null) {
			if(!$model) return array();
			$site_theme = self::$siteid ? get_site(self::$siteid, 'site_theme') : C('site_theme');
			$tablename = is_array($model) ? $model['alias'] : $model;
			$pre = $model ? $pre.$tablename : $pre;
			$files = glob(APP_PATH.'index'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$site_theme.DIRECTORY_SEPARATOR.$pre.'*.html');
			$files = @array_map('basename', $files);
			$templates = array();
			$tem_style = APP_PATH.'index'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.$site_theme.DIRECTORY_SEPARATOR.'config.php';
			$templets_style = is_file($tem_style) ? require($tem_style) : array();	
			$templets_style = $templets_style ? $templets_style[$style] : $templets_style;	
			if(is_array($files)) {
				foreach($files as $file) {
					$key = substr($file, 0, -5);
					$templates[$key] = isset($templets_style[$key]) ? $templets_style[$key] : $file;
				}
			}

			return $templates;
			
	}
	
	
	/**
	 * 批量修复栏目数据
	 */
	private function repairs($arrparentid, $cpath=null) {  
		$data1 = explode(',', $arrparentid);
		$data2 = $cpath ? explode(',', $cpath) : array();
		$data = array_merge($data1, $data2);
		foreach($data as $val){
			if($val) $this->repair($val);
		}
	}
	
	
	/**
	 * 修复栏目数据
	 * @param $catid 栏目ID
	 */
	private function repair($catid) {
		$this->db->update(array('arrchildid' => $this->get_arrchildid($catid)), array('catid' => $catid));  //更新本类的子分类
	}
	

	/**
	 * 获取子栏目ID列表
	 * @param $catid 栏目ID
	 */
	private function get_arrchildid($catid) {
		$arrchildid = $catid;
		$data = $this->db->field('catid')->where("FIND_IN_SET('$catid',arrparentid)")->order('catid ASC')->select();
		foreach($data as $val) {
			$arrchildid .= ','.$val['catid'];
		}
		return $arrchildid;
	}


	/**
	 * 获取栏目URL 
	 * @param $domain 
	 * @param $catid 
	 * @param $id 
	 */
	private function get_category_url($domain,$catdir){
		$url_mode = get_config('url_mode');
		if($url_mode==1 || $url_mode==3){
			return $domain ? $domain.$catdir.'/' : get_site_url().$catdir.'/';
		}
		return SITE_PATH.$catdir.'/';
	}	


	/**
	 * 清除栏目缓存
	 */
	private function delcache() {
		$site_mapping = self::$siteid ? 'site_mapping_site_'.self::$siteid : 'site_mapping_index_'.self::$siteid;
		delcache('categoryinfo');
		delcache('categoryinfo_siteid_'.self::$siteid);
		delcache($site_mapping);
	}


	/**
	 * 设置栏目绑定域名
	 */
	private function set_domain() {

		// 当你看到这里的时候，你一定想绑定域名，免费版已经把该功能阉割了，开发不易，请购买授权支持，联系QQ：214243830！
		return_json(array('status'=>0,'message'=>'当前版本不支持栏目绑定域名，请购买授权版本！'));	
	}
	
}