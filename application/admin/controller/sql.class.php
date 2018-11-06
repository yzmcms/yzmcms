<?php
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

				if(stristr($sql, 'outfile')){
					$str = '<span class="c-red">ERROR : 检测到非法字符 “outfile”！</span>';
					break;
				}
				if(stristr($sql, '.php')){
					$str = '<span class="c-red">ERROR : 检测到非法字符 “.php” ！</span>';
					break;
				}
				if(stristr($sql, 'concat')){
					$str = '<span class="c-red">ERROR : 检测到非法字符 “concat” ！</span>';
					break;
				}
				if(preg_match("/^drop(.*)database/i", $sql)){
					$str = '<span class="c-red">ERROR : 不允许删除数据库！</span>';
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
}