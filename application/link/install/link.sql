DROP TABLE IF EXISTS `yzmcms_link`;
CREATE TABLE `yzmcms_link` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `typeid` smallint(3) unsigned NOT NULL DEFAULT '1' COMMENT '1首页,2列表页,3内容页',
  `linktype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:文字链接,1:logo链接',
  `name` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `msg` text NOT NULL,
  `username` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(40) NOT NULL DEFAULT '',
  `listorder` smallint(5) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未通过,1正常,2未审核',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `siteid` (`siteid`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;