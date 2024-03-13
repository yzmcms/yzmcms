<?php
/**
 * db_mysql.class.php	 MYSQL数据库类
 *  
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2024-03-10
 */
	
class db_mysql{
	
	private static $link = null;       		 //数据库连接资源句柄
	private static $db_link = array();  	 //数据库连接资源池
	private $config = array();  	  		 //数据库配置信息
	private $tablename;                      //数据库表名,不包含表前缀
	private $key = array();           		 //存放条件语句
	private $lastsql = '';            		 //存放sql语句
		
		
	/**
	 * 初始化链接数据库
	 */
	public function __construct($config, $tablename){
		$this->config = $config;
		$this->tablename = $tablename;

		if(is_null(self::$link)) $this->db(0, $config);	
	}
	

	/**
	 * 真正开启数据库连接
	 * 			
	 * @return resource mysql
	 */	
	public function connect(){ 
		self::$link = @mysql_connect($this->config['db_host'] .($this->config['db_port'] ? ':'.intval($this->config['db_port']) : ''), $this->config['db_user'], $this->config['db_pwd']);  	
		if(self::$link == false) {
			$mysql_error = APP_DEBUG ? mysql_error() : 'Can not connect to MySQL server!';
			application::halt($mysql_error, 550);
		}        
		$db = mysql_select_db($this->config['db_name'], self::$link);            	         	
		if($db == false)  {
			$mysql_error = APP_DEBUG ? mysql_error() : 'Database selection failed!';
			application::halt($mysql_error, 550);    
		}                                      
		mysql_query("SET names utf8, sql_mode=''"); 	
		return self::$link;				
	}	
	

	/**
	 * 切换当前的数据库连接
	 *
	 * @param $linknum 	数据库编号	
	 * @param $config 	array	
	 * @参数说明		array('db_host'=>'127.0.0.1', 'db_user'=>'root', 'db_pwd'=>'', 'db_name'=>'yzmcms', 'db_port'=>3306, 'db_prefix'=>'yzm_')
	 *					[服务器地址, 数据库用户名, 数据库密码, 数据库名, 服务器端口, 数据表前缀]
	 * 						
	 * 使用方法(添加一个编号为1的数据库连接，并自动切换到当前的数据库连接):  
	 * D('tablename')->db(1, array('db_host'=>'127.0.0.1', 'db_user'=>'root', 'db_pwd'=>'', 'db_name'=>'test', 'db_port'=>3306, 'db_prefix'=>'yzm_'))->select();
	 * 
	 * 当第二次切换到相同的数据库的时候，就不需要传入数据库连接信息了，可以直接使用：D('tablename')->db(1)->select();
	 * 如果需要切换到默认的数据库连接，只需要调用：D('tablename')->db(0)->select();
	 * @return object
	 */		
	public function db($linknum = 0, $config = array()){
		if(isset(self::$db_link[$linknum])){
			self::$link = self::$db_link[$linknum]['db'];
			$this->config = self::$db_link[$linknum]['config'];
		}else{
			if(empty($config)) $this->geterr('Database number to '.$linknum.' Not existent!'); 
			$this->config = $config;
			self::$db_link[$linknum]['db'] = self::$link = self::connect();
			self::$db_link[$linknum]['config'] = $config;
		}
		return $this;
	}
	
	

