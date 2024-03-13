-- ----------------------------
-- author: yuanzhimeng 
-- qq: 214243830 
-- framework: yzmphp 
-- siteurl: www.yzmcms.com 
-- YzmCMS开源内容管理系统 
-- ----------------------------

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for yzm_admin
-- ----------------------------
DROP TABLE IF EXISTS `yzm_admin`;
CREATE TABLE `yzm_admin` (
  `adminid` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `adminname` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `roleid` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `rolename` varchar(30) NOT NULL DEFAULT '',
  `realname` varchar(30) NOT NULL DEFAULT '',
  `nickname` varchar(30) NOT NULL DEFAULT '',
  `email` varchar(30) NOT NULL DEFAULT '',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0',
  `loginip` varchar(15) NOT NULL DEFAULT '',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `errnum` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `addpeople` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`adminid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_admin
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `yzm_admin_log`;
CREATE TABLE `yzm_admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(15) NOT NULL DEFAULT '',
  `controller` varchar(20) NOT NULL DEFAULT '',
  `querystring` varchar(255) NOT NULL DEFAULT '',
  `adminid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `adminname` varchar(30) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `logtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `logtime` (`logtime`),
  KEY `adminid` (`adminid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_admin_log
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_admin_login_log
-- ----------------------------
DROP TABLE IF EXISTS `yzm_admin_login_log`;
CREATE TABLE `yzm_admin_login_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adminname` varchar(30) NOT NULL DEFAULT '',
  `logintime` int(10) unsigned NOT NULL DEFAULT '0',
  `loginip` varchar(15) NOT NULL DEFAULT '',
  `address` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(30) NOT NULL DEFAULT '',
  `loginresult` tinyint(1) NOT NULL DEFAULT '0' COMMENT '登录结果1为登录成功0为登录失败',
  `cause` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `admin_index` (`adminname`,`loginresult`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_admin_login_log
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_admin_role
-- ----------------------------
DROP TABLE IF EXISTS `yzm_admin_role`;
CREATE TABLE `yzm_admin_role` (
  `roleid` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `rolename` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`roleid`),
  KEY `disabled` (`disabled`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_admin_role
-- ----------------------------
INSERT INTO `yzm_admin_role` VALUES ('1', '超级管理员', '超级管理员', '1', '0');
INSERT INTO `yzm_admin_role` VALUES ('2', '总编', '总编', '1', '0');
INSERT INTO `yzm_admin_role` VALUES ('3', '发布人员', '发布人员', '1', '0');

-- ----------------------------
-- Table structure for yzm_admin_role_priv
-- ----------------------------
DROP TABLE IF EXISTS `yzm_admin_role_priv`;
CREATE TABLE `yzm_admin_role_priv` (
  `roleid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `m` char(20) NOT NULL DEFAULT '',
  `c` char(20) NOT NULL DEFAULT '',
  `a` char(30) NOT NULL DEFAULT '',
  `data` char(100) NOT NULL DEFAULT '',
  KEY `roleid` (`roleid`,`m`,`c`,`a`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_admin_role_priv
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_adver
-- ----------------------------
DROP TABLE IF EXISTS `yzm_adver`;
CREATE TABLE `yzm_adver` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1文字2代码3图片',
  `title` varchar(100) NOT NULL DEFAULT '',
  `url` varchar(200) NOT NULL DEFAULT '',
  `text` varchar(200) NOT NULL DEFAULT '',
  `img` varchar(200) NOT NULL DEFAULT '',
  `code` text NOT NULL,
  `describe` varchar(250) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_adver
-- ----------------------------
INSERT INTO `yzm_adver` VALUES ('1', '1', '首页广告位', 'http://www.yzmcms.com', '免费又好用的CMS建站系统，就选YzmCMS', '', '<a href=\"http://www.yzmcms.com\" target=\"_blank\" title=\"首页广告位\">免费又好用的CMS建站系统，就选YzmCMS</a>', '', '1570799778', '0', '0');

-- ----------------------------
-- Table structure for yzm_all_content
-- ----------------------------
DROP TABLE IF EXISTS `yzm_all_content`;
CREATE TABLE `yzm_all_content` (
  `allid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `modelid` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` char(30) NOT NULL DEFAULT '',
  `title` varchar(150) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0',
  `url` varchar(100) NOT NULL DEFAULT '',
  `thumb` varchar(150) NOT NULL DEFAULT '',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`allid`),
  KEY `userid_index` (`userid`,`issystem`,`status`),
  KEY `modelid_index` (`modelid`,`id`),
  KEY `status` (`siteid`,`status`),
  KEY `issystem` (`siteid`,`issystem`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_all_content
-- ----------------------------
INSERT INTO `yzm_all_content` VALUES ('1', '0', '1', '2', '1', '1', 'yzmcms', 'YZMPHP轻量级开源框架 V2.9', '1710000726', '1710000726', '/guanfangxinwen/1.html', '', '1', '1');
INSERT INTO `yzm_all_content` VALUES ('2', '0', '1', '2', '2', '1', 'yzmcms', 'YzmCMS v7.1正式版发布', '1710270615', '1710270615', '/guanfangxinwen/2.html', '', '1', '1');

-- ----------------------------
-- Table structure for yzm_article
-- ----------------------------
DROP TABLE IF EXISTS `yzm_article`;
CREATE TABLE `yzm_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `nickname` varchar(30) NOT NULL DEFAULT '',
  `title` varchar(180) NOT NULL DEFAULT '',
  `color` char(9) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0',
  `keywords` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `click` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `copyfrom` varchar(50) NOT NULL DEFAULT '',
  `thumb` varchar(150) NOT NULL DEFAULT '',
  `url` varchar(100) NOT NULL DEFAULT '',
  `flag` varchar(12) NOT NULL DEFAULT '' COMMENT '1置顶,2头条,3特荐,4推荐,5热点,6幻灯,7跳转',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `listorder` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `groupids_view` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '阅读权限',
  `readpoint` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '阅读收费',
  `paytype` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '收费类型',
  `is_push` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否百度推送',
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`listorder`),
  KEY `catid` (`status`,`catid`),
  KEY `userid` (`status`,`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_article
-- ----------------------------
INSERT INTO `yzm_article` VALUES ('1', '2', '1', 'yzmcms', '袁志蒙', 'YZMPHP轻量级开源框架 V2.9', '', '1710000726', '1710000726', 'yzmphp,php框架,轻量级框架,mvc框架', '简介：YZMPHP是一款免费开源的轻量级PHP框架，框架完全采用面向对象的设计思想，并且是基于MVC的三层设计模式。具有部署和应用及为简单、效...', '100', '<p><strong>简介:</strong></p><p>YZMPHP是一款免费开源的轻量级PHP框架，框架完全采用面向对象的设计思想，并且是基于MVC的三层设计模式。具有部署和应用及为简单、效率高、速度快，扩展性和可维护性都很好等特点。</p><p>2016年12月19日完成框架的1.0版本，经过近两年的磨炼与成长，今日发布YZMPHP 2.0版本，该框架已经被多家公司企业采用和认可，是一款简单强大的PHP框架。上手快、框架源码简单明了结构清析，使得项目开发更加容易和方便，使用YZMPHP框架适合开发BBS、电子商城、SNS、CMS、Blog、企业门户等任何的中小型系统！</p><p><br/></p><p><strong>特点：</strong></p><p>简洁、高效、轻量级、高性能</p><p>软件环境：Apache/Nginx/IIS</p><p>PHP：支持PHP5.2以上的所有版本</p><p><br/></p><p><strong>YZMPHP  更新日志：</strong></p><p>1.新增：框架命令模式,可自定义或新增命令;</p><p>2.新增：缓存类型配置，支持类型:file/redis/memcache;</p><p>3.新增：系统URL路由映射重写;</p><p>4.新增：DB类库事务处理;</p><p>5.新增：支持切换和链接其他数据库;</p><p>6.新增：DB类库多种操作数据库方法;</p><p>7.新增：Nginx支持PATHINFO模式配置;</p><p>8.新增：系统函数库多种方法;</p><p>9.新增：支持捕捉致命错误;</p><p>10.优化：数据对象单例模式;</p><p>11.优化：支持join多表链接查询;</p><p>12.修复：框架漏洞一枚;</p><p>本次更新优化内容包括但不限于以上所列举的项！</p><p><br/></p>', '原创', '', '/guanfangxinwen/1.html', '4', '1', '1', '10', '0', '0', '1', '0');
INSERT INTO `yzm_article` VALUES ('2', '2', '1', 'yzmcms', '袁志蒙', 'YzmCMS v7.1正式版发布', '#ff0000', '1710270615', '1710270615', 'cms系统,yzmcms最新版,yzmcms下载,php建站系统,轻量级开源', '产品说明：YzmCMS是一款轻量级开源内容管理系统，它采用OOP（面向对象）方式自主开发的框架。基于PHP+Mysql架构，并采用MVC框架式开发的一...', '100', '<p><strong style=\"color: red;\">产品说明：</strong></p><p>YzmCMS是一款轻量级开源内容管理系统，它采用OOP（面向对象）方式自主开发的框架。基于PHP+Mysql架构，并采用MVC框架式开发的一款高效开源的内容管理系统，可运行在Linux、Windows、MacOSX、Solaris等各种平台上。</p><p>它可以让您不需要任何专业技术轻松搭建您需要的网站，操作简单，很容易上手，快捷方便的后台操作让您10分钟就会建立自己的爱站。在同类产品的比较中，YzmCMS更是凸显出了体积轻巧、功能强大、源码简洁、系统安全等特点，无论你是做企业网站、新闻网站、个人博客、门户网站、行业网站、电子商城等，它都能完全胜任，而且还提供了非常方便的二次开发体系，是一款全能型的建站系统！</p><p><br/></p><p>下载地址：<a href=\"http://www.yzmcms.com/xiazai/\" target=\"_blank\" style=\"color:blue\">官方下载</a></p>', '原创', '', '/guanfangxinwen/2.html', '1,4', '1', '1', '1', '0', '0', '1', '0');

-- ----------------------------
-- Table structure for yzm_attachment
-- ----------------------------
DROP TABLE IF EXISTS `yzm_attachment`;
CREATE TABLE `yzm_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `module` char(15) NOT NULL DEFAULT '',
  `contentid` varchar(20) NOT NULL DEFAULT '',
  `originname` varchar(50) NOT NULL DEFAULT '',
  `filename` varchar(50) NOT NULL DEFAULT '',
  `filepath` char(200) NOT NULL DEFAULT '',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `fileext` char(10) NOT NULL DEFAULT '',
  `isimage` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `downloads` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `uploadtime` int(10) unsigned NOT NULL DEFAULT '0',
  `uploadip` char(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `siteid` (`siteid`),
  KEY `userid_index` (`userid`),
  KEY `contentid` (`contentid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_attachment
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_banner
-- ----------------------------
DROP TABLE IF EXISTS `yzm_banner`;
CREATE TABLE `yzm_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `image` varchar(150) NOT NULL DEFAULT '',
  `url` varchar(150) NOT NULL DEFAULT '',
  `introduce` varchar(255) NOT NULL DEFAULT '' COMMENT '简介',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `listorder` smallint(5) unsigned NOT NULL DEFAULT '0',
  `typeid` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1显示0隐藏',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `typeid` (`typeid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_banner
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_banner_type
-- ----------------------------
DROP TABLE IF EXISTS `yzm_banner_type`;
CREATE TABLE `yzm_banner_type` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_banner_type
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_category
-- ----------------------------
DROP TABLE IF EXISTS `yzm_category`;
CREATE TABLE `yzm_category` (
  `catid` smallint(5) NOT NULL AUTO_INCREMENT COMMENT '栏目ID',
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `catname` varchar(60) NOT NULL DEFAULT '' COMMENT '栏目名称',
  `modelid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '模型id',
  `parentid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
  `arrparentid` varchar(255) NOT NULL DEFAULT '' COMMENT '父级路径',
  `arrchildid` mediumtext NOT NULL COMMENT '子栏目id集合',
  `catdir` varchar(50) NOT NULL DEFAULT '' COMMENT '栏目目录',
  `catimg` varchar(150) NOT NULL DEFAULT '' COMMENT '栏目图片',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '栏目类型:0普通栏目1单页2外部链接',
  `listorder` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '栏目排序',
  `target` char(10) NOT NULL DEFAULT '' COMMENT '打开方式',
  `member_publish` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否会员投稿',
  `display` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '在导航显示',
  `pclink` varchar(100) NOT NULL DEFAULT '' COMMENT '电脑版地址',
  `domain` varchar(100) NOT NULL DEFAULT '' COMMENT '绑定域名',
  `entitle` varchar(80) NOT NULL DEFAULT '' COMMENT '英文标题',
  `subtitle` varchar(60) NOT NULL DEFAULT '' COMMENT '副标题',
  `mobname` varchar(50) NOT NULL DEFAULT '' COMMENT '手机版名称',
  `category_template` varchar(30) NOT NULL DEFAULT '' COMMENT '频道页模板',
  `list_template` varchar(30) NOT NULL DEFAULT '' COMMENT '列表页模板',
  `show_template` varchar(30) NOT NULL DEFAULT '' COMMENT '内容页模板',
  `seo_title` varchar(100) NOT NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(200) NOT NULL DEFAULT '' COMMENT 'SEO关键字',
  `seo_description` varchar(250) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  PRIMARY KEY (`catid`),
  KEY `siteid` (`siteid`),
  KEY `modelid` (`modelid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_category
-- ----------------------------
INSERT INTO `yzm_category` VALUES ('1', '0', '新闻中心', '1', '0', '0', '1,2,3', 'xinwenzhongxin', '', '0', '0', '_self', '0', '1', '/xinwenzhongxin/', '', '', '', '新闻中心', 'category_article', 'list_article', 'show_article', '', '', '');
INSERT INTO `yzm_category` VALUES ('2', '0', '官方新闻', '1', '1', '0,1', '2', 'guanfangxinwen', '', '0', '0', '_self', '0', '1', '/guanfangxinwen/', '', '', '', '官方新闻', 'category_article', 'list_article_img', 'show_article', '', '', '');
INSERT INTO `yzm_category` VALUES ('3', '0', '其他新闻', '1', '1', '0,1', '3', 'qitaxinwen', '', '0', '0', '_self', '1', '1', '/qitaxinwen/', '', '', '', '其他新闻', 'category_article', 'list_article', 'show_article', '', '', '');
INSERT INTO `yzm_category` VALUES ('4', '0', '关于我们', '0', '0', '0', '4', 'guanyuwomen', '', '1', '0', '_self', '0', '1', '/guanyuwomen/', '', '', '', '关于我们', 'category_page', '', '', '', '', '');
INSERT INTO `yzm_category` VALUES ('5', '0', '官方网站', '0', '0', '0', '5', '', '', '2', '0', '_blank', '0', '1', 'http://www.yzmcms.com/', '', '', '', '官方网站', '', '', '', '', '', '');

-- ----------------------------
-- Table structure for yzm_collection_content
-- ----------------------------
DROP TABLE IF EXISTS `yzm_collection_content`;
CREATE TABLE `yzm_collection_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nodeid` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:未采集,1:已采集,2:已导入',
  `url` char(255) NOT NULL DEFAULT '',
  `title` char(100) NOT NULL DEFAULT '',
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nodeid` (`nodeid`),
  KEY `status` (`status`),
  KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_collection_content
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_collection_node
-- ----------------------------
DROP TABLE IF EXISTS `yzm_collection_node`;
CREATE TABLE `yzm_collection_node` (
  `nodeid` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '采集节点ID',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '节点名称',
  `lastdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后采集时间',
  `sourcecharset` varchar(8) NOT NULL DEFAULT '' COMMENT '采集点字符集',
  `sourcetype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '网址类型:1序列网址,2单页',
  `urlpage` text NOT NULL COMMENT '采集地址',
  `pagesize_start` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '页码开始',
  `pagesize_end` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '页码结束',
  `par_num` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '每次增加数',
  `url_contain` char(100) NOT NULL DEFAULT '' COMMENT '网址中必须包含',
  `url_except` char(100) NOT NULL DEFAULT '' COMMENT '网址中不能包含',
  `url_start` char(100) NOT NULL DEFAULT '' COMMENT '网址开始',
  `url_end` char(100) NOT NULL DEFAULT '' COMMENT '网址结束',
  `title_rule` char(100) NOT NULL DEFAULT '' COMMENT '标题采集规则',
  `title_html_rule` text NOT NULL COMMENT '标题过滤规则',
  `time_rule` char(100) NOT NULL DEFAULT '' COMMENT '时间采集规则',
  `time_html_rule` text COMMENT '时间过滤规则',
  `content_rule` char(100) NOT NULL DEFAULT '' COMMENT '内容采集规则',
  `content_html_rule` text COMMENT '内容过滤规则',
  `down_attachment` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否下载图片',
  `watermark` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '图片加水印',
  `coll_order` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '导入顺序',
  PRIMARY KEY (`nodeid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_collection_node
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_comment
-- ----------------------------
DROP TABLE IF EXISTS `yzm_comment`;
CREATE TABLE `yzm_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `commentid` char(30) NOT NULL DEFAULT '',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `userpic` varchar(100) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` char(15) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '评论状态{0:未审核,1:通过审核}',
  `reply` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '是否为回复',
  PRIMARY KEY (`id`),
  KEY `siteid` (`siteid`),
  KEY `userid` (`userid`),
  KEY `commentid` (`commentid`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_comment
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_comment_data
-- ----------------------------
DROP TABLE IF EXISTS `yzm_comment_data`;
CREATE TABLE `yzm_comment_data` (
  `commentid` char(30) NOT NULL DEFAULT '',
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `title` char(255) NOT NULL DEFAULT '',
  `url` varchar(200) NOT NULL DEFAULT '',
  `total` int(8) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(4) unsigned NOT NULL DEFAULT '0',
  `modelid` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`commentid`),
  KEY `siteid` (`siteid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_comment_data
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_config
-- ----------------------------
DROP TABLE IF EXISTS `yzm_config`;
CREATE TABLE `yzm_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '配置名称',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '配置类型',
  `title` varchar(60) NOT NULL DEFAULT '' COMMENT '配置说明',
  `value` text NOT NULL COMMENT '配置值',
  `fieldtype` varchar(20) NOT NULL DEFAULT '' COMMENT '字段类型',
  `setting` text COMMENT '字段设置',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_config
-- ----------------------------
INSERT INTO `yzm_config` VALUES ('1', 'site_name', '0', '站点名称', 'YzmCMS演示站', '', '', '1');
INSERT INTO `yzm_config` VALUES ('2', 'site_url', '0', '站点根网址', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('3', 'site_keyword', '0', '站点关键字', 'YzmCMS演示站', '', '', '1');
INSERT INTO `yzm_config` VALUES ('4', 'site_description', '0', '站点描述', 'YzmCMS是一款基于YZMPHP开发的一套轻量级开源内容管理系统，YzmCMS简洁、安全、开源、实用，可运行在Linux、Windows、MacOSX、Solaris等各种平台上，专注为公司企业、个人站长快速建站提供解决方案。', '', '', '1');
INSERT INTO `yzm_config` VALUES ('5', 'site_copyright', '0', '版权信息', 'Powered By YzmCMS内容管理系统 © 2014-2099 袁志蒙工作室', '', '', '1');
INSERT INTO `yzm_config` VALUES ('6', 'site_filing', '0', '站点备案号', '京ICP备666666号', '', '', '1');
INSERT INTO `yzm_config` VALUES ('7', 'site_code', '0', '统计代码', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('8', 'site_theme', '0', '站点模板主题', 'default', '', '', '1');
INSERT INTO `yzm_config` VALUES ('9', 'site_logo', '0', '站点logo', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('10', 'url_mode', '0', '前台URL模式', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('11', 'is_words', '0', '是否开启前端留言功能', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('12', 'upload_maxsize', '0', '文件上传最大限制', '2048', '', '', '1');
INSERT INTO `yzm_config` VALUES ('13', 'upload_types', '0', '允许上传附件类型', 'zip|rar|ppt|doc|xls', '', '', '1');
INSERT INTO `yzm_config` VALUES ('14', 'upload_image_types', '0', '允许上传图片类型', 'png|jpg|jpeg|gif', '', '', '1');
INSERT INTO `yzm_config` VALUES ('15', 'watermark_enable', '0', '是否开启图片水印', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('16', 'watermark_name', '0', '水印图片名称', 'mark.png', '', '', '1');
INSERT INTO `yzm_config` VALUES ('17', 'watermark_position', '0', '水印的位置', '9', '', '', '1');
INSERT INTO `yzm_config` VALUES ('18', 'mail_server', '1', 'SMTP服务器', 'ssl://smtp.qq.com', '', '', '1');
INSERT INTO `yzm_config` VALUES ('19', 'mail_port', '1', 'SMTP服务器端口', '465', '', '', '1');
INSERT INTO `yzm_config` VALUES ('20', 'mail_from', '1', 'SMTP服务器的用户邮箱', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('21', 'mail_auth', '1', 'AUTH LOGIN验证', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('22', 'mail_user', '1', 'SMTP服务器的用户帐号', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('23', 'mail_pass', '1', 'SMTP服务器的用户密码', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('24', 'mail_inbox', '1', '收件邮箱地址', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('25', 'admin_log', '2', '启用后台管理操作日志', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('26', 'admin_prohibit_ip', '2', '禁止登录后台的IP', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('27', 'prohibit_words', '2', '屏蔽词', '她妈|它妈|他妈|你妈|去死|贱人', '', '', '1');
INSERT INTO `yzm_config` VALUES ('28', 'comment_check', '2', '是否开启评论审核', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('29', 'comment_tourist', '2', '是否允许游客评论', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('30', 'is_link', '2', '允许用户申请友情链接', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('31', 'member_register', '3', '是否开启会员注册', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('32', 'member_email', '3', '新会员注册是否需要邮件验证', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('33', 'member_check', '3', '新会员注册是否需要管理员审核', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('34', 'member_point', '3', '新会员默认积分', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('35', 'member_yzm', '3', '是否开启会员登录验证码', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('36', 'rmb_point_rate', '3', '1元人民币购买积分数量', '10', '', '', '1');
INSERT INTO `yzm_config` VALUES ('37', 'login_point', '3', '每日登录奖励积分', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('38', 'comment_point', '3', '发布评论奖励积分', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('39', 'publish_point', '3', '投稿奖励积分', '3', '', '', '1');
INSERT INTO `yzm_config` VALUES ('40', 'qq_app_id', '3', 'QQ App ID', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('41', 'qq_app_key', '3', 'QQ App key', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('42', 'weibo_key', '4', '微博登录App Key', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('43', 'weibo_secret', '4', '微博登录App Secret', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('44', 'wx_appid', '4', '微信开发者ID', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('45', 'wx_secret', '4', '微信开发者密码', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('46', 'wx_token', '4', '微信Token签名', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('47', 'wx_encodingaeskey', '4', '微信EncodingAESKey', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('48', 'wx_relation_model', '4', '微信关联模型', 'article', '', '', '1');
INSERT INTO `yzm_config` VALUES ('49', 'baidu_push_token', '0', '百度推送token', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('50', 'thumb_width', '2', '缩略图默认宽度', '500', '', '', '1');
INSERT INTO `yzm_config` VALUES ('51', 'thumb_height', '2', '缩略图默认高度', '300', '', '', '1');
INSERT INTO `yzm_config` VALUES ('52', 'site_seo_division', '0', '站点标题分隔符', '_', '', '', '1');
INSERT INTO `yzm_config` VALUES ('53', 'keyword_link', '2', '是否启用关键字替换', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('54', 'keyword_replacenum', '2', '关键字替换次数', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('55', 'error_log_save', '2', '是否保存系统错误日志', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('56', 'comment_code', '2', '是否开启评论验证码', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('57', 'site_wap_open', '0', '是否启用手机站点', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('58', 'site_wap_theme', '0', 'WAP端模板风格', 'default', '', '', '1');
INSERT INTO `yzm_config` VALUES ('59', 'member_theme', '3', '会员中心模板风格', 'default', '', '', '1');
INSERT INTO `yzm_config` VALUES ('60', 'att_relation_content', '1', '是否开启内容附件关联', '0', '', '', '1');
INSERT INTO `yzm_config` VALUES ('61', 'site_seo_suffix', '0', '站点SEO后缀', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('62', 'site_security_number', '0', '公安备案号', '', '', ' ', '1');
INSERT INTO `yzm_config` VALUES ('63', 'words_code', '3', '是否开启留言验证码', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('64', 'watermark_minwidth', '2', '添加水印最小宽度', '300', '', '', '1');
INSERT INTO `yzm_config` VALUES ('65', 'watermark_minheight', '2', '添加水印最小高度', '300', '', '', '1');
INSERT INTO `yzm_config` VALUES ('66', 'auto_down_imag', '2', '自动下载远程图片', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('67', 'down_ignore_domain', '2', '下载远程图片忽略的域名', '', '', '', '1');
INSERT INTO `yzm_config` VALUES ('68', 'content_click_random', '2', '内容默认点击量', '1', '', '', '1');
INSERT INTO `yzm_config` VALUES ('69', 'blacklist_ip', '3', ' 前端IP黑名单', '', '', '', '1');

-- ----------------------------
-- Table structure for yzm_download
-- ----------------------------
DROP TABLE IF EXISTS `yzm_download`;
CREATE TABLE `yzm_download` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `nickname` varchar(30) NOT NULL DEFAULT '',
  `title` varchar(180) NOT NULL DEFAULT '',
  `color` char(9) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0',
  `keywords` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `click` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `copyfrom` varchar(50) NOT NULL DEFAULT '',
  `thumb` varchar(150) NOT NULL DEFAULT '',
  `url` varchar(100) NOT NULL DEFAULT '',
  `flag` varchar(12) NOT NULL DEFAULT '' COMMENT '1置顶,2头条,3特荐,4推荐,5热点,6幻灯,7跳转',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `listorder` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `groupids_view` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '阅读权限',
  `readpoint` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '阅读收费',
  `paytype` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '收费类型',
  `is_push` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否百度推送',
  `down_url` varchar(200) NOT NULL DEFAULT '' COMMENT '下载地址',
  `copytype` varchar(30) NOT NULL DEFAULT '' COMMENT '授权形式',
  `systems` varchar(100) NOT NULL DEFAULT '' COMMENT '平台',
  `language` varchar(30) NOT NULL DEFAULT '' COMMENT '语言',
  `version` varchar(30) NOT NULL DEFAULT '' COMMENT '版本',
  `filesize` varchar(10) NOT NULL DEFAULT '' COMMENT '文件大小',
  `classtype` varchar(30) NOT NULL DEFAULT '' COMMENT '软件类型',
  `stars` varchar(10) NOT NULL DEFAULT '' COMMENT '评分等级',
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`listorder`),
  KEY `catid` (`status`,`catid`),
  KEY `userid` (`status`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_download
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_favorite
-- ----------------------------
DROP TABLE IF EXISTS `yzm_favorite`;
CREATE TABLE `yzm_favorite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` char(100) NOT NULL DEFAULT '',
  `url` char(100) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_favorite
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_guestbook
-- ----------------------------
DROP TABLE IF EXISTS `yzm_guestbook`;
CREATE TABLE `yzm_guestbook` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `title` varchar(150) NOT NULL DEFAULT '' COMMENT '主题',
  `booktime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '名字',
  `email` varchar(40) NOT NULL DEFAULT '' COMMENT '留言人电子邮箱',
  `phone` varchar(11) NOT NULL DEFAULT '' COMMENT '留言人电话',
  `qq` varchar(11) NOT NULL DEFAULT '' COMMENT '留言人qq',
  `address` varchar(100) NOT NULL DEFAULT '' COMMENT '留言人地址',
  `bookmsg` text NOT NULL COMMENT '内容',
  `ip` varchar(20) NOT NULL DEFAULT '' COMMENT 'ip地址',
  `ischeck` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否审核',
  `isread` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否读过',
  `ispc` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1电脑,0手机',
  `replyid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复的id',
  PRIMARY KEY (`id`),
  KEY `index_booktime` (`booktime`),
  KEY `index_replyid` (`replyid`),
  KEY `index_ischeck` (`siteid`,`ischeck`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_guestbook
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_keyword_link
-- ----------------------------
DROP TABLE IF EXISTS `yzm_keyword_link`;
CREATE TABLE `yzm_keyword_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(36) NOT NULL DEFAULT '' COMMENT '关键字',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '地址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_keyword_link
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_link
-- ----------------------------
DROP TABLE IF EXISTS `yzm_link`;
CREATE TABLE `yzm_link` (
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_link
-- ----------------------------
INSERT INTO `yzm_link` VALUES ('1', '0', '0', '0', 'YzmCMS官方网站', 'http://www.yzmcms.com/', '', '', '袁志蒙', '', '1', '1', '1483095485');
INSERT INTO `yzm_link` VALUES ('2', '0', '0', '0', 'YzmCMS交流社区', 'http://www.yzmask.com/', '', '', '袁志蒙', '', '2', '1', '1483095496');
INSERT INTO `yzm_link` VALUES ('3', '0', '0', '0', 'YzmCMS官方博客', 'http://blog.yzmcms.com/', '', '', '袁志蒙', '', '3', '1', '1483095596');

-- ----------------------------
-- Table structure for yzm_member
-- ----------------------------
DROP TABLE IF EXISTS `yzm_member`;
CREATE TABLE `yzm_member` (
  `userid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL DEFAULT '',
  `password` char(32) NOT NULL DEFAULT '',
  `regdate` int(10) unsigned NOT NULL DEFAULT '0',
  `lastdate` int(10) unsigned NOT NULL DEFAULT '0',
  `regip` char(15) NOT NULL DEFAULT '',
  `lastip` char(15) NOT NULL DEFAULT '',
  `loginnum` smallint(5) unsigned NOT NULL DEFAULT '0',
  `email` char(32) NOT NULL DEFAULT '',
  `groupid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `amount` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '金钱',
  `experience` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '经验',
  `point` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0待审核,1正常,2锁定,3拒绝',
  `vip` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `overduedate` int(10) unsigned NOT NULL DEFAULT '0',
  `email_status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `problem` varchar(39) NOT NULL DEFAULT '' COMMENT '安全问题',
  `answer` varchar(30) NOT NULL DEFAULT '' COMMENT '答案',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_member
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_member_authorization
-- ----------------------------
DROP TABLE IF EXISTS `yzm_member_authorization`;
CREATE TABLE `yzm_member_authorization` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `authname` varchar(10) NOT NULL DEFAULT '',
  `token` varchar(60) NOT NULL DEFAULT '',
  `userinfo` varchar(255) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `authindex` (`authname`,`token`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_member_authorization
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_member_detail
-- ----------------------------
DROP TABLE IF EXISTS `yzm_member_detail`;
CREATE TABLE `yzm_member_detail` (
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `sex` varchar(6) NOT NULL DEFAULT '',
  `realname` varchar(30) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `nickname` varchar(30) NOT NULL DEFAULT '',
  `qq` char(11) NOT NULL DEFAULT '',
  `mobile` char(11) NOT NULL DEFAULT '',
  `phone` char(10) NOT NULL DEFAULT '',
  `userpic` varchar(100) NOT NULL DEFAULT '',
  `birthday` char(10) NOT NULL DEFAULT '' COMMENT '生日',
  `industry` varchar(60) NOT NULL DEFAULT '' COMMENT '行业',
  `area` varchar(60) NOT NULL DEFAULT '',
  `motto` varchar(210) NOT NULL DEFAULT '' COMMENT '个性签名',
  `introduce` text COMMENT '个人简介',
  `guest` int(10) unsigned NOT NULL DEFAULT '0',
  `fans` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '粉丝数',
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_member_detail
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_member_follow
-- ----------------------------
DROP TABLE IF EXISTS `yzm_member_follow`;
CREATE TABLE `yzm_member_follow` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `followid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '被关注者id',
  `followname` varchar(30) NOT NULL DEFAULT '' COMMENT '被关注者用户名',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_member_follow
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_member_group
-- ----------------------------
DROP TABLE IF EXISTS `yzm_member_group`;
CREATE TABLE `yzm_member_group` (
  `groupid` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(21) NOT NULL DEFAULT '',
  `experience` smallint(6) unsigned NOT NULL DEFAULT '0',
  `icon` char(30) NOT NULL DEFAULT '' COMMENT '图标',
  `authority` char(12) NOT NULL DEFAULT '' COMMENT '1短消息,2发表评论,3发表内容',
  `max_amount` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '每日最大投稿量',
  `description` char(100) NOT NULL DEFAULT '',
  `is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统内置',
  PRIMARY KEY (`groupid`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_member_group
-- ----------------------------
INSERT INTO `yzm_member_group` VALUES ('1', '初来乍到', '50', 'icon1.png', '1,2', '1', '初来乍到组', '1');
INSERT INTO `yzm_member_group` VALUES ('2', '新手上路', '100', 'icon2.png', '1,2', '2', '新手上路组', '1');
INSERT INTO `yzm_member_group` VALUES ('3', '中级会员', '200', 'icon3.png', '1,2,3', '3', '中级会员组', '1');
INSERT INTO `yzm_member_group` VALUES ('4', '高级会员', '300', 'icon4.png', '1,2,3', '4', '高级会员组', '1');
INSERT INTO `yzm_member_group` VALUES ('5', '金牌会员', '500', 'icon5.png', '1,2,3,4', '5', '金牌会员组', '1');

-- ----------------------------
-- Table structure for yzm_member_guest
-- ----------------------------
DROP TABLE IF EXISTS `yzm_member_guest`;
CREATE TABLE `yzm_member_guest` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `space_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `guest_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `guest_name` varchar(30) NOT NULL DEFAULT '',
  `guest_pic` varchar(100) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `space_id` (`space_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_member_guest
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_menu
-- ----------------------------
DROP TABLE IF EXISTS `yzm_menu`;
CREATE TABLE `yzm_menu` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(40) NOT NULL DEFAULT '',
  `parentid` smallint(6) NOT NULL DEFAULT '0',
  `m` char(20) NOT NULL DEFAULT '',
  `c` char(20) NOT NULL DEFAULT '',
  `a` char(30) NOT NULL DEFAULT '',
  `data` char(100) NOT NULL DEFAULT '',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0',
  `display` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `listorder` (`listorder`),
  KEY `parentid` (`parentid`),
  KEY `module` (`m`,`c`,`a`)
) ENGINE=MyISAM AUTO_INCREMENT=317 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_menu
-- ----------------------------
INSERT INTO `yzm_menu` VALUES ('1', '内容管理', '0', 'admin', 'content', 'top', 'yzm-iconneirong', '1', '1');
INSERT INTO `yzm_menu` VALUES ('2', '会员管理', '0', 'member', 'member', 'top', 'yzm-iconyonghu', '2', '1');
INSERT INTO `yzm_menu` VALUES ('3', '模块管理', '0', 'admin', 'module', 'top', 'yzm-icondaohang', '3', '1');
INSERT INTO `yzm_menu` VALUES ('4', '管理员管理', '0', 'admin', 'admin_manage', 'top', 'yzm-iconguanliyuan', '4', '1');
INSERT INTO `yzm_menu` VALUES ('5', '个人信息', '0', 'admin', 'admin_manage', 'top', 'yzm-iconrizhi', '5', '0');
INSERT INTO `yzm_menu` VALUES ('6', '系统管理', '0', 'admin', 'system_manage', 'top', 'yzm-iconshezhi', '6', '1');
INSERT INTO `yzm_menu` VALUES ('7', '数据管理', '0', 'admin', 'database', 'top', 'yzm-iconshujuku', '7', '1');
INSERT INTO `yzm_menu` VALUES ('8', '稿件管理', '1', 'admin', 'admin_content', 'init', '', '13', '1');
INSERT INTO `yzm_menu` VALUES ('9', '稿件浏览', '8', 'admin', 'admin_content', 'public_preview', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('10', '稿件删除', '8', 'admin', 'admin_content', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('11', '通过审核', '8', 'admin', 'admin_content', 'adopt', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('12', '退稿', '8', 'admin', 'admin_content', 'rejection', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('13', '后台操作日志', '6', 'admin', 'admin_log', 'init', '', '66', '1');
INSERT INTO `yzm_menu` VALUES ('14', '操作日志删除', '13', 'admin', 'admin_log', 'del_log', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('15', '操作日志搜索', '13', 'admin', 'admin_log', 'search_log', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('16', '后台登录日志', '6', 'admin', 'admin_log', 'admin_login_log_list', '', '67', '1');
INSERT INTO `yzm_menu` VALUES ('17', '登录日志删除', '16', 'admin', 'admin_log', 'del_login_log', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('18', '管理员管理', '4', 'admin', 'admin_manage', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('19', '删除管理员', '18', 'admin', 'admin_manage', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('20', '添加管理员', '18', 'admin', 'admin_manage', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('21', '编辑管理员', '18', 'admin', 'admin_manage', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('22', '修改资料', '18', 'admin', 'admin_manage', 'public_edit_info', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('23', '修改密码', '18', 'admin', 'admin_manage', 'public_edit_pwd', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('24', '栏目管理', '1', 'admin', 'category', 'init', '', '11', '1');
INSERT INTO `yzm_menu` VALUES ('25', '排序栏目', '24', 'admin', 'category', 'order', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('26', '删除栏目', '24', 'admin', 'category', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('27', '添加栏目', '24', 'admin', 'category', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('28', '编辑栏目', '24', 'admin', 'category', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('29', '编辑单页内容', '24', 'admin', 'category', 'page_content', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('30', '内容管理', '1', 'admin', 'content', 'init', '', '10', '1');
INSERT INTO `yzm_menu` VALUES ('31', '内容搜索', '30', 'admin', 'content', 'search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('32', '添加内容', '30', 'admin', 'content', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('33', '修改内容', '30', 'admin', 'content', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('34', '删除内容', '30', 'admin', 'content', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('35', '数据备份', '7', 'admin', 'database', 'init', '', '70', '1');
INSERT INTO `yzm_menu` VALUES ('36', '数据还原', '7', 'admin', 'database', 'databack_list', '', '71', '1');
INSERT INTO `yzm_menu` VALUES ('37', '优化表', '35', 'admin', 'database', 'public_optimize', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('38', '修复表', '35', 'admin', 'database', 'public_repair', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('39', '备份文件删除', '36', 'admin', 'database', 'databack_del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('40', '备份文件下载', '36', 'admin', 'database', 'databack_down', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('41', '数据导入', '36', 'admin', 'database', 'import', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('42', '字段管理', '54', 'admin', 'model_field', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('43', '添加字段', '42', 'admin', 'model_field', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('44', '修改字段', '42', 'admin', 'model_field', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('45', '删除字段', '42', 'admin', 'model_field', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('46', '排序字段', '42', 'admin', 'model_field', 'order', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('47', '模块管理', '3', 'admin', 'module', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('48', '模块安装', '47', 'admin', 'module', 'install', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('49', '模块卸载', '47', 'admin', 'module', 'uninstall', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('50', '角色管理', '4', 'admin', 'role', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('51', '删除角色', '50', 'admin', 'role', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('52', '添加角色', '50', 'admin', 'role', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('53', '编辑角色', '50', 'admin', 'role', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('54', '模型管理', '1', 'admin', 'sitemodel', 'init', '', '15', '1');
INSERT INTO `yzm_menu` VALUES ('55', '删除模型', '54', 'admin', 'sitemodel', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('56', '添加模型', '54', 'admin', 'sitemodel', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('57', '编辑模型', '54', 'admin', 'sitemodel', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('58', '系统设置', '6', 'admin', 'system_manage', 'init', '', '60', '1');
INSERT INTO `yzm_menu` VALUES ('59', '会员中心设置', '2', 'admin', 'system_manage', 'member_set', '', '26', '1');
INSERT INTO `yzm_menu` VALUES ('60', '屏蔽词管理', '6', 'admin', 'system_manage', 'prohibit_words', '', '63', '1');
INSERT INTO `yzm_menu` VALUES ('61', '自定义配置', '6', 'admin', 'system_manage', 'user_config_list', '', '62', '1');
INSERT INTO `yzm_menu` VALUES ('62', '添加配置', '61', 'admin', 'system_manage', 'user_config_add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('63', '配置编辑', '61', 'admin', 'system_manage', 'user_config_edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('64', '配置删除', '61', 'admin', 'system_manage', 'user_config_del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('65', 'TAG管理', '1', 'admin', 'tag', 'init', '', '16', '1');
INSERT INTO `yzm_menu` VALUES ('66', '添加TAG', '65', 'admin', 'tag', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('67', '编辑TAG', '65', 'admin', 'tag', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('68', '删除TAG', '65', 'admin', 'tag', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('69', '批量更新URL', '1', 'admin', 'update_urls', 'init', '', '17', '1');
INSERT INTO `yzm_menu` VALUES ('70', '附件管理', '1', 'attachment', 'index', 'init', '', '14', '1');
INSERT INTO `yzm_menu` VALUES ('71', '附件搜索', '70', 'attachment', 'index', 'search_list', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('72', '附件浏览', '70', 'attachment', 'index', 'public_att_view', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('73', '删除单个附件', '70', 'attachment', 'index', 'del_one', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('74', '删除多个附件', '70', 'attachment', 'index', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('75', '评论管理', '1', 'comment', 'comment', 'init', '', '12', '1');
INSERT INTO `yzm_menu` VALUES ('76', '评论搜索', '75', 'comment', 'comment', 'search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('77', '删除评论', '75', 'comment', 'comment', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('78', '评论审核', '75', 'comment', 'comment', 'adopt', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('79', '留言管理', '3', 'guestbook', 'guestbook', 'init', '', '1', '1');
INSERT INTO `yzm_menu` VALUES ('80', '查看及回复留言', '79', 'guestbook', 'guestbook', 'read', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('81', '留言审核', '79', 'guestbook', 'guestbook', 'toggle', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('82', '删除留言', '79', 'guestbook', 'guestbook', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('88', '会员管理', '2', 'member', 'member', 'init', '', '20', '1');
INSERT INTO `yzm_menu` VALUES ('89', '会员搜索', '88', 'member', 'member', 'search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('90', '添加会员', '88', 'member', 'member', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('91', '修改会员信息', '88', 'member', 'member', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('92', '修改会员密码', '88', 'member', 'member', 'password', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('93', '删除会员', '88', 'member', 'member', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('94', '审核会员', '2', 'member', 'member', 'check', '', '21', '1');
INSERT INTO `yzm_menu` VALUES ('95', '通过审核', '94', 'member', 'member', 'adopt', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('96', '锁定用户', '88', 'member', 'member', 'lock', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('97', '解锁用户', '88', 'member', 'member', 'unlock', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('98', '账单管理', '2', 'member', 'member', 'pay', '', '22', '1');
INSERT INTO `yzm_menu` VALUES ('99', '入账记录搜索', '98', 'member', 'member', 'pay_search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('100', '入账记录删除', '98', 'member', 'member', 'pay_del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('101', '消费记录', '98', 'member', 'member', 'pay_spend', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('102', '消费记录搜索', '98', 'member', 'member', 'pay_spend_search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('103', '消费记录删除', '98', 'member', 'member', 'pay_spend_del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('104', '会员组管理', '2', 'member', 'member_group', 'init', '', '25', '1');
INSERT INTO `yzm_menu` VALUES ('105', '添加组别', '104', 'member', 'member_group', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('106', '修改组别', '104', 'member', 'member_group', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('107', '删除组别', '104', 'member', 'member_group', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('108', '消息管理', '2', 'member', 'member_message', 'init', '', '23', '1');
INSERT INTO `yzm_menu` VALUES ('109', '消息搜索', '108', 'member', 'member_message', 'search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('110', '删除消息', '108', 'member', 'member_message', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('111', '发送单个消息', '108', 'member', 'member_message', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('112', '群发消息', '2', 'member', 'member_message', 'messages_list', '', '23', '1');
INSERT INTO `yzm_menu` VALUES ('113', '新建群发', '112', 'member', 'member_message', 'add_messages', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('114', '删除群发消息', '112', 'member', 'member_message', 'del_messages', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('115', '权限管理', '50', 'admin', 'role', 'role_priv', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('116', '后台菜单管理', '6', 'admin', 'menu', 'init', '', '64', '1');
INSERT INTO `yzm_menu` VALUES ('117', '删除菜单', '116', 'admin', 'menu', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('118', '添加菜单', '116', 'admin', 'menu', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('119', '编辑菜单', '116', 'admin', 'menu', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('120', '菜单排序', '116', 'admin', 'menu', 'order', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('121', '邮箱配置', '6', 'admin', 'system_manage', 'init', 'tab=4', '61', '1');
INSERT INTO `yzm_menu` VALUES ('122', '修改资料', '5', 'admin', 'admin_manage', 'public_edit_info', '', '51', '1');
INSERT INTO `yzm_menu` VALUES ('123', '修改密码', '5', 'admin', 'admin_manage', 'public_edit_pwd', '', '52', '1');
INSERT INTO `yzm_menu` VALUES ('134', '友情链接管理', '3', 'link', 'link', 'init', '', '6', '1');
INSERT INTO `yzm_menu` VALUES ('135', '添加友情链接', '134', 'link', 'link', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('136', '修改友情链接', '134', 'link', 'link', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('137', '删除单个友情链接', '134', 'link', 'link', 'del_one', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('138', '删除多个友情链接', '134', 'link', 'link', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('139', 'URL规则管理', '6', 'admin', 'urlrule', 'init', '', '65', '1');
INSERT INTO `yzm_menu` VALUES ('140', '添加URL规则', '139', 'admin', 'urlrule', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('141', '删除URL规则', '139', 'admin', 'urlrule', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('142', '编辑URL规则', '139', 'admin', 'urlrule', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('143', '移动分类', '30', 'admin', 'content', 'remove', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('144', 'SQL命令行', '6', 'admin', 'sql', 'init', '', '63', '1');
INSERT INTO `yzm_menu` VALUES ('145', '提交SQL命令', '144', 'admin', 'sql', 'do_sql', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('156', '轮播图管理', '3', 'banner', 'banner', 'init', '', '1', '1');
INSERT INTO `yzm_menu` VALUES ('157', '添加轮播', '156', 'banner', 'banner', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('158', '修改轮播', '156', 'banner', 'banner', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('159', '删除轮播', '156', 'banner', 'banner', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('160', '添加轮播分类', '156', 'banner', 'banner', 'cat_add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('161', '管理轮播分类', '156', 'banner', 'banner', 'cat_manage', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('162', '会员统计', '2', 'member', 'member', 'member_count', '', '24', '1');
INSERT INTO `yzm_menu` VALUES ('165', '采集管理', '3', 'collection', 'collection_content', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('166', '添加采集节点', '165', 'collection', 'collection_content', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('167', '编辑采集节点', '165', 'collection', 'collection_content', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('168', '删除采集节点', '165', 'collection', 'collection_content', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('169', '采集测试', '165', 'collection', 'collection_content', 'collection_test', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('170', '采集网址', '165', 'collection', 'collection_content', 'collection_list_url', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('171', '采集内容', '165', 'collection', 'collection_content', 'collection_article_content', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('172', '内容导入', '165', 'collection', 'collection_content', 'collection_content_import', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('173', '新建内容发布方案', '165', 'collection', 'collection_content', 'create_programme', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('174', '采集列表', '165', 'collection', 'collection_content', 'collection_list', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('175', '删除采集列表', '165', 'collection', 'collection_content', 'collection_list_del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('200', '微信管理', '0', 'wechat', 'wechat', 'top', 'yzm-iconweixin', '3', '1');
INSERT INTO `yzm_menu` VALUES ('201', '微信配置', '200', 'wechat', 'config', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('202', '保存配置', '201', 'wechat', 'config', 'save', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('203', '微信用户', '200', 'wechat', 'user', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('204', '关注者搜索', '203', 'wechat', 'user', 'search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('205', '获取分组名称', '203', 'wechat', 'user', 'get_groupname', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('206', '同步微信服务器用户', '203', 'wechat', 'user', 'synchronization', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('207', '批量移动用户分组', '203', 'wechat', 'user', 'move_user_group', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('208', '设置用户备注', '203', 'wechat', 'user', 'set_userremark', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('209', '查询用户所在组', '203', 'wechat', 'user', 'select_user_group', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('210', '分组管理', '200', 'wechat', 'group', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('211', '创建分组', '210', 'wechat', 'group', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('212', '修改分组', '210', 'wechat', 'group', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('213', '删除分组', '210', 'wechat', 'group', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('214', '查询所有分组', '210', 'wechat', 'group', 'select_group', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('215', '微信菜单', '200', 'wechat', 'menu', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('216', '添加菜单', '215', 'wechat', 'menu', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('217', '编辑菜单', '215', 'wechat', 'menu', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('218', '删除菜单', '215', 'wechat', 'menu', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('219', '菜单排序', '215', 'wechat', 'menu', 'order', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('220', '创建菜单提交微信', '215', 'wechat', 'menu', 'create_menu', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('221', '查询远程菜单', '215', 'wechat', 'menu', 'select_menu', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('222', '删除所有菜单提交微信', '215', 'wechat', 'menu', 'delete_menu', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('223', '消息回复', '200', 'wechat', 'reply', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('224', '自动回复/关注回复', '223', 'wechat', 'reply', 'reply_list', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('225', '添加关键字回复', '223', 'wechat', 'reply', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('226', '修改关键字回复', '223', 'wechat', 'reply', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('227', '删除关键字回复', '223', 'wechat', 'reply', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('228', '选择文章', '223', 'wechat', 'reply', 'select_article', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('229', '消息管理', '200', 'wechat', 'message', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('230', '用户发送信息', '229', 'wechat', 'message', 'send_message', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('231', '标识已读', '229', 'wechat', 'message', 'read', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('232', '删除消息', '229', 'wechat', 'message', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('233', '微信场景', '200', 'wechat', 'scan', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('234', '添加场景', '233', 'wechat', 'scan', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('235', '编辑场景', '233', 'wechat', 'scan', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('236', '删除场景', '233', 'wechat', 'scan', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('237', '素材管理', '200', 'wechat', 'material', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('238', '素材搜索', '237', 'wechat', 'material', 'search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('239', '添加素材', '237', 'wechat', 'material', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('240', '添加图文素材', '237', 'wechat', 'material', 'add_news', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('241', '删除素材', '237', 'wechat', 'material', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('242', '选择缩略图', '237', 'wechat', 'material', 'select_thumb', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('243', '获取永久素材列表', '237', 'wechat', 'material', 'get_material_list', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('244', '高级群发', '200', 'wechat', 'mass', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('245', '新建群发', '244', 'wechat', 'mass', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('246', '查询群发状态', '244', 'wechat', 'mass', 'select_status', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('247', '删除群发', '244', 'wechat', 'mass', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('248', '选择素材', '244', 'wechat', 'mass', 'select_material', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('249', '选择用户', '244', 'wechat', 'mass', 'select_user', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('250', '自定义表单', '3', 'diyform', 'diyform', 'init', '', '2', '1');
INSERT INTO `yzm_menu` VALUES ('251', '添加表单', '250', 'diyform', 'diyform', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('252', '编辑表单', '250', 'diyform', 'diyform', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('253', '删除表单', '250', 'diyform', 'diyform', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('254', '字段列表', '250', 'diyform', 'diyform_field', 'init', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('255', '添加字段', '254', 'diyform', 'diyform_field', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('256', '修改字段', '254', 'diyform', 'diyform_field', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('257', '删除字段', '254', 'diyform', 'diyform_field', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('258', '排序排序', '254', 'diyform', 'diyform_field', 'order', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('259', '表单信息列表', '250', 'diyform', 'diyform_info', 'init', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('260', '查看表单信息', '259', 'diyform', 'diyform_info', 'view', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('261', '删除表单信息', '259', 'diyform', 'diyform_info', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('262', '广告管理', '3', 'adver', 'adver', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('263', '添加广告', '262', 'adver', 'adver', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('264', '修改广告', '262', 'adver', 'adver', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('265', '删除广告', '262', 'adver', 'adver', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('266', '网站地图', '1', 'admin', 'sitemap', 'init', '', '16', '1');
INSERT INTO `yzm_menu` VALUES ('267', '生成地图', '266', 'admin', 'sitemap', 'make_sitemap', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('268', '导出模型', '54', 'admin', 'sitemodel', 'import', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('269', '导入模型', '54', 'admin', 'sitemodel', 'export', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('270', '导出配置', '61', 'admin', 'system_manage', 'user_config_export', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('271', '导入配置', '61', 'admin', 'system_manage', 'user_config_import', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('283', '支付模块', '3', 'pay', 'pay', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('284', '支付配置', '283', 'pay', 'pay', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('285', '订单管理', '2', 'member', 'order', 'init', '', '22', '1');
INSERT INTO `yzm_menu` VALUES ('286', '订单搜索', '285', 'member', 'order', 'order_search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('287', '订单改价', '285', 'member', 'order', 'change_price', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('288', '订单删除', '285', 'member', 'order', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('289', '订单详情', '285', 'member', 'order', 'order_details', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('290', '推送至百度', '30', 'admin', 'content', 'baidu_push', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('291', '内容属性变更', '30', 'admin', 'content', 'attribute_operation', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('292', '更改model', '69', 'admin', 'update_urls', 'change_model', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('293', '更新栏目URL', '69', 'admin', 'update_urls', 'update_category_url', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('294', '更新内容页URL', '69', 'admin', 'update_urls', 'update_content_url', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('295', '留言搜索', '79', 'guestbook', 'guestbook', 'search', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('296', '内容关键字', '3', 'admin', 'keyword_link', 'init', '', '1', '1');
INSERT INTO `yzm_menu` VALUES ('297', '添加关键字', '296', 'admin', 'keyword_link', 'add', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('298', '编辑关键字', '296', 'admin', 'keyword_link', 'edit', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('299', '删除关键字', '296', 'admin', 'keyword_link', 'del', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('300', '应用商店', '3', 'admin', 'store', 'init', '', '0', '1');
INSERT INTO `yzm_menu` VALUES ('301', '批量添加栏目', '24', 'admin', 'category', 'adds', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('302', '内容状态变更', '30', 'admin', 'content', 'status_operation', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('303', '关联内容', '65', 'admin', 'tag', 'content', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('304', '加入/移除Tag', '65', 'admin', 'tag', 'content_oper', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('305', '删除地图', '266', 'admin', 'sitemap', 'delete', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('306', '保存配置', '58', 'admin', 'system_manage', 'save', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('307', '立即备份', '35', 'admin', 'database', 'export_list', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('308', '复制内容', '30', 'admin', 'content', 'copy', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('309', '在线充值', '88', 'member', 'member', 'recharge', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('310', '登录到任意会员中心', '88', 'member', 'member', 'login_user', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('311', '友情链接排序', '134', 'link', 'link', 'order', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('312', '友情链接审核', '134', 'link', 'link', 'adopt', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('313', '标识已读', '79', 'guestbook', 'guestbook', 'set_read', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('314', '删除管理员回复', '79', 'guestbook', 'guestbook', 'del_reply', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('315', '轮播图排序', '156', 'banner', 'banner', 'order', '', '0', '0');
INSERT INTO `yzm_menu` VALUES ('316', '管理非自己发布的内容', '30', 'admin', 'content', 'all_content', '', '0', '0');

-- ----------------------------
-- Table structure for yzm_message
-- ----------------------------
DROP TABLE IF EXISTS `yzm_message`;
CREATE TABLE `yzm_message` (
  `messageid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `send_from` varchar(30) NOT NULL DEFAULT '' COMMENT '发件人',
  `send_to` varchar(30) NOT NULL DEFAULT '' COMMENT '收件人',
  `message_time` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` char(80) NOT NULL DEFAULT '' COMMENT '主题',
  `content` text NOT NULL,
  `replyid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复的id',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1正常0隐藏',
  `isread` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否读过',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统信息',
  PRIMARY KEY (`messageid`),
  KEY `replyid` (`replyid`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_message
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_message_data
-- ----------------------------
DROP TABLE IF EXISTS `yzm_message_data`;
CREATE TABLE `yzm_message_data` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `group_message_id` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '读过的信息ID',
  PRIMARY KEY (`id`),
  KEY `message` (`userid`,`group_message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_message_data
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_message_group
-- ----------------------------
DROP TABLE IF EXISTS `yzm_message_group`;
CREATE TABLE `yzm_message_group` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` varchar(20) NOT NULL DEFAULT '' COMMENT '用户组id',
  `subject` char(80) NOT NULL DEFAULT '',
  `content` text NOT NULL COMMENT '内容',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_message_group
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_model
-- ----------------------------
DROP TABLE IF EXISTS `yzm_model`;
CREATE TABLE `yzm_model` (
  `modelid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` char(30) NOT NULL DEFAULT '',
  `tablename` varchar(30) NOT NULL DEFAULT '',
  `alias` varchar(30) NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `setting` text,
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `items` smallint(5) unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isdefault` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`modelid`),
  KEY `siteid` (`siteid`,`type`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_model
-- ----------------------------
INSERT INTO `yzm_model` VALUES ('1', '0', '文章模型', 'article', 'article', '文章模型', '', '1466393786', '0', '0', '0', '0', '1', '1');
INSERT INTO `yzm_model` VALUES ('2', '0', '产品模型', 'product', 'product', '产品模型', '', '1466393786', '0', '0', '0', '0', '1', '0');
INSERT INTO `yzm_model` VALUES ('3', '0', '下载模型', 'download', 'download', '下载模型', '', '1466393786', '0', '0', '0', '0', '1', '0');
INSERT INTO `yzm_model` VALUES ('4', '0', '单页模型', 'page', 'page', '单页模型', '', '1683775806', '0', '0', '2', '0', '1', '0');

-- ----------------------------
-- Table structure for yzm_model_field
-- ----------------------------
DROP TABLE IF EXISTS `yzm_model_field`;
CREATE TABLE `yzm_model_field` (
  `fieldid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `modelid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `field` varchar(30) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `tips` varchar(100) NOT NULL DEFAULT '',
  `setting_catid` varchar(100) NOT NULL DEFAULT '',
  `minlength` int(10) unsigned NOT NULL DEFAULT '0',
  `maxlength` int(10) unsigned NOT NULL DEFAULT '0',
  `errortips` varchar(100) NOT NULL DEFAULT '',
  `fieldtype` varchar(20) NOT NULL DEFAULT '',
  `defaultvalue` varchar(30) NOT NULL DEFAULT '',
  `setting` text,
  `isrequired` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isunique` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isadd` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `listorder` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`fieldid`),
  KEY `modelid` (`modelid`,`disabled`),
  KEY `field` (`field`,`modelid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_model_field
-- ----------------------------
INSERT INTO `yzm_model_field` VALUES ('1', '0', 'title', '标题', '', '', '1', '100', '请输入标题', 'input', '', '', '1', '1', '0', '1', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('2', '0', 'catid', '栏目', '', '', '1', '10', '请选择栏目', 'select', '', '', '1', '1', '0', '1', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('3', '0', 'thumb', '缩略图', '', '', '0', '100', '', 'image', '', '', '0', '1', '0', '1', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('4', '0', 'keywords', '关键词', '', '', '0', '50', '', 'input', '', '', '0', '1', '0', '1', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('5', '0', 'description', '摘要', '', '', '0', '255', '', 'textarea', '', '', '0', '1', '0', '1', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('6', '0', 'inputtime', '发布时间', '', '', '1', '10', '', 'datetime', '', '', '1', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('7', '0', 'updatetime', '更新时间', '', '', '1', '10', '', 'datetime', '', '', '1', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('8', '0', 'copyfrom', '来源', '', '', '0', '30', '', 'input', '', '', '0', '1', '0', '1', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('9', '0', 'url', 'URL', '', '', '1', '100', '', 'input', '', '', '1', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('10', '0', 'userid', '用户ID', '', '', '1', '10', '', 'input', '', '', '1', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('11', '0', 'username', '用户名', '', '', '1', '30', '', 'input', '', '', '1', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('12', '0', 'nickname', '昵称', '', '', '0', '30', '', 'input', '', '', '0', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('13', '0', 'template', '模板', '', '', '1', '50', '', 'select', '', '', '1', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('14', '0', 'content', '内容', '', '', '1', '999999', '', 'editor', '', '', '1', '1', '0', '1', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('15', '0', 'click', '点击数', '', '', '1', '10', '', 'input', '0', '', '0', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('16', '0', 'tag', 'TAG', '', '', '0', '50', '', 'checkbox', '', '', '0', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('17', '0', 'readpoint', '阅读收费', '', '', '1', '5', '', 'input', '0', '', '0', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('18', '0', 'groupids_view', '阅读权限', '', '', '1', '10', '', 'checkbox', '1', '', '0', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('19', '0', 'status', '状态', '', '', '1', '2', '', 'checkbox', '', '', '1', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('20', '0', 'flag', '属性', '', '', '1', '16', '', 'checkbox', '', '', '1', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('21', '0', 'listorder', '排序', '', '', '1', '5', '', 'input', '1', '', '1', '1', '0', '0', '0', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('22', '2', 'brand', '品牌', '', '', '0', '30', '', 'input', '', '', '0', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('23', '2', 'standard', '型号', '', '', '0', '30', '', 'input', '', '', '0', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('24', '2', 'yieldly', '产地', '', '', '0', '50', '', 'input', '', '', '0', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('25', '2', 'pictures', '产品图集', '', '', '0', '1000', '', 'images', '', '', '0', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('26', '2', 'price', '单价', '请输入单价', '', '1', '10', '单价不能为空', 'decimal', '', '', '1', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('27', '2', 'unit', '价格单位', '', '', '1', '10', '', 'select', '', '{\"0\":\"件\",\"1\":\"斤\",\"2\":\"KG\",\"3\":\"吨\",\"4\":\"套\"}', '1', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('28', '2', 'stock', '库存', '库存量必须为数字', '', '1', '5', '库存不能为空', 'number', '99999', '', '1', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('29', '3', 'down_url', '下载地址', '', '', '1', '100', '下载地址不能为空', 'attachment', '', '', '1', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('30', '3', 'copytype', '授权形式', '', '', '0', '20', '', 'select', '', '{\"0\":\"免费版\",\"1\":\"正式版\",\"2\":\"共享版\",\"3\":\"试用版\",\"4\":\"演示版\",\"5\":\"注册版\",\"6\":\"破解版\"}', '0', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('31', '3', 'systems', '平台', '', '', '1', '30', '', 'select', '', '{\"0\":\"Windows\",\"1\":\"Linux\",\"2\":\"MacOS\"}', '1', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('32', '3', 'language', '语言', '', '', '0', '20', '', 'select', '', '{\"0\":\"简体中文\",\"1\":\"繁体中文\",\"2\":\"英文\",\"3\":\"多国语言\",\"4\":\"其他语言\"}', '0', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('33', '3', 'version', '版本', '', '', '1', '15', '版本号不能为空', 'input', '', '', '1', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('34', '3', 'filesize', '文件大小', '只输入数字即可，单位是字节', '', '0', '10', '', 'input', '', '', '0', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('35', '3', 'classtype', '软件类型', '', '', '1', '30', '', 'radio', '', '{\"0\":\"国产软件\",\"1\":\"国外软件\",\"2\":\"汉化补丁\",\"3\":\"程序源码\",\"4\":\"其他\"}', '1', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('36', '3', 'stars', '评分等级', '', '', '0', '20', '', 'radio', '', '{\"0\":\"1:1星\",\"1\":\"2:2星\",\"2\":\"3:3星\",\"3\":\"4:4星\",\"4\":\"5:5星\"}', '0', '0', '0', '1', '1', '0', '0', '1');
INSERT INTO `yzm_model_field` VALUES ('37', '4', 'introduce', '单页介绍', '', '0', '0', '100', '', 'textarea', '', '', '0', '0', '0', '0', '1', '0', '0', '1');

-- ----------------------------
-- Table structure for yzm_module
-- ----------------------------
DROP TABLE IF EXISTS `yzm_module`;
CREATE TABLE `yzm_module` (
  `module` varchar(15) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `iscore` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `version` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `setting` text,
  `listorder` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `installdate` date NOT NULL DEFAULT '2016-01-01',
  `updatedate` date NOT NULL DEFAULT '2016-01-01',
  PRIMARY KEY (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_module
-- ----------------------------
INSERT INTO `yzm_module` VALUES ('admin', '后台模块', '1', '3.0', '后台模块', '', '0', '0', '2016-08-27', '2023-05-12');
INSERT INTO `yzm_module` VALUES ('index', '前台模块', '1', '2.0', '前台模块', '', '0', '0', '2016-09-21', '2023-01-21');
INSERT INTO `yzm_module` VALUES ('api', '接口模块', '1', '2.0', '为整个系统提供接口', '', '0', '0', '2016-08-28', '2022-08-28');
INSERT INTO `yzm_module` VALUES ('install', '安装模块', '1', '2.0', 'CMS安装模块', '', '0', '0', '2016-10-28', '2022-10-28');
INSERT INTO `yzm_module` VALUES ('attachment', '附件模块', '1', '2.0', '附件模块', '', '0', '0', '2016-10-10', '2023-05-10');
INSERT INTO `yzm_module` VALUES ('member', '会员模块', '1', '3.0', '会员模块', '', '0', '0', '2016-09-21', '2023-02-21');
INSERT INTO `yzm_module` VALUES ('guestbook', '留言模块', '1', '2.0', '留言板模块', '', '0', '0', '2016-10-25', '2022-10-25');
INSERT INTO `yzm_module` VALUES ('search', '搜索模块', '1', '2.0', '搜索模块', '', '0', '0', '2016-11-21', '2023-01-21');
INSERT INTO `yzm_module` VALUES ('link', '友情链接', '0', '2.0', '友情链接模块', '', '0', '0', '2016-12-11', '2023-02-10');
INSERT INTO `yzm_module` VALUES ('comment', '评论模块', '1', '2.0', '全站评论', '', '0', '0', '2017-01-05', '2022-01-05');
INSERT INTO `yzm_module` VALUES ('mobile', '手机模块', '1', '2.0', '手机模块', '', '0', '0', '2017-04-05', '2022-04-05');
INSERT INTO `yzm_module` VALUES ('banner', '轮播图管理', '0', '2.0', '轮播图管理模块', '', '0', '0', '2017-05-12', '2023-02-10');
INSERT INTO `yzm_module` VALUES ('collection', '采集模块', '1', '1.0', '采集模块', '', '0', '0', '2017-08-16', '2022-08-16');
INSERT INTO `yzm_module` VALUES ('wechat', '微信模块', '1', '2.0', '微信模块', '', '0', '0', '2017-11-03', '2022-11-03');
INSERT INTO `yzm_module` VALUES ('diyform', '自定义表单模块', '1', '2.0', '自定义表单模块', '', '0', '0', '2018-01-15', '2023-05-11');
INSERT INTO `yzm_module` VALUES ('adver', '广告管理', '0', '2.0', '广告管理模块', '', '0', '0', '2018-01-18', '2023-01-18');
INSERT INTO `yzm_module` VALUES ('pay', '支付模块', '1', '1.0', '支付模块', '', '0', '0', '2018-07-03', '2022-07-03');

-- ----------------------------
-- Table structure for yzm_order
-- ----------------------------
DROP TABLE IF EXISTS `yzm_order`;
CREATE TABLE `yzm_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_sn` char(18) NOT NULL DEFAULT '' COMMENT '订单号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态0未付款1已付款',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '下单时间',
  `paytime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `paytype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付方式1支付宝2微信',
  `transaction` varchar(32) NOT NULL DEFAULT '' COMMENT '第三方交易单号',
  `money` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `quantity` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '数量',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1积分,2金钱',
  `ip` char(15) NOT NULL DEFAULT '',
  `desc` varchar(250) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `userid` (`userid`),
  KEY `order_sn` (`order_sn`),
  KEY `status` (`status`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='订单表';

-- ----------------------------
-- Records of yzm_order
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_page
-- ----------------------------
DROP TABLE IF EXISTS `yzm_page`;
CREATE TABLE `yzm_page` (
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `title` varchar(160) NOT NULL DEFAULT '',
  `introduce` varchar(255) NOT NULL DEFAULT '',
  `content` text,
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `catid` (`catid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_page
-- ----------------------------
INSERT INTO `yzm_page` VALUES ('4', '关于我们', '', '<p>YzmCMS是一款轻量级开源内容管理系统，它采用自主研发的框架YZMPHP开发。程序基于PHP+Mysql架构，并采用MVC框架式开发的一款高效开源的内容管理系统，可运行在Linux、Windows、MacOSX、Solaris等各种平台上。</p><p><br/></p><p>它可以让您不需要任何专业技术轻松搭建您需要的网站，操作简单，很容易上手，快捷方便的后台操作让您10分钟就会建立自己的爱站。在同类产品的比较中，YzmCMS更是凸显出了体积轻巧、功能强大、源码简洁、系统安全等特点，无论你是做企业网站、新闻网站、个人博客、门户网站、行业网站、电子商城等，它都能完全胜任，而且还提供了非常方便的二次开发体系，是一款全能型的建站系统！</p><p><br/></p><p>YzmCMS由2014年开始研发，从未停止更新，时至今日程序下载量累计超过100万次，已持续几万家企业和个人提供网站服务，每个版本都汇聚了自己的心血，力求每一个产品版本的发布，都要向前迈进，与时俱进。</p><p><br/></p><p><strong>YzmCMS官方QQ群号码：161208398</strong></p>', '1576509811');

-- ----------------------------
-- Table structure for yzm_pay
-- ----------------------------
DROP TABLE IF EXISTS `yzm_pay`;
CREATE TABLE `yzm_pay` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `trade_sn` char(18) NOT NULL DEFAULT '' COMMENT '订单号',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `money` char(8) NOT NULL DEFAULT '' COMMENT '金钱或积分的量',
  `creat_time` int(10) unsigned NOT NULL DEFAULT '0',
  `msg` varchar(30) NOT NULL DEFAULT '' COMMENT '类型说明',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1积分,2金钱',
  `ip` char(15) NOT NULL DEFAULT '',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '1成功,0失败',
  `remarks` varchar(250) NOT NULL DEFAULT '' COMMENT '备注说明',
  `adminnote` varchar(30) NOT NULL DEFAULT '' COMMENT '如是后台操作,管理员姓名',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `trade_sn` (`trade_sn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_pay
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_pay_mode
-- ----------------------------
DROP TABLE IF EXISTS `yzm_pay_mode`;
CREATE TABLE `yzm_pay_mode` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '',
  `logo` varchar(100) NOT NULL DEFAULT '',
  `desc` varchar(250) NOT NULL DEFAULT '',
  `config` text,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `author` varchar(60) NOT NULL DEFAULT '',
  `version` varchar(10) NOT NULL DEFAULT '',
  `action` varchar(30) NOT NULL DEFAULT '' COMMENT '支付调用方法',
  `template` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_pay_mode
-- ----------------------------
INSERT INTO `yzm_pay_mode` VALUES ('1', '支付宝', 'alipay.png', '支付宝新版在线支付插件，要求PHP版本>=5.5', '{\"app_id\":\"\",\"merchant_private_key\":\"\",\"alipay_public_key\":\"\"}', '1', '袁志蒙', '1.0', 'alipay', 'alipay');
INSERT INTO `yzm_pay_mode` VALUES ('2', '微信', 'wechat.png', '微信支付提供公众号支付、APP支付、扫码支付、刷卡支付等支付方式。', '{\\\"app_id\\\":\\\"\\\",\\\"app_secret\\\":\\\"\\\",\\\"mch_id\\\":\\\"\\\",\\\"key\\\":\\\"\\\"}', '0', '袁志蒙', '1.0', 'wechat', 'wechat');

-- ----------------------------
-- Table structure for yzm_pay_spend
-- ----------------------------
DROP TABLE IF EXISTS `yzm_pay_spend`;
CREATE TABLE `yzm_pay_spend` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `trade_sn` char(18) NOT NULL DEFAULT '' COMMENT '订单号',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `money` char(8) NOT NULL DEFAULT '' COMMENT '金钱或积分的量',
  `creat_time` int(10) unsigned NOT NULL DEFAULT '0',
  `msg` varchar(30) NOT NULL DEFAULT '' COMMENT '类型说明',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1积分,2金钱',
  `ip` char(15) NOT NULL DEFAULT '',
  `remarks` varchar(250) NOT NULL DEFAULT '' COMMENT '备注说明',
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`),
  KEY `trade_sn` (`trade_sn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_pay_spend
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_product
-- ----------------------------
DROP TABLE IF EXISTS `yzm_product`;
CREATE TABLE `yzm_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `userid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `nickname` varchar(30) NOT NULL DEFAULT '',
  `title` varchar(180) NOT NULL DEFAULT '',
  `color` char(9) NOT NULL DEFAULT '',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0',
  `keywords` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `click` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `copyfrom` varchar(50) NOT NULL DEFAULT '',
  `thumb` varchar(150) NOT NULL DEFAULT '',
  `url` varchar(100) NOT NULL DEFAULT '',
  `flag` varchar(12) NOT NULL DEFAULT '' COMMENT '1置顶,2头条,3特荐,4推荐,5热点,6幻灯,7跳转',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `listorder` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `groupids_view` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '阅读权限',
  `readpoint` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '阅读收费',
  `paytype` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '收费类型',
  `is_push` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否百度推送',
  `brand` varchar(50) NOT NULL DEFAULT '' COMMENT '品牌',
  `standard` varchar(100) NOT NULL DEFAULT '' COMMENT '型号',
  `yieldly` varchar(100) NOT NULL DEFAULT '' COMMENT '产地',
  `pictures` text COMMENT '产品图集',
  `price` decimal(8,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单价',
  `unit` varchar(30) NOT NULL DEFAULT '' COMMENT '价格单位',
  `stock` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  PRIMARY KEY (`id`),
  KEY `status` (`status`,`listorder`),
  KEY `catid` (`status`,`catid`),
  KEY `userid` (`status`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_product
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_tag
-- ----------------------------
DROP TABLE IF EXISTS `yzm_tag`;
CREATE TABLE `yzm_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `tag` varchar(30) NOT NULL DEFAULT '',
  `total` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `click` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `seo_title` varchar(100) NOT NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(200) NOT NULL DEFAULT '' COMMENT 'SEO关键字',
  `seo_description` varchar(255) NOT NULL DEFAULT '' COMMENT 'SEO描述',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `siteid_catid` (`siteid`,`catid`),
  KEY `siteid_tag` (`siteid`,`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_tag
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_tag_content
-- ----------------------------
DROP TABLE IF EXISTS `yzm_tag_content`;
CREATE TABLE `yzm_tag_content` (
  `siteid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `modelid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `aid` int(10) unsigned NOT NULL DEFAULT '0',
  `tagid` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `tag_index` (`modelid`,`aid`),
  KEY `tagid_index` (`siteid`,`tagid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_tag_content
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_urlrule
-- ----------------------------
DROP TABLE IF EXISTS `yzm_urlrule`;
CREATE TABLE `yzm_urlrule` (
  `urlruleid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '规则名称',
  `urlrule` varchar(100) NOT NULL DEFAULT '' COMMENT 'URL规则',
  `route` varchar(100) NOT NULL DEFAULT '' COMMENT '指向的路由',
  `listorder` tinyint(3) unsigned NOT NULL DEFAULT '50' COMMENT '优先级排序',
  PRIMARY KEY (`urlruleid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_urlrule
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_wechat_auto_reply
-- ----------------------------
DROP TABLE IF EXISTS `yzm_wechat_auto_reply`;
CREATE TABLE `yzm_wechat_auto_reply` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1关键字回复2自动回复3关注回复',
  `keyword` varchar(64) NOT NULL DEFAULT '' COMMENT '关键字回复的关键字',
  `keyword_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1完全匹配0模糊匹配',
  `relation_id` varchar(15) NOT NULL DEFAULT '' COMMENT '图文回复的关联内容ID',
  `content` text NOT NULL COMMENT '文本回复的内容',
  PRIMARY KEY (`id`),
  KEY `type_index` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_wechat_auto_reply
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_wechat_group
-- ----------------------------
DROP TABLE IF EXISTS `yzm_wechat_group`;
CREATE TABLE `yzm_wechat_group` (
  `id` mediumint(9) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_wechat_group
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_wechat_mass
-- ----------------------------
DROP TABLE IF EXISTS `yzm_wechat_mass`;
CREATE TABLE `yzm_wechat_mass` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `message_type` char(6) NOT NULL DEFAULT '' COMMENT '消息类型',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0通过openid群发1通过分组群发2全部',
  `media_id` varchar(200) NOT NULL DEFAULT '',
  `msg_id` varchar(10) NOT NULL DEFAULT '',
  `msg_data_id` varchar(10) NOT NULL DEFAULT '' COMMENT '图文消息的数据ID',
  `receive` varchar(255) NOT NULL DEFAULT '' COMMENT '按组群发为组id，否则为openid列表',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1任务提交成功2群发已结束',
  `masstime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_wechat_mass
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_wechat_media
-- ----------------------------
DROP TABLE IF EXISTS `yzm_wechat_media`;
CREATE TABLE `yzm_wechat_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `originname` varchar(50) NOT NULL DEFAULT '',
  `filename` varchar(50) NOT NULL DEFAULT '',
  `filepath` char(200) NOT NULL DEFAULT '',
  `type` char(6) NOT NULL DEFAULT '',
  `media_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0临时素材,1永久素材',
  `media_id` varchar(200) NOT NULL DEFAULT '',
  `created_at` int(10) unsigned NOT NULL DEFAULT '0',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '永久素材的图片url/图文素材标题',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_wechat_media
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_wechat_menu
-- ----------------------------
DROP TABLE IF EXISTS `yzm_wechat_menu`;
CREATE TABLE `yzm_wechat_menu` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `parentid` mediumint(6) NOT NULL DEFAULT '0',
  `name` varchar(48) NOT NULL DEFAULT '',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1关键字2跳转',
  `keyword` varchar(128) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `event` varchar(64) NOT NULL DEFAULT '',
  `listorder` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parentid` (`parentid`),
  KEY `listorder` (`listorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_wechat_menu
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_wechat_message
-- ----------------------------
DROP TABLE IF EXISTS `yzm_wechat_message`;
CREATE TABLE `yzm_wechat_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` char(100) NOT NULL DEFAULT '',
  `issystem` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统回复',
  `inputtime` int(10) unsigned NOT NULL DEFAULT '0',
  `msgtype` varchar(32) NOT NULL DEFAULT '' COMMENT '消息类型',
  `isread` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1已读0未读',
  `content` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `openid` (`openid`),
  KEY `issystem` (`issystem`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_wechat_message
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_wechat_scan
-- ----------------------------
DROP TABLE IF EXISTS `yzm_wechat_scan`;
CREATE TABLE `yzm_wechat_scan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scan` varchar(65) NOT NULL DEFAULT '' COMMENT '场景',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0永久,1临时',
  `expire_time` char(7) NOT NULL DEFAULT '0' COMMENT '二维码有效时间',
  `ticket` varchar(150) NOT NULL DEFAULT '',
  `remarks` varchar(255) NOT NULL DEFAULT '' COMMENT '场景备注',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_wechat_scan
-- ----------------------------

-- ----------------------------
-- Table structure for yzm_wechat_user
-- ----------------------------
DROP TABLE IF EXISTS `yzm_wechat_user`;
CREATE TABLE `yzm_wechat_user` (
  `wechatid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `openid` char(100) NOT NULL DEFAULT '',
  `groupid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `subscribe` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1关注0取消',
  `nickname` varchar(50) NOT NULL DEFAULT '',
  `sex` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `city` char(50) NOT NULL DEFAULT '',
  `province` char(50) NOT NULL DEFAULT '',
  `country` char(50) NOT NULL DEFAULT '',
  `headimgurl` char(255) NOT NULL DEFAULT '',
  `subscribe_time` int(10) unsigned NOT NULL DEFAULT '0',
  `remark` varchar(50) NOT NULL DEFAULT '',
  `scan` varchar(30) NOT NULL DEFAULT '' COMMENT '来源场景',
  PRIMARY KEY (`wechatid`),
  UNIQUE KEY `openid` (`openid`),
  KEY `groupid` (`groupid`),
  KEY `subscribe` (`subscribe`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yzm_wechat_user
-- ----------------------------