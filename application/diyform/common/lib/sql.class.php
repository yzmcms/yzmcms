<?php
/**
 * YzmCMS内容管理系统
 * 商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * 功能定制QQ: 21423830
 * 版权所有 WWW.YZMCMS.COM
 */

defined('IN_YZMPHP') or exit('Access Denied'); 

class sql{
	
	public static $tablename;


	/**
	 * 校验字段名
	 */
	private static function is_valid_field_name($field){
		return is_string($field) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,63}$/', $field);
	}

	/**
	 * 校验表名
	 */
	private static function is_valid_tablename($tablename){
		return is_string($tablename) && preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,63}$/', $tablename);
	}

	/**
	 * 设置表名
	 */
	public static function set_tablename($tablename){
		if(!self::is_valid_tablename($tablename)) return false;
		self::$tablename = C('db_prefix').$tablename;
		return true;
	}


	public static function sql_create($tablename){
		if(!self::set_tablename($tablename)) return false;
		self::sql_delete($tablename);
		$charset = C('db_charset') ? C('db_charset') : 'utf8mb4';
		$charset = in_array(strtolower($charset), array('utf8', 'utf8mb4', 'gbk', 'latin1')) ? $charset : 'utf8mb4';
		$sql = "CREATE TABLE `".self::$tablename."` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
			  `username` varchar(60) NOT NULL DEFAULT '',
			  `ip` varchar(15) NOT NULL DEFAULT '',
			  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=".$charset.";";
        self::sql_exec($sql);			
	}


	public static function sql_delete($tablename){
		if(!self::set_tablename($tablename)) return false;
		$sql = "DROP TABLE IF EXISTS `".self::$tablename."`";
		self::sql_exec($sql);			
	}


	public static function sql_add_field($tablename, $field, $defaultvalue='', $maxlength=255){
		if(!self::set_tablename($tablename)) return false;
		if(!self::is_valid_field_name($field)) return false;
		$maxlength = (int)$maxlength;
		if($maxlength <1 || $maxlength>5000) $maxlength = 255;
		$defaultvalue = addslashes((string)$defaultvalue);
		$sql = "ALTER TABLE `".self::$tablename."` ADD COLUMN `".$field."` varchar(".$maxlength.") NOT NULL DEFAULT '".$defaultvalue."'";
		self::sql_exec($sql); 			
	}


	public static function sql_add_field_mediumtext($tablename, $field){
		if(!self::set_tablename($tablename)) return false;
		if(!self::is_valid_field_name($field)) return false;
		$sql = "ALTER TABLE `".self::$tablename."` ADD COLUMN `".$field."` mediumtext";
		self::sql_exec($sql); 			
	}
	
	
	public static function sql_add_field_text($tablename, $field){
		if(!self::set_tablename($tablename)) return false;
		if(!self::is_valid_field_name($field)) return false;
		$sql = "ALTER TABLE `".self::$tablename."` ADD COLUMN `".$field."` text";
		self::sql_exec($sql); 			
	}
	
	
	public static function sql_add_field_int($tablename, $field, $defaultvalue=0){
		if(!self::set_tablename($tablename)) return false;
		if(!self::is_valid_field_name($field)) return false;
		$defaultvalue = (int)$defaultvalue;
		$sql = "ALTER TABLE `".self::$tablename."` ADD COLUMN `".$field."` int(10) UNSIGNED NOT NULL DEFAULT ".$defaultvalue;
		self::sql_exec($sql); 			
	}


	public static function sql_add_field_decimal($tablename, $field, $defaultvalue='0.00'){
		if(!self::set_tablename($tablename)) return false;
		if(!self::is_valid_field_name($field)) return false;
		$defaultvalue = number_format((float)$defaultvalue, 2, '.', '');
		$sql = "ALTER TABLE `".self::$tablename."` ADD COLUMN `".$field."` decimal(8,2) unsigned NOT NULL DEFAULT ".$defaultvalue;
		self::sql_exec($sql); 			
	}
	

	public static function sql_del_field($tablename, $field){
		if(!self::set_tablename($tablename)) return false;
		if(!self::is_valid_field_name($field)) return false;
		$sql = "ALTER TABLE `".self::$tablename."` DROP COLUMN `".$field."`";
		self::sql_exec($sql); 			
	}

	
    public static function sql_exec($sql){
		global $model;
		$model = isset($model) ? $model : D('model');
		$model->query($sql);
	}	
}



