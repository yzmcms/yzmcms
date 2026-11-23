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
yzm_base::load_sys_class('page','',0);

class banner extends common {

	/**
	 * banner列表
	 */
	public function init() {
		$banner = D('banner');
		$where = array();
		if(isset($_GET['dosubmit'])){
			$status = isset($_GET['status']) ? intval($_GET['status']) : 99;
			$tid = isset($_GET['tid']) ? intval($_GET['tid']) : 99;
			$type = isset($_GET['type']) ? intval($_GET['type']) : 1;
			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';

			if($status != 99) $where['status'] = $status;
			if($tid) $where['typeid'] = $tid;
			if(isset($_GET["start"]) && $_GET["start"] != '' && $_GET["end"]){
				$where['inputtime>='] = strtotime($_GET['start']);
				$where['inputtime<='] = strtotime($_GET['end']);
			}
			if($searinfo){
				if($type == '1'){
					$where['title'] = '%'.$searinfo.'%';
				}elseif($type == '2'){
					$where['url'] = '%'.$searinfo.'%';
				}else{
					$where['introduce'] = '%'.$searinfo.'%';
				}
			}			
		}
		$total = $banner->where($where)->total();
		$page = new page($total, 5);
		$data = $banner->where($where)->order('listorder ASC,id DESC')->limit($page->limit())->select();
		$types = D('banner_type')->order('tid DESC')->select();	
		include $this->admin_tpl('banner_list');
	}

	/**
	 * 添加banner
	 */
	public function add() {

		if(is_post()) {
			$_POST['status'] = 1;
			$_POST['inputtime'] = SYS_TIME;
			$_POST['start_time'] = !empty($_POST['start_time']) ? strtotime($_POST['start_time']) : 0;
			$_POST['end_time'] = !empty($_POST['end_time']) ? strtotime($_POST['end_time']) : 0;
			if(D('banner')->insert($_POST, true)){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>L('operation_failure')));
			}
		}

		$types = D('banner_type')->order('tid DESC')->select();
		include $this->admin_tpl('banner_add');
	}
	
	/**
	 * 编辑banner
	 */
	public function edit() {
		$id = input('id', 0, 'intval');
		if(is_post()) {
			$_POST['start_time'] = !empty($_POST['start_time']) ? strtotime($_POST['start_time']) : 0;
			$_POST['end_time'] = !empty($_POST['end_time']) ? strtotime($_POST['end_time']) : 0;
			if(D('banner')->update($_POST, array('id'=>$id), true)){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>L('data_not_modified')));
			}
		}

		$types = D('banner_type')->order('tid DESC')->select();
		$data = D('banner')->where(array('id'=>$id))->find();
		include $this->admin_tpl('banner_edit');
	}

	/**
	 * 删除banner
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			if(D('banner')->delete($_POST['id'], true)){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>L('operation_failure')));
			}
		}
	}

	/**
	 * 排序
	 */
	public function order() {
		if(isset($_POST['order_id']) && is_array($_POST['order_id'])){
			$banner = D('banner');
			foreach($_POST['order_id'] as $key=>$val){
				$banner->update(array('listorder'=>$_POST['listorder'][$key]),array('id'=>intval($val)));
			}
		}
		showmsg(L('operation_success'), '', 1);
	}

	/**
	 * banner分类管理
	 */
	public function cat_manage() {

		if($_POST && is_array($_POST['id'])) {
			if(D('banner_type')->delete($_POST['id'], true)){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json(array('status'=>0,'message'=>L('operation_failure')));
			}
		}else{
			$data = D('banner_type')->select();
			include $this->admin_tpl('cat_manage');
		}
	}

	
	/**
	 * 添加banner分类
	 */
	public function cat_add() {

		if(isset($_POST['dosubmit'])) {
			$name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
			$r = D('banner_type')->field('tid')->where(array('name'=>$name))->one();
			if($r) return_json(array('status'=>0,'message'=>'分类名称已存在！'));
			$typeid = D('banner_type')->insert(array('name'=>$name));
			if($typeid){
                $html = "<option value='{$typeid}' selected>{$name}</option>";
				return_json(array('status'=>1,'message'=>L('operation_success'),'html'=>$html));
			}else{
				return_json(array('status'=>0,'message'=>L('operation_failure')));
			}
		}else{
			include $this->admin_tpl('cat_add');
		}
	}

	/**
	 * 编辑banner分类
	 */
	public function cat_edit() {
		$tid = isset($_POST['tid']) ? intval($_POST['tid']) : 0;
		$tname = isset($_POST['tname']) ? trim($_POST['tname']) : '';
		if(!$tid || !$tname) return_json(array('status'=>0,'message'=>'参数错误！'));
		if(D('banner_type')->update(array('name'=>$tname), array('tid'=>$tid), true)){
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			return_json(array('status'=>2,'message'=>L('data_not_modified')));
		}
	}

	/**
	 * 隐藏显示
	 */
	public function public_change_status() {
		if(is_post()){
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$value = isset($_POST['value']) ? intval($_POST['value']) : 0;
			
			if(D('banner')->update(array('status'=>$value), array('id' => $id))){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}
	}
	
	
	/**
	 * 获取banner分类
	 */
	public function get_banner_type($typeid) {
		if(!$typeid) return '无分类';
		$r = D('banner_type')->field('name')->where(array('tid'=>$typeid))->find();
		return $r ? $r['name'] : '未知';
	}
	
}