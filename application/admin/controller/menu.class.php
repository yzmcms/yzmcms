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

class menu extends common {

	/**
	 * 菜单列表
	 */
	public function init() {
		$tree = yzm_base::load_sys_class('tree');
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$data = D('menu')->order('listorder ASC,id ASC')->limit(1000)->select();

		$array = array();
		foreach($data as $v) {
			$v['style'] = $v['parentid'] ? 'child' : 'top';
			$v['parentoff'] = $v['parentid'] ? '' : '<i class="yzm-iconfont parentid" action="2">&#xe653;</i> ';
			$v['string'] = '<a title="增加子类" href="javascript:;" onclick="yzm_open(\'增加菜单\',\''.U('add',array('parentid'=>$v['id'])).'\',600,410)" style="text-decoration:none"  class="btn-mini btn-success ml-5">增加子类</a> <a title="编辑菜单" href="javascript:;" onclick="yzm_open(\'编辑菜单\',\''.U('edit',array('id'=>$v['id'])).'\',600,410)" style="text-decoration:none"  class="btn-mini btn-primary ml-5">编辑</a> <a title="删除" href="javascript:;" onclick="yzm_confirm(\''.U('delete',array('id'=>$v['id'])).'\', \'确定要删除【'.$v['name'].'】吗？\')" style="text-decoration:none"  class="btn-mini btn-danger ml-5">删除</a>';
			$v['display'] = $v['display'] ? '<span class="yzm-status-enable" data-field="status" data-id="'.$v['id'].'" onclick="yzm_change_status(this,\''.U('public_change_status').'\')"><i class="yzm-iconfont">&#xe81f;</i>是</span>' : '<span class="yzm-status-disable" data-field="status" data-id="'.$v['id'].'" onclick="yzm_change_status(this,\''.U('public_change_status').'\')"><i class="yzm-iconfont">&#xe601;</i>否</span>';
			$array[] = $v;
		}	
		$str  = "<tr class='text-c \$style'>
					<td><input name='listorders[\$id]' type='text' value='\$listorder' class='input-text listorder'></td>
					<td>\$id</td>
					<td class='text-l'>\$parentoff\$spacer\$name</td>
					<td class='td-manage'>\$display</td>
					<td>\$string</td>
				</tr>";
		$tree->init($array);
		$menus = $tree->get_tree(0, $str);
		include $this->admin_tpl('menu_list');
	}
	
	
	/**
	 * 删除菜单
	 */
	public function delete() {
		$id = intval($_GET['id']);
		D('menu')->delete(array('id'=>$id));
		D('menu')->delete(array('parentid'=>$id));
		delcache('menu_string_1');
		showmsg(L('operation_success'), '', 1);
	}
	
	
	/**
	 * 添加菜单
	 */
	public function add() {		
		$menu = D('menu');
		if(isset($_POST['dosubmit'])) { 	
			$menu->insert($_POST, true);
			delcache('menu_string_1');
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			$parentid = isset($_GET['parentid']) ? intval($_GET['parentid']) : 0;
			$tree = yzm_base::load_sys_class('tree');
			$data = D('menu')->order('listorder ASC,id ASC')->limit(1000)->select();
			$array = array();
			foreach($data as $v) {
				$v['selected'] = $v['id'] == $parentid ? 'selected' : '';
				$array[] = $v;
			}
			$str  = "<option value='\$id' \$selected>\$spacer \$name</option>";
			$tree->init($array);
			$select_menus = $tree->get_tree(0, $str);	
			if($parentid) $parent = D('menu')->where(array('id'=>$parentid))->find();
			include $this->admin_tpl('menu_add');
		}
		
	}
	
	
	/**
	 * 编辑菜单
	 */
	public function edit() {				
		$menu = D('menu');
		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
		
			if($menu->update($_POST, array('id' => $id), true)){
				delcache('menu_string_1');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$r = $menu->where(array('id' => $id))->find();
			if($r) extract($r);
			$tree = yzm_base::load_sys_class('tree');
			$r = $menu->order('listorder ASC,id ASC')->select();
			$array = array();
			foreach($r as $v) {
				$v['selected'] = $v['id'] == $parentid ? 'selected' : '';
				$array[] = $v;
			}
			$str  = "<option value='\$id' \$selected>\$spacer \$name</option>";
			$tree->init($array);
			$select_menus = $tree->get_tree(0, $str);
			include $this->admin_tpl('menu_edit');
		}
		
	}


	/**
	 * 菜单排序
	 */
	public function order() {
		if(isset($_POST['listorders']) && is_array($_POST['listorders'])){
			$menu = D('menu');
			foreach($_POST['listorders'] as $id => $listorder) {
				$menu->update(array('listorder'=>$listorder),array('id'=>$id));
			}
			delcache('menu_string_1');
		}
		showmsg(L('operation_success'), '' ,1);
	}	


	/**
	 * 菜单隐藏显示
	 */
	public function public_change_status() {
		if(is_post()){
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$value = isset($_POST['value']) ? intval($_POST['value']) : 0;
			
			if(D('menu')->update(array('display'=>$value), array('id' => $id))){
				delcache('menu_string_1');
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
		}
	}
	
}