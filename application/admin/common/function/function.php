<?php

/**
 * 设置config文件
 * @param $config 配置信息
 */
function set_config($config) {
	$configfile = YZMPHP_PATH.'common'.DIRECTORY_SEPARATOR.'config/config.php';
	if(!is_writable($configfile)) showmsg('Please chmod '.$configfile.' to 0777 !', 'stop');
	$pattern = $replacement = array();
	foreach($config as $k=>$v) {
		$v = str_replace(array(',','$'), '', $v);
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
			$s = $key ==0 ? ' style="display:block;"' : '';
			$menu_string .= '<div class="menu_dropdown">
			<dl id="'.$val['id'].'-menu">
				<dt class="selected"><i class="Hui-iconfont">'.$val['data'].'</i> '.$val['name'].'<i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
				<dd'.$s.'>
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
	
	return $menu_list;
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