    /**
     * 获取当前的数据表
     * @return string
     */
    private function get_tablename() {
        $alias = isset($this->key['alias']) ? ' '.$this->key['alias'].' ' : '';
        return '`'.$this->config['db_name'].'` . `'.$this->config['db_prefix'].$this->tablename.'`' .$alias;
    }	
	
	
	/**
	 * 内部方法：过滤函数
	 * @param $value
	 * @param $chars
	 * @return string
	 */	
	private function safe_data($value, $chars = false){
		if(!MAGIC_QUOTES_GPC) $value = addslashes($value);
		if($chars) $value = htmlspecialchars($value);

		return $value;
	}
	
	
	/**
	 * 内部方法：过滤非表字段
	 * @param $arr
	 * @param $primary 是否过滤主键
	 * @return array
	 */
	private function filter_field($arr, $primary = true){		
		$fields = $this->get_fields();	
		foreach($arr as $k => $v){
			if(!in_array($k, $fields, true)) unset($arr[$k]);
		}
		if($primary){
			$p = $this->get_primary();
			if(isset($arr[$p])) unset($arr[$p]);
		}
		return $arr;
	}

	
	/**
	 * 内部方法：数据库查询执行方法
	 * @param $sql 要执行的sql语句
	 * @return object
	 */
	private function execute($sql) {
		try{
			$sql_start_time = microtime(true);
			$this->lastsql = $sql;
			$res = mysql_query($sql) or $this->geterr($sql);
			$this->key = array();
			APP_DEBUG && debug::addmsg($sql, 1, $sql_start_time);
			return $res;
		}catch (Exception $e){
			if (strpos($e->getMessage(), 'server has gone away') !== false) {
		        self::$db_link[0]['db'] = self::$link = self::connect();
		        return $this->execute($sql);
		    }
			$this->geterr('Execute SQL error, message : '.$e->getMessage(), $sql);
		}
	}	
	

	/**
	 * 组装where条件，将数组转换为SQL语句
	 * @param array $where  要生成的数组,参数可以为数组也可以为字符串，建议数组。
	 * return object
	 */
	public function where($arr = ''){
		if(empty($arr) || isset($this->key['wheres']))  return $this;	
		if(is_array($arr)) {
			$args = func_get_args();
			$str = '(';
			foreach ($args as $value){
				foreach($value as $kk => $vv){
					if(is_array($vv)) $vv = 'array';

					$vv = !is_null($vv) ? $this->safe_data($vv) : '';
					if(!strpos($kk,'>') && !strpos($kk,'<') && !strpos($kk,'=') && substr($vv, 0, 1) != '%' && substr($vv, -1) != '%'){   
						$str .= $kk." = '".$vv."' AND ";
					}else if(substr($vv, 0, 1) == '%' || substr($vv, -1) == '%'){
						$str .= $kk." LIKE '".$vv."' AND "; 
					}else{ 
						$str .= $kk."'".$vv."' AND ";   
					}
				}
				$str = rtrim($str,' AND ').')';
				$str .= ' OR (';
			}
			$str = rtrim($str,' OR (');
			$this->key['where'] = $str;
		}else{
			$this->key['where'] = str_replace('yzmcms_', $this->config['db_prefix'], $arr);	
		}
		return $this;
	}


