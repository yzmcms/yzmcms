<?php
// +----------------------------------------------------------------------
// | Site:  [ http://www.yzmcms.com]
// +----------------------------------------------------------------------
// | Copyright: 袁志蒙工作室，并保留所有权利
// +----------------------------------------------------------------------
// | Author: YuanZhiMeng <214243830@qq.com>
// +---------------------------------------------------------------------- 
// | Explain: 这不是一个自由软件,您只能在不用于商业目的的前提下对程序代码进行修改和使用，不允许对程序代码以任何形式任何目的的再发布！
// +----------------------------------------------------------------------

/**
 * 设置config文件
 * @param $config 配置信息
 */
function set_config($config) {
	$configfile = YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.'config/config.php';
	if(!is_writable($configfile)) return_message('Please chmod '.$configfile.' to 0777 !', 0);
	$pattern = $replacement = array();
	foreach($config as $k=>$v) {
		$v = str_replace(array(',','$','/'), '', $v);
		$pattern[$k] = "/'".$k."'\s*=>\s*([']?)[^']*([']?)(\s*),/is";
		$replacement[$k] = "'".$k."' => \${1}".$v."\${2}\${3},";					
	}
	$str = file_get_contents($configfile);
	$str = preg_replace($pattern, $replacement, $str);
	return file_put_contents($configfile, $str, LOCK_EX);		
}


/**
 * 显示后台菜单
 */
function show_menu() {
	if(!$menu_string = getcache('menu_string_'.$_SESSION['roleid'])){
		$menu_list = get_menu();
		$menu_string = '';
		foreach($menu_list as $key => $val){
			$s1 = $key ==0 ? ' class="selected"' : '';
			$s2 = $key ==0 ? ' style="display:block;"' : '';
			$menu_string .= '<div class="menu_dropdown">
			<dl id="'.$val['id'].'-menu">
				<dt'.$s1.'><i class="yzm-iconfont '.$val['data'].' mr-5"></i> '.$val['name'].'<i class="yzm-nav-icon yzm-iconfont yzm-iconxiangxia menu_dropdown-arrow"></i></dt>
				<dd'.$s2.'>
					<ul>';
						foreach($val['child'] as $v){
							$menu_string .= '<li><a href="javascript:void(0)" _href="'.url($v['m'].'/'.$v['c'].'/'.$v['a'], $v['data']).'" data-title="'.$v['name'].'">'.$v['name'].'</a></li>';
						}					
					$menu_string .= '</ul>
				</dd>
			</dl>
			</div>';
		}
		setcache('menu_string_'.$_SESSION['roleid'], $menu_string);
	}	
	return $menu_string;
}


/**
 * 获取菜单
 */
function get_menu(){
	$menu_list = D('menu')->field('`id`,`name`,`m`,`c`,`a`,`data`')->where(array('parentid'=>'0','display'=>'1'))->order('listorder ASC,id ASC')->limit('20')->select();
	foreach ($menu_list as $key => $value) {
		$child = D('menu')->field('`id`,`name`,`m`,`c`,`a`,`data`')->where(array('parentid'=>$value['id'],'display'=>'1'))->order('listorder ASC,id ASC')->select();
		foreach ($child as $k=>$v) {
			if($_SESSION['roleid'] != 1){
				$data = D('admin_role_priv')->field('roleid')->where(array('roleid'=>$_SESSION['roleid'], 'm'=>$v['m'], 'c'=>$v['c'], 'a'=>$v['a']))->find();
				if(!$data) unset($child[$k]);
			}
		}
		if($child){
			$menu_list[$key]['child'] = $child;
		}else{
			unset($menu_list[$key]);
		}
	}
	
	return array_values($menu_list);
}



function url($url='', $vars='') {	
	$url = trim($url, '/');
	$arr = explode('/', $url);
	$string = SITE_PATH;
	if(URL_MODEL == 0){
		$string .= 'index.php?';
		$string .= 'm='.$arr[0].'&c='.$arr[1].'&a='.$arr[2];
		if($vars){
			if(is_array($vars)) $vars = http_build_query($vars);
			$string .= '&'.$vars;
		}
	}else{
		if(URL_MODEL == 1) $string .= 'index.php?s=';
		if(URL_MODEL == 4) $string .= 'index.php/';
		$string .= $url;
		if($vars){
			if(!is_array($vars)) parse_str($vars, $vars);			
            foreach ($vars as $var => $val){
                if(trim($val) !== '') $string .= '/'.$var.'/'.$val;
            } 
		}
        $string .= C('url_html_suffix');		
	}

	return $string;
}


function downfile($url, $md5){
    if (extension_loaded('curl')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $content = curl_exec($ch);
        curl_close($ch);
    } else {
        $content = @file_get_contents($url);
    }

    if(!$content) return array('status'=>0, 'message'=>'升级包不存在，请重试！');

    $filename = explode('/', $url);    
    $filename = end($filename);
    $downname = YZMPHP_PATH.'cache/down_package/'.$filename;
    $fp = fopen($downname, 'w');
    fwrite($fp, $content);
    fclose($fp);

    if(!is_file($downname)) return array('status'=>0, 'message'=>'下载文件失败，请检查目录权限！');
    if($md5 != md5_file($downname)) return array('status'=>0, 'message'=>'升级包损坏，请重新下载！');

    return array('status'=>1, 'message'=>'下载成功！', 'file_path'=>$downname);
}


function unzips($filename, $unzip_folder){
    if(!is_file($filename)) return array('status'=>0, 'message'=>'将解压的文件不存在！');

    $zip = new ZipArchive();
    if (!$zip->open($filename)) {
        return array('status'=>0, 'message'=>'打开升级包失败！');
    }

    $zip->extractTo($unzip_folder);
    $zip->close();
    return array('status'=>1, 'message'=>'解压成功！');
}


function exec_sql($sqlstr) {
    $sqlstr = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sqlstr);
    $sqlstr = str_replace("\r", "\n", $sqlstr);
    $queriesarray = explode(";\n", trim($sqlstr));
    $admin = D('admin');
    foreach ($queriesarray as $query) {
        $query_sql = '';
        $queries = explode("\n", trim($query));
        $queries = array_filter($queries);
        foreach ($queries as $query) {
            $str1 = substr($query, 0, 1);
            if ($str1 != '-' && $str1 != '#') $query_sql .= $query;
        }
        if(!$query_sql) continue;
        $result = $admin->query($query_sql);
        if(!$result) {
        	write_log($query_sql);
        	return false;
        }
    }
    return true;
}


function copy_file($source, $objective){
	static $fail = 0;
	$dir = opendir($source);
	while ($file = readdir($dir)) {
	    if($file!='.' && $file!='..') {
	        if (is_dir($source.'/'.$file)) {
	            copy_file($source.'/'.$file, $objective.'/'.$file);
	        }else{
	            if(!is_dir($objective)) mkdir($objective, 0777, true);
                $res = @copy($source.'/'.$file, $objective.'/'.$file);
                if($res) {
                    @unlink($source.'/'.$file);
                } else {
                    $fail++;
                }
	        }
	    }
	}
	closedir($dir);
	return $fail;
}


function del_dir($path){
	if (is_dir($path)) {
	    $file_list = scandir($path);
	    foreach ($file_list as $file) {
	        if ($file != '.' && $file != '..') {
	            del_dir($path . '/' . $file); 
	        }
	    }
	    return @rmdir($path);
	} else if (is_file($path)) {
	    @unlink($path);
	}
}