<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class role extends common {

	/**
	 * 角色管理列表
	 */
	public function init() {
		$data = D('admin_role')->order('roleid ASC')->select();
		include $this->admin_tpl('role_list');
	}
	
	
	/**
	 * 删除角色
	 */
	public function delete() {
		$roleid = intval($_GET['roleid']);
		if(in_array($roleid, array(1, 2, 3))) showmsg('不能删除系统角色！');
		if(D('admin')->where(array('roleid' => $roleid))->total() > 0){
			showmsg('请先删除该角色下的管理员！');
		}else{
			D('admin_role')->delete(array('roleid'=>$roleid));
		}	
		showmsg(L('operation_success'));
	}
	
	
	
	/**
	 * 添加角色
	 */
	public function add() {		
		$admin_role = D('admin_role');
		if(isset($_POST['dosubmit'])) { 
			if(!$_POST['rolename']) return_json(array('status'=>0,'message'=>'角色名称不能为空！'));
			$res = $admin_role->where(array('rolename' => $_POST['rolename']))->find();
			if($res) return_json(array('status'=>0,'message'=>'角色名称已存在！'));			
			$_POST['system'] = 0;	
			$admin_role->insert($_POST, true);
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{			
			include $this->admin_tpl('role_add');
		}
		
	}
	
	
	
	/**
	 * 编辑角色
	 */
	public function edit() {				
		$admin_role = D('admin_role');
		if(isset($_POST['dosubmit'])) {
			$roleid = isset($_POST['roleid']) ? intval($_POST['roleid']) : 0;
			unset($_POST["system"]);
		
			if($admin_role->update($_POST, array('roleid' => $roleid))){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$roleid = isset($_GET['roleid']) ? intval($_GET['roleid']) : 0;
			if($roleid == 1) showmsg('超级管理员信息不允许修改！');
			$data = $admin_role->where(array('roleid' => $roleid))->find();
			include $this->admin_tpl('role_edit');
		}
		
	}	
	
	
	/**
	 * 权限管理
	 */
	public function role_priv() {				
		$admin_role = D('admin_role');
		if(isset($_POST['dosubmit'])) {
			$admin_role_priv = D('admin_role_priv');
			if (is_array($_POST['menuid']) && count($_POST['menuid']) > 0) {
				
				$admin_role_priv->delete(array('roleid'=>$_POST['roleid']));
				$menuinfo = D('menu')->field('`id`,`m`,`c`,`a`,`data`')->select();
				foreach ($menuinfo as $_v) $menu_info[$_v['id']] = $_v;
				foreach($_POST['menuid'] as $menuid){
					$info = array();
					$info = $menu_info[$menuid];
					if($info['m'] == '') continue;
					$info['roleid'] = $_POST['roleid'];
					$admin_role_priv->insert($info);
				}
			} else {
				$admin_role_priv->delete(array('roleid'=>$_POST['roleid']));
			}
			
			delcache('menu_string_'.$_POST['roleid']);
			showmsg(L('operation_success'));
			
		}else{
			$roleid = isset($_GET['roleid']) ? intval($_GET['roleid']) : 0;
			if($roleid == 1) showmsg('超级管理员权限不允许修改！');
		
			$tree = yzm_base::load_sys_class('tree');
			$tree->icon = array('│ ','├─ ','└─ ');
			$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
			$data = D('menu')->order('listorder ASC,id DESC')->select();
			$priv_data = D('admin_role_priv')->where(array('roleid'=>$roleid))->select();
			foreach($data as $k=>$v) {
				$data[$k]['level'] = $this->get_level($v['id'],$data);
				$data[$k]['checked'] = ($this->is_checked($v,$roleid,$priv_data))? ' checked' : '';
			}		
			
			$str  = "<tr>
						<td><label>\$spacer<input type='checkbox' name='menuid[]' value='\$id' level='\$level' \$checked onclick='javascript:checknode(this);'> \$name</label></td>
					</tr>";
			$tree->init($data);
			$menus = $tree->get_tree(0, $str);
			include $this->admin_tpl('role_priv');		
		}
		
	}
	
	
	/**
	 * 获取菜单深度
	 * @param $id
	 * @param $array
	 * @param $i
	 */
	private function get_level($id, $array=array(), $i=0) {
		foreach($array as $n=>$value){
			if($value['id'] == $id){
				if($value['parentid']== '0') return $i;
				$i++;
				return $this->get_level($value['parentid'],$array,$i);
			}
		}
	}
	
	
	/**
	 *  检查指定菜单是否有权限
	 * @param array $data menu表中数组
	 * @param int $roleid 需要检查的角色ID
	 */
	private function is_checked($data,$roleid,$priv_data) {
		$priv_arr = array('m','c','a','data');
		if($data['m'] == '') return false;
		foreach($data as $key=>$value){
			if(!in_array($key,$priv_arr)) unset($data[$key]);
		}
		$data['roleid'] = $roleid;
		return in_array($data, $priv_data) ? true : false;
	}
}