	/**
	 * 组装where条件，where方法升级版
	 * 格式：$where['字段']  = array('表达式','字段条件','可选参数(函数名)');
	 * 简写：$where['字段']  = array('yzmcms') 等价 $where['字段']  = array('eq', 'yzmcms')
	 * @param array $where  要生成的数组,参数可以为数组也可以为字符串，建议数组。
	 * return object
	 */
	public function wheres($arr = ''){
		if(empty($arr))  return $this;
		if(is_array($arr)) {
			$args = func_get_args();
			$str = '(';
			foreach ($args as $value){
				foreach($value as $kk => $vv){
					if(!is_array($vv)) $this->geterr('The parameters of the wheres method must be array!'); 

					$exp_arr = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN','not in'=>'NOT IN','between'=>'BETWEEN','not between'=>'NOT BETWEEN','notbetween'=>'NOT BETWEEN');
					$fun_arr = array('intval','strval','trim','ltrim','rtrim','htmlspecialchars','addslashes','stripslashes','strip_tags','strtoupper','strtolower');
					
					$tmp_exp = isset($vv[0])&&!is_array($vv[0]) ? strtolower($vv[0]) : '';
					$exp = isset($exp_arr[$tmp_exp]) ? $exp_arr[$tmp_exp] : '';
					$rule = isset($vv[1]) ? $vv[1] : '';
					$fun = isset($vv[2]) ? $vv[2] : '';

					// 支持简写方式
					if(!$exp && !$rule) {
						$exp = '=';
						$rule = $tmp_exp;
					}

					if(!$exp) $this->geterr('The expression '.$vv[0].' does not exis!');
					if($fun && !in_array($fun, $fun_arr)) $this->geterr('The callback function '.$fun.' is disabled!'); 
					
					if(is_array($rule)) {
						if(strpos($exp, 'IN') === false && strpos($exp, 'BETWEEN') === false) $rule = array('array');
						if($fun) $rule = array_map($fun, $rule);
						$rule = strpos($exp, 'BETWEEN') === false ? "('".join("','", $rule)."')" : "'".join("' AND '", $rule)."'";
					}else{
						$rule = $fun ? "'".$fun($rule)."'" : "'".$this->safe_data($rule)."'";
					}
					$str .= $kk.' '.$exp.' '.$rule.' AND ';
				}
				$str = rtrim($str,' AND ').')';
				$str .= ' OR (';
			}
			$str = rtrim($str,' OR (');
			$this->key['where'] = $str;
		}else{
			$this->key['where'] = str_replace('yzmcms_', $this->config['db_prefix'], $arr);	
		}

		$this->key['wheres'] = true;
		return $this;
	}
	
		
	/**
	 * 内部方法：查询部分，开始组装SQL
	 * @param $name
	 * @param $value
	 * @return object
	 */
	public function __call($name, $value){
		if(in_array($name, array('alias','field','order','limit','group','having'))){
			if(isset($value[0])) $this->key[$name] = $value[0];
			return $this;
		}else{
			$this->geterr('Call to '.$name.' function not exist!'); 
		}
	}
	
	
	/**
	 * 执行添加记录操作
	 * @param $data         要增加的数据，参数为数组。数组key为字段值，数组值为数据取值
	 * @param $filter       如果为真值[1为真] 则开启实体转义
	 * @param $primary 		是否过滤主键
	 * @param $replace 		是否为replace
	 * @return int|boolean  成功：返回自动增长的ID，失败：false
	 */
	public function insert($data, $filter = false, $primary = true, $replace = false){
		if(!is_array($data)) {
		    $this->geterr('insert function First parameter Must be array!'); 
			return false;
		}
		$data = $this->filter_field($data, $primary); 
		$fields = $values = array();
		foreach ($data AS $key => $val){
			$fields[] = '`'. $key .'`';
			$values[] = "'" . $this->safe_data($val, $filter) . "'";
		}		
		
		if(empty($fields)) return false;
		$sql = ($replace ? 'REPLACE' : 'INSERT').' INTO '.$this->get_tablename().' ('. implode(', ', $fields) .') VALUES ('. implode(', ', $values) .')';
		$this->execute($sql);
		return mysql_insert_id();
	}


