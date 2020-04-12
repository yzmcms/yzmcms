<?php
/**
 * YzmCMS 安装向导
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-01-15
 */
 
header('Content-Type:text/html;charset=utf-8'); 

define('IN_YZMCMS', true);

date_default_timezone_set('PRC');
error_reporting(E_ALL & ~E_NOTICE);

define('APPDIR', _dir_path(substr(dirname(__FILE__), 0, -8)));
define('SITEDIR', dirname(APPDIR).DIRECTORY_SEPARATOR);
define("VERSION", 'YzmCMS 5.6');

if(is_file(SITEDIR.'cache'.DIRECTORY_SEPARATOR.'install.lock')){
    exit("YzmCMS程序已运行安装，如果你确定要重新安装，请先从FTP中删除 cache/install.lock！");
}

@set_time_limit(1000);

if (version_compare(PHP_VERSION,'5.2.0','<')) exit('您的php版本过低，不能安装本软件，请升级到5.2.0或更高版本再安装，谢谢！');

$sqlFile = 'database.sql';

if (!is_file(APPDIR . 'install/' . $sqlFile)) {
    echo '缺少数据库文件!';
    exit;
}
$Title = "安装程序";
$Powered = "YzmCMS内容管理系统";
$steps = array(
    '1' => '安装许可协议',
    '2' => '运行环境检测',
    '3' => '安装参数设置',
    '4' => '安装详细过程',
    '5' => '安装完成',
);
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

//地址
$scriptName = !empty($_SERVER["REQUEST_URI"]) ? $scriptName = $_SERVER["REQUEST_URI"] : $scriptName = $_SERVER["PHP_SELF"];
$rootpath = @preg_replace("/\/application\/(I|i)nstall\/index\.php(.*)$/", "", $scriptName);
$domain = empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
if ((int) $_SERVER['SERVER_PORT'] != 80) {
    $domain .= ":" . $_SERVER['SERVER_PORT'];
}
$domain = $domain . $rootpath;

