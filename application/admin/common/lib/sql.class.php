<?php
defined('IN_YZMPHP') or exit('Access Denied'); 

class sql{
	
	public static $tablename;

	
	public static function set_tablename($tablename){
		self::$tablename = C('db_prefix').$tablename;
	}

	
	public static function sql_create($tablename){
		self::set_tablename($tablename);
		self::sql_delete($tablename);
		$sql = "CREATE TABLE `".self::$tablename."` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
		  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
		  `username` varchar(20) NOT NULL DEFAULT '',
		  `nickname` varchar(30) NOT NULL DEFAULT '',
		  `title` varchar(180) NOT NULL DEFAULT '',
		  `seo_title` varchar(200) NOT NULL DEFAULT '',
		  `color` char(9) NOT NULL DEFAULT '',
		  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
		  `updatetime` int(10) unsigned NOT NULL DEFAULT '0',
		  `keywords` varchar(100) NOT NULL DEFAULT '',
		  `description` varchar(255) NOT NULL DEFAULT '',
		  `click` mediumint(8) unsigned NOT NULL DEFAULT '0',
		  `content` text NOT NULL,
		  `copyfrom` varchar(50) NOT NULL DEFAULT '',
		  `thumb` varchar(100) NOT NULL DEFAULT '',
		  `url` varchar(100) NOT NULL DEFAULT '',
		  `flag` varchar(12) NOT NULL DEFAULT '' COMMENT '1置顶,2头条,3特荐,4推荐,5热点,6幻灯,7跳转',
		  `status` tinyint(1) NOT NULL DEFAULT '1',
		  `system` tinyint(1) unsigned NOT NULL DEFAULT '0',
		  `listorder` tinyint(3) unsigned NOT NULL DEFAULT '1',
		  `groupids_view` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '阅读权限',
		  `readpoint` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '阅读收费',
		  `paytype` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '收费类型',
		  `is_push` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否百度推送',
		  PRIMARY KEY (`id`),
		  KEY `status` (`status`,`listorder`),
		  KEY `catid` (`catid`,`status`),
		  KEY `userid` (`userid`,`status`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
        self::sql_exec($sql);			
	}
	
	
	public static function sql_delete($tablename){
		self::set_tablename($tablename);
		$sql = "DROP TABLE IF EXISTS `".self::$tablename."`";
		self::sql_exec($sql);			
	}


	public static function sql_add_field($tablename, $field, $defaultvalue='', $maxlength=100){
		self::set_tablename($tablename);
		$sql = "ALTER TABLE `".self::$tablename."` ADD COLUMN `$field` varchar($maxlength) NOT NULL DEFAULT '$defaultvalue'";
		self::sql_exec($sql);			
	}


	public static function sql_add_field_mediumtext($tablename, $field){
		self::set_tablename($tablename);
		$sql = "ALTER TABLE `".self::$tablename."` ADD COLUMN `$field` mediumtext NOT NULL";
		self::sql_exec($sql);			
	}
	
	
	public static function sql_add_field_text($tablename, $field){
		self::set_tablename($tablename);
		$sql = "ALTER TABLE `".self::$tablename."` ADD COLUMN `$field` text NOT NULL";
		self::sql_exec($sql);			
	}
	
	
	public static function sql_add_field_int($tablename, $field, $defaultvalue=0){
		self::set_tablename($tablename);
		$sql = "ALTER TABLE `".self::$tablename."` ADD COLUMN `$field` int(10) UNSIGNED NOT NULL DEFAULT $defaultvalue";
		self::sql_exec($sql);			
	}
	

	public static function sql_del_field($tablename, $field){
		self::set_tablename($tablename);
		$sql = "ALTER TABLE `".self::$tablename."` DROP COLUMN `$field`";
		self::sql_exec($sql);			
	}

	
    public static function sql_exec($sql){
		global $model;
		$model = isset($model) ? $model : D('model');
		$model->query($sql);
	}	
}