	/**
	 * 批量执行添加记录操作
	 * @param $data         要增加的数据，参数为二维数组
	 * @param $filter       如果为真值[1为真] 则开启实体转义
	 * @param $replace 		是否为replace
	 * @return int|boolean  成功：返回首个自动增长的ID，失败：false
	 */
	public function insert_all($datas, $filter = false, $replace = false){
		if(!is_array($datas) || !current($datas)) {
		    $this->geterr('insert all function First parameter Must be array!'); 
			return false;
		}
		$fields = array_keys(current($datas));
		$values = array();
		foreach ($datas as $data){
			$value = array();
			foreach ($data as $key => $val) {
				$value[] = "'" . $this->safe_data($val, $filter) . "'";
			}
			$values[] = '('.implode(',', $value).')';
		}		
		
		if(empty($fields)) return false;
		$sql = ($replace ? 'REPLACE' : 'INSERT').' INTO '.$this->get_tablename().' ('. implode(', ', $fields) .') VALUES '. implode(', ', $values);
		$this->execute($sql);
		return mysql_insert_id();
	}

	
	/**
	 * 执行删除记录操作
	 * @param $where 		参数为数组，删除数据条件,不允许为空。
	 * @param $many 		是否删除多个，多用在批量删除，取的主键在某个范围内，例如 $admin->delete(array(3,4,5), true);
	 *                      结果为： DELETE FROM `yzmcms_admin` WHERE id IN (3,4,5);
	 *
	 * @return int          返回影响行数
	 */
	public function delete($where = array(), $many = false){
		if(!isset($this->key['wheres'])){
			if(is_array($where) && !empty($where)){
				if(!$many){
					$this->where($where);   
				}else{
					$where = array_map('intval', $where);
					$sql = implode(', ', $where);
					$this->key['where'] = $this->get_primary().' IN ('.$sql.')';
				}			
			}else{
				$this->geterr('delete function First parameter Must be array Or cant be empty!'); 
				return false;
			}
		}	

		$sql = 'DELETE FROM '.$this->get_tablename().' WHERE '.$this->key['where'];
		$this->execute($sql);
		return mysql_affected_rows();
	}

	
	/**
	 * 执行更新记录操作
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='myname',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'php','password'=>'123456')]						
	 * @param $where 		更新数据时的条件,参数为数组类型或者字符串
	 * @param $filter 		第三个参数选填 如果为真值[1为真] 则开启实体转义
	 * @param $primary 		是否过滤主键
	 * @return int          返回影响行数
	 */		
	public function update($data, $where = array(), $filter = false, $primary = true){	
		if(!isset($this->key['wheres'])) $this->where($where);
		if(is_array($data)){
			$data = $this->filter_field($data, $primary);				
			$sets = array();
			foreach ($data AS $key => $val){
				$sets[] = '`'. $key .'` = \''. $this->safe_data($val, $filter) .'\'';
			}
			$value = implode(', ', $sets);				
		}else{
			$value = $data;		
		}	

		if(empty($value)) return false;
		$sql = 'UPDATE '.$this->get_tablename().' SET '.$value.' WHERE '.$this->key['where'];
		$this->execute($sql);
		return mysql_affected_rows();	
	}

	
	/**
	 * 获取查询多条结果，返回二维数组
	 * @return array
	 */	
	public function select(){
		$rs = array();		
		$field = isset($this->key['field']) ? str_replace('yzmcms_', $this->config['db_prefix'], $this->key['field']) : ' * ';
		$join = isset($this->key['join']) ? ' '.implode(' ', $this->key['join']) : '';
		$where = isset($this->key['where']) ? ' WHERE '.$this->key['where'] : '';
		$group = isset($this->key['group']) ? ' GROUP BY '.$this->key['group'] : '';
		$having = isset($this->key['having']) ? ' HAVING '.$this->key['having'] : '';
		$order = isset($this->key['order']) ? ' ORDER BY '.$this->key['order'] : '';
		$limit = isset($this->key['limit']) ? ' LIMIT '.$this->key['limit'] : '';				
		
		$sql = 'SELECT '.$field.' FROM '.$this->get_tablename().$join.$where.$group.$having.$order.$limit;
		$selectquery = $this->execute($sql);
		while($data = mysql_fetch_assoc($selectquery)){
	      $rs[] = $data;
	    }
	    return $rs;
	}
	
	
	/**
	 * 获取查询一条结果，返回一维数组
	 * @return array
	 */	
	public function find(){
		$field = isset($this->key['field']) ? str_replace('yzmcms_', $this->config['db_prefix'], $this->key['field']) : ' * ';
		$join = isset($this->key['join']) ? ' '.implode(' ', $this->key['join']) : '';
		$where = isset($this->key['where']) ? ' WHERE '.$this->key['where'] : '';
		$group = isset($this->key['group']) ? ' GROUP BY '.$this->key['group'] : '';
		$having = isset($this->key['having']) ? ' HAVING '.$this->key['having'] : '';
		$order = isset($this->key['order']) ? ' ORDER BY '.$this->key['order'] : '';
		$limit = ' LIMIT 1';		
		
		$sql = 'SELECT '.$field.' FROM '.$this->get_tablename().$join.$where.$group.$having.$order.$limit;
		$findquery = $this->execute($sql);
	    return mysql_fetch_assoc($findquery);
	}
	
	
	
