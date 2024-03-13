<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
return array(

    //系统配置
    'auth_key'           => 'TEZTYUQ0N1BZVmGCW4BeFSrmLsoq8ENh',    //系统密钥
    'error_page'         => '404.html',    //错误提示页面，非调试模式有效
    'error_log_save'     => true,          //是否保存系统错误日志，非调试模式有效
    'site_theme'         => 'default',     //站点默认主题目录
    'url_html_suffix'    => '.html',       //URL伪静态后缀
    'set_pathinfo'       => false,         //Nginx默认不支持PATHINFO模式，需配置此项为true，则Nginx可支持PATHINFO，系统默认为false
    
    //数据库配置
    'db_type'            => 'pdo',          // 数据库链接扩展 , 支持 pdo | mysqli | mysql
    'db_host'            => '127.0.0.1',    // 服务器地址
    'db_name'            => 'yzmcms',       // 数据库名
    'db_user'            => 'root',         // 用户名
    'db_pwd'             => '123456',       // 密码
    'db_port'            => 3306,           // 端口
    'db_prefix'          => 'yzm_',         // 数据库表前缀
    
    //路由配置
    'route_config'       => array (
        'default'        => array('m'=>'index', 'c'=>'index', 'a'=>'init')
    ),
    'route_mapping'      => true,         //是否开启路由映射
    //路由映射规则
    'route_rules'        => array(),
    
    //Cookie配置
    'cookie_domain'      => '',           //Cookie 作用域
    'cookie_path'        => '/',          //Cookie 作用路径
    'cookie_ttl'         => 0,            //Cookie 生命周期，0 表示随浏览器进程
    'cookie_pre'         => 'yzmphp_',    //Cookie 前缀，同一域名下安装多套系统时，请修改Cookie前缀
    'cookie_secure'      => false,        //是否通过安全的 HTTPS 连接来传输Cookie
    'cookie_httponly'    => false,        //是否仅可通过 HTTP 协议访问Cookie
    
    //缓存配置
    'cache_type'         => 'file',         // 缓存类型，支持 file , redis , memcache
    //缓存类型为file缓存时的配置项
    'file_config'        => array (
        'cache_dir'      => YZMPHP_PATH.'cache/cache_file/',    //缓存文件目录
        'suffix'         => '.cache.php',  //缓存文件后缀
        'mode'           => '2',           //缓存格式：mode 1 为serialize序列化, mode 2 为保存为可执行文件array
    ), 
    //缓存类型为redis缓存时的配置项
    'redis_config'       => array (
        'host'           => '127.0.0.1',    // redis主机
        'port'           => 6379,           // redis端口
        'password'       => '',             // 密码
        'select'         => 0,              // 操作库
        'timeout'        => 0,              // 超时时间(秒)
        'expire'         => 3600,           // 有效期(秒)
        'persistent'     => false,          // 是否长连接
        'prefix'         => '',             // 前缀
    ), 
    //缓存类型为memcache缓存时的配置项
    'memcache_config'    => array (
        'host'           => '127.0.0.1',    // memcache主机
        'port'           => 11211,          // memcache端口
        'timeout'        => 0,              // 超时时间(秒)
        'expire'         => 3600,           // 有效期(秒)
        'persistent'     => false,          // 是否长连接
        'prefix'         => '',             // 前缀
    ),
	
    //队列配置
    'queue_connection'   => 'database',      //队列驱动类型，支持 database 和 redis
    'queue_name'         => 'default',       //队列名称
    
    //系统语言
    'language'           => 'zh_cn',      //支持 简体中文zh_cn 和 美式英语en_us
    
    //附件相关配置
    'upload_type'        => 'host',       //文件上传类型，host:本地, qiniu:七牛云, aliyun:阿里云, tencent:腾讯云
    'upload_file'        => 'uploads',    //上传文件目录，后面一定不要加斜杠（“/”）
    'watermark_enable'   => 0,            //是否开启图片水印
    'watermark_name'     => 'mark.png',   //水印名称
    'watermark_position' => '9',          //水印位置
    
    //其他设置
    'sql_execute'        => false,        //是否允许在线执行SQL命令
    'edit_template'      => false,        //是否允许在线编辑模板

);