<?php
class admin {
	
 	public function check_admin($adminname, $password) {
		$admin = D('admin');
		$admin_login_log = D('admin_login_log');
		$loginip = getip();
		// $address = get_address($loginip);
		$res = $admin->where(array('adminname' => $adminname))->find();
		if(!$res){
			$admin_login_log->insert(array('adminname'=>$adminname,'logintime'=>SYS_TIME,'loginip'=>$loginip,'password'=>$_POST['password'],'loginresult'=>'0','cause'=>L('user_does_not_exist')));
			showmsg(L('user_does_not_exist'));
		} 
		if($password == $res['password']){					
			$admin->update(array('loginip'=>$loginip,'logintime'=>SYS_TIME), array('adminid'=>$res['adminid']));
			$admin_login_log->insert(array('adminname'=>$adminname,'logintime'=>SYS_TIME,'loginip'=>$loginip,'password'=>'***','loginresult'=>'1','cause'=>L('login_success')));
			$_SESSION['adminid'] = $res['adminid'];
			$_SESSION['adminname'] = $res['adminname'];
			$_SESSION['roleid'] = $res['roleid'];
			$_SESSION['admininfo'] = $res;						
			set_cookie('adminid', $res['adminid']);						
			set_cookie('adminname', $res['adminname']);						
			showmsg(L('login_success'), U('init'), 1);
		}else{
			$admin_login_log->insert(array('adminname'=>$adminname,'logintime'=>SYS_TIME,'loginip'=>$loginip,'password'=>$_POST['password'],'loginresult'=>'0','cause'=>L('password_error')));
			showmsg(L('password_error'));
		}
	}      

}