	/**
	 * 获取查询一条结果的一个字段
	 * @return string
	 */	
	public function one(){
		$field = isset($this->key['field']) ? str_replace('yzmcms_', $this->config['db_prefix'], $this->key['field']) : ' * ';
		$join = isset($this->key['join']) ? ' '.implode(' ', $this->key['join']) : '';
		$where = isset($this->key['where']) ? ' WHERE '.$this->key['where'] : '';
		$group = isset($this->key['group']) ? ' GROUP BY '.$this->key['group'] : '';
		$having = isset($this->key['having']) ? ' HAVING '.$this->key['having'] : '';
		$order = isset($this->key['order']) ? ' ORDER BY '.$this->key['order'] : '';
		$limit = ' LIMIT 1';		
		
		$sql = 'SELECT '.$field.' FROM '.$this->get_tablename().$join.$where.$group.$having.$order.$limit;
		$findquery = $this->execute($sql);
	    $data = mysql_fetch_row($findquery);
	    return $data&&$data[0] ? $data[0] : '';
	}	
	
	
	/**
	 * 连接查询
	 * @param $join 	string SQL语句，如yzmcms_admin ON yzmcms_admintype.id=yzmcms_admin.id
	 * @param $type 	可选参数,默认是inner
	 * @return object
	 */	
	public function join($join, $type = 'INNER'){
		$join = str_replace('yzmcms_', $this->config['db_prefix'], $join);    
        $this->key['join'][] = stripos($join,'JOIN') !== false ? $join : $type.' JOIN '.$join;
	    return $this;
	}		
	
	
	/**
	 * 用于调试程序，输入SQL语句
	 * @param $echo 	可选参数,默认是输出
	 * @return string
	 */	
	public function lastsql($echo = true){
		if(!$echo) {
			return $this->lastsql;
		}
		echo '<div style="font-size:14px;text-align:left;border:1px solid #9cc9e0;line-height:25px;padding:5px 10px;color:#000;font-family:Arial,Helvetica,sans-serif;"><p><b>SQL：</b>'.$this->lastsql.'</p></div>';		
	}

	
	/**
	 * 自定义SQL语句
	 * @param  $sql sql语句
	 * @param  $fetch_all 查询时是否返回二维数组
	 * @return mixed
	 */		
	public function query($sql = '', $fetch_all = true){
		$sql = str_replace('yzmcms_', $this->config['db_prefix'], $sql);
		if(preg_match("/^(?:UPDATE|DELETE|TRUNCATE|ALTER|DROP|FLUSH|INSERT|REPLACE|SET|CREATE)\\s+/i", $sql)){
			return $this->execute($sql);	 
		} 
		return $fetch_all ? $this->fetch_all($this->execute($sql)) : $this->fetch_array($this->execute($sql));
	}


