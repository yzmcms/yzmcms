<?php
/**
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2020-08-19
 *
 * 功能说明：此功能主要为开发人员预留功能
 * 			需手动修改配置文件（config.php）才可使用本功能！
 */
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);

class sql extends common {

	/**
	 * SQL命令行
	 */
	public function init() {		
		include $this->admin_tpl('sql');
	}


	/**
	 * 执行SQL命令
	 */
	public function do_sql() {
		if(isset($_POST['sqlstr'])){
			if(!C('sql_execute')) showmsg('根据系统配置，不允许在线执行SQL命令！', 'stop');
			$sqlstr = MAGIC_QUOTES_GPC ? stripslashes($_POST['sqlstr']) : $_POST['sqlstr'];
			$sqlstr = rtrim(trim($sqlstr), ';');
			$sqls = $_POST['action']=='many' ? explode(';', $sqlstr) : array(0 => $sqlstr);

			$admin = D('admin');
			foreach($sqls as $sql){
				$sql = trim($sql);
				if(empty($sql)) continue;

				$res = $this->_blacklist($sql);
				if(!$res['status']){
					$str = $res['message'];
					break;
				}

				$result = $admin->query($sql); 
				if($result){
					$str = '<span style="color:green">OK : 执行成功！</span>';
					if(!preg_match("/^(?:UPDATE|DELETE|TRUNCATE|ALTER|DROP|FLUSH|INSERT|REPLACE|SET|CREATE)\\s+/i", $sql)){
						$data = $admin->fetch_all($result);
					}					
				}else{
					$str = '<span class="c-red">ERROR : 执行失败！</span>';
					break;
				}				
			}
		}

		include $this->admin_tpl('sql');
	}


	/**
	 * 关键字黑名单
	 */
	private function _blacklist($sql){
		$arr = array(
			'general_log',
			'outfile',
			'dumpfile',
			'concat',
			'replace',
			'.php',
		);
		$status = true;
		$message = '';
		foreach ($arr as $val) {
			if(stripos($sql, $val) !== false){
				$status = false;
				$message = '<span class="c-red">ERROR : 检测到非法字符 “'.$val.'” ！</span>';
				break;
			}
		}

		if($status && preg_match("/^drop(.*)database/i", $sql)){
			$status = false;
			$message = '<span class="c-red">ERROR : 不允许删除数据库！</span>';
		}

		return array('status'=>$status, 'message'=>$message);
	}	
}