switch ($step) {

    case '1':
        include ("./templates/s1.php");
        exit();

    case '2':

        if (phpversion() < 5) {
            die('本系统需要PHP5+MYSQL >=5.0环境，当前PHP版本为：' . phpversion());
        }

        $phpv = @ phpversion();
        $os = PHP_OS;
        $os = php_uname();
        $tmp = function_exists('gd_info') ? gd_info() : array();
        $server = $_SERVER["SERVER_SOFTWARE"];
        $host = (empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"]);
        $name = $_SERVER["SERVER_NAME"];
        $max_execution_time = ini_get('max_execution_time');
        $allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $allow_url_fopen = (ini_get('allow_url_fopen') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $safe_mode = (ini_get('safe_mode') ? '<font color=red>[×]On</font>' : '<font color=green>[√]Off</font>');

        $err = 0;
        if (empty($tmp['GD Version'])) {
            $gd = '<span class="correct_span error_span">&radic;</span> 未开启';
            $err++;
        } else {
            $gd = '<span class="correct_span">&radic;</span> 已开启';
        }
        if (class_exists('pdo')) {
            $mysql = '<span class="correct_span">&radic;</span> 已安装PDO扩展';
        } else {
			//如果没有安装PDO扩展，在检查是否安装MYSQLI扩展
			if(class_exists('mysqli')){
				$mysql = '<span class="correct_span">&radic;</span> 已安装MYSQLI扩展';
			}else{
				$mysql = '<span class="correct_span error_span">&radic;</span> 未安装PDO和MYSQLI';
				$err++;
			}
        }
        if (ini_get('file_uploads')) {
            $uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
        } else {
            $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
        }
        if (function_exists('session_start')) {
            $session = '<span class="correct_span">&radic;</span> 已开启';
        } else {
            $session = '<span class="correct_span error_span">&radic;</span> 未开启';
            $err++;
        }
		if(function_exists('curl_init')){
			$curl = '<span class="correct_span">&radic;</span> 已开启';
		}else{
			$curl = '<span class="correct_span error_span">&radic;</span> 未开启';
            $err++;
		}
        $folder = array('cache','uploads','common');
        include ("./templates/s2.php");
        exit();

    case '3':
		//ajax测试链接数据库
        if (isset($_GET['testdbpwd'])) {
            $conn = @mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpw'], null, intval($_POST['dbport']));
            die($conn ? '1' : '0');
        }
        include ("./templates/s3.php");
        exit();


    case '4':
        if (isset($_GET['install'])) {
            $n = isset($_GET['n']) ? intval($_GET['n']) : 0;
            $arr = array();

            $dbType = trim($_POST['dbtype']);
            $dbHost = trim($_POST['dbhost']);
            $dbPort = intval(trim($_POST['dbport']));
            $dbName = trim($_POST['dbname']);
            $dbUser = trim($_POST['dbuser']);
            $dbPwd = trim($_POST['dbpw']);
            $dbPrefix = empty($_POST['dbprefix']) ? 'yzm_' : str_replace(array('"','\'',','), '', trim($_POST['dbprefix']));
            $adminname = trim($_POST['manager_adminname']);
            $password = trim($_POST['manager_pwd']);
            $config = array();

            $config['db_type'] = in_array($dbType, array('pdo', 'mysqli', 'mysql')) ? $dbType : 'pdo';
            $config['db_host'] = $dbHost;
            $config['db_name'] = $dbName;
            $config['db_user'] = $dbUser;
            $config['db_pwd'] = $dbPwd;
            $config['db_port'] = $dbPort;
            $config['db_prefix'] = $dbPrefix;

            $conn = @mysqli_connect($dbHost, $dbUser, $dbPwd, null, $dbPort);
            if (!$conn) {
                $arr['msg'] = "连接数据库失败!"; 
                die(json_encode($arr));
            }
            mysqli_query($conn, "SET NAMES utf8"); 

            if (!mysqli_select_db($conn, $dbName)) {
                //创建数据时同时设置编码
                if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` DEFAULT CHARACTER SET utf8;")) {
                    $arr['msg'] = '数据库 ' . $dbName . ' 不存在，也没权限创建新的数据库！';
                    die(json_encode($arr));
                }
                if (!$n) {
                    $arr['n'] = 1;
                    $arr['msg'] = "成功创建数据库:{$dbName}<br>";
					
                    die(json_encode($arr));
                }
                mysqli_select_db($conn, $dbName);
            }

            //读取数据文件
            $sqldata = file_get_contents(APPDIR . 'install/' . $sqlFile);
            $sqlFormat = sql_split($sqldata, $dbPrefix);


            /**
            执行SQL语句
             */
            $counts = count($sqlFormat);

            for ($i = $n; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
                if(empty($sql)) continue;

                if (strstr($sql, 'CREATE TABLE')) {
                    preg_match('/CREATE TABLE `([^ ]*)`/', $sql, $matches);
                    //mysqli_query($conn, "DROP TABLE IF EXISTS `$matches[1]");
                    $ret = mysqli_query($conn, $sql);
                    if ($ret) {
                        $message = '<li><span class="correct_span">&radic;</span>创建数据表' . $matches[1] . '，完成</li> ';
                    } else {
                        $message = '<li><span class="correct_span error_span">&radic;</span>创建数据表' . $matches[1] . '，失败</li>';
                    }
                    $i++;
                    $arr = array('n' => $i, 'msg' => $message);
                    die(json_encode($arr));
                } else {
                    $ret = mysqli_query($conn, $sql);
                    $message = '';
                    $arr = array('n' => $i, 'msg' => $message);
                    //echo json_encode($arr); exit;
                }
            }

            if ($i == 999999)
                exit;

            //插入管理员
            $password = md5(substr(md5(trim($password)), 3, 26));
            $time = time();
            $query = "INSERT INTO `{$dbPrefix}admin` VALUES ('1', '{$adminname}', '{$password}', '1', '超级管理员', '', '', '', '0', '', '{$time}', '系统')";
            $ret = mysqli_query($conn, $query);
			if($ret){
				$message = '添加管理员成功';
			}else{
				$message = '<span style="color:red">添加管理员失败！</span>';
			}
			
			//插入网站配置
			$sitename = trim($_POST['sitename']);
            $siteurl = trim($_POST['siteurl']);
			$query = "UPDATE {$dbPrefix}config  SET value='{$sitename}' WHERE id=1";
			mysqli_query($conn, $query);
			$query = "UPDATE {$dbPrefix}config  SET value='{$siteurl}' WHERE id=2";
            mysqli_query($conn, $query);
			
			//写入配置文件
            set_config($config);
			$message .= '<br />成功写入配置文件<br>安装完成．';
            $arr = array('n' => 999999, 'msg' => $message);
            die(json_encode($arr));
        }

        include ("./templates/s4.php");
        exit;

    case '5':
        include ("./templates/s5.php");
        @touch(SITEDIR.'cache'.DIRECTORY_SEPARATOR.'install.lock');
        if(is_file(SITEDIR.'install.php')) @unlink(SITEDIR.'install.php');
		if(is_file(SITEDIR.'index.html')) @unlink(SITEDIR.'index.html');
        exit;
}

function testwrite($d) {
    $tfile = "yzmcms_test.txt";
    $fp = @fopen($d . "/" . $tfile, "w");
    if (!$fp) {
        return false;
    }
    fclose($fp);
    $rs = @unlink($d . "/" . $tfile);
    if ($rs) {
        return true;
    }
    return false;
}

function sql_split($sql, $tablepre) {
    if ($tablepre != "yzm_")
        $sql = str_replace("yzm_", $tablepre, $sql);
    $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $query) {
        $ret[$num] = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '#' && $str1 != '-')
                $ret[$num] .= $query;
        }
        $num++;
    }
    return $ret;
}

function _dir_path($path) {
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) != '/')
        $path = $path . '/';
    return $path;
}

function set_config($config) {
	$configfile = SITEDIR.'common'.DIRECTORY_SEPARATOR.'config/config.php';
	if(!is_writable($configfile)) die('Please chmod '.$configfile.' to 0777 !');
	$pattern = $replacement = array();
	$config['auth_key'] = random(32);
	foreach($config as $k=>$v) {
		$v = trim($v);
		$configs[$k] = $v;
		$pattern[$k] = "/'".$k."'\s*=>\s*([']?)[^']*([']?)(\s*),/is";
		$replacement[$k] = "'".$k."' => \${1}".$v."\${2}\${3},";					
	}
	$str = file_get_contents($configfile);
	$str = preg_replace($pattern, $replacement, $str);
	return file_put_contents($configfile, $str, LOCK_EX);		
}

function random($length, $chars = '1294567890abcdefghigklmnopqrstuvwxyzABCDEFGHIGKLMNOPQRSTUVWXYZ') {
	$hash = '';
	$max = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}
?>