	/**
	 * 返回一维数组，与query方法结合使用
	 * @param  resource
	 * @return array
	 */		
    public function fetch_array($query, $result_type = MYSQL_ASSOC) {
    	if(!is_resource($query))   return $query;
		return mysql_fetch_array($query, $result_type);
	}	

	
	/**
	 * 返回二维数组，与query方法结合使用
	 * @param  resource
	 * @return array
	 */		
    public function fetch_all($query, $result_type = MYSQL_ASSOC) {
    	if(!is_resource($query))   return $query;
		$arr = array();
		while($data = mysql_fetch_array($query, $result_type)) {
			$arr[] = $data;
		}
		return $arr;
	}
	
	
	/**
	 * 获取错误提示
	 */		
	private function geterr($msg, $sql=''){
		if(PHP_SAPI == 'cli'){
			throw new Exception('MySQL Error: '.mysql_error().' | '.$msg);
		}
		
		if(APP_DEBUG){
			if(is_ajax()) return_json(array('status'=>0, 'message'=>'MySQL Error: '.mysql_error().' | '.$msg));
			application::fatalerror($msg, mysql_error(), 2);	
		}else{
			write_error_log(array('MySQL Error', mysql_errno(), mysql_error(), $msg));
			if(is_ajax()) return_json(array('status'=>0, 'message'=>'MySQL Error!'));
			application::halt('MySQL Error!', 500);
		}
	}
	
	
	/**
	 * 返回记录行数。
	 * @return int 
	 */	
	public function total(){
		$join = isset($this->key['join']) ? ' '.implode(' ', $this->key['join']) : '';
		$where = isset($this->key['where']) ? ' WHERE '.$this->key['where'] : '';		
		$sql = 'SELECT COUNT(*) AS total FROM '.$this->get_tablename().$join.$where;
		$totquery = $this->execute($sql);
		$total = mysql_fetch_assoc($totquery);   
        return $total['total'];		
	}


    /**
     * 启动事务
     * @return boolean
     */
    public function start_transaction() {
		
        $this->execute('start transaction');
        return $this->execute('SET AUTOCOMMIT=0');
    }

	
    /**
     * 提交事务
     * @return boolean
     */
    public function commit() {
        
		$this->execute('commit');
		return $this->execute('SET AUTOCOMMIT=1');
    }

	
    /**
     * 事务回滚
     * @return boolean
     */
    public function rollback() {
		
		$this->execute('rollback');
		return $this->execute('SET AUTOCOMMIT=1');
    }
	
	
	
	/**
	 * 获取数据表主键
	 * @param $table 		数据表 可选
	 * @return array
	 */
	public function get_primary($table = '') {
		$table = empty($table) ? $this->get_tablename() : $table;
		$sql = "SHOW COLUMNS FROM $table";
		$r = mysql_query($sql) or $this->geterr($sql);
		while($data = mysql_fetch_assoc($r)){
			if($data['Key'] == 'PRI') break;
		}
		return $data['Field'];
	}
	

	/**
	 * 获取数据库 所有表
	 * @return array 
	 */		
	public function list_tables() {
		$tables = array();
		$listqeury = $this->execute('SHOW TABLES');
		while($r = $this->fetch_array($listqeury, MYSQL_NUM)) {
			$tables[] = $r[0];
		}
		return $tables;
	}	


	/**
	 * 获取表字段
	 * @param $table 		数据表 可选
	 * @return array
	 */
	public function get_fields($table = '') {
		$table = empty($table) ? $this->get_tablename() : $table;
		$fields = array();
		$sql = "SHOW COLUMNS FROM $table";
		$r = mysql_query($sql) or $this->geterr($sql);
		while($data = mysql_fetch_assoc($r)){
			$fields[] = $data['Field'];
		}		
		return $fields;
	}

	
	/**
	 * 检查表是否存在
	 * @param $table 表名
	 * @return boolean
	 */
	public function table_exists($table) {
		$table = C('db_prefix').str_replace(C('db_prefix'), '', $table);
		$tables = $this->list_tables();
		return in_array($table, $tables);
	}


	/**
	 * 检查字段是否存在
	 * @param $table 表名
	 * @param $field 字段名
	 * @return boolean
	 */
	public function field_exists($table, $field) {
		$fields = $this->get_fields($table);
		return in_array($field, $fields);
	}
		
	
	/**
	 * 返回 MySQL 服务器版本信息
	 * @return string 
	 */	
	public function version(){
	    return mysql_get_server_info();	
	}
	
	
	/**
	 * 关闭数据库连接
	 * @return boolean
	 */	
	public function close(){
	    return @mysql_close(self::$link);
	}
	
}