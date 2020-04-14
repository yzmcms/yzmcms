<?php
/**
 * system.func.php   系统函数库
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-01-18
 */


/**
 * 获取模板主题列表
 * @param string $m 模块
 * @return array
 */
function get_theme_list($m = 'index'){
	$theme_list = array();
	$list = glob(APP_PATH.$m.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
	 
	foreach($list as $v){	 
		$theme_list[] = basename($v);
	}
	
	return $theme_list;
}


/**
 * 获取系统配置信息
 * @param $key 键值，可为空，为空获取整个数组
 * @return array|string
 */
function get_config($key = ''){
	if(!$configs = getcache('configs')){
		$data = D('config')->where(array('status'=>1))->select();
		$configs = array();
		foreach($data as $val){
			$configs[$val['name']] = $val['value'];
		}
		setcache('configs', $configs);
	}
    if(!$key){
		return $configs;
	}else{
		return array_key_exists($key, $configs) ? $configs[$key] : '';
	}	
}


/**
 * 获取内容页URL 
 * @param $catid 
 * @param $id 
 */
function get_content_url($catid, $id){
	$catinfo = get_category($catid);
	if(get_config('url_mode')){
		return get_config('site_url').$catinfo['catdir'].'/'.$id.C('url_html_suffix');
	}
	return SITE_PATH.$catinfo['catdir'].'/'.$id.C('url_html_suffix');
}


/**
 * 单页面标签，用于在首页或频道页调取单页的简介...
 * @param $catid
 * @param $limit
 * @param $field
 * @return string
 */
function page_content($catid = 1, $limit = 300, $field = 'content'){
	global $catpage;
	$catpage = isset($catpage) ? $catpage : D('page');
	$string = $catpage->where(array('catid'=>$catid))->field($field)->find();
	$string = $string ? str_cut(strip_tags($string[$field]), $limit) : '';
	return $string;	
}


/**
 * 获取模型信息
 * @param $typeall    0只包含内容模型，1全部模型
 * @return array
 */
function get_modelinfo($typeall = 0){
	$where = array('disabled' => 0);
	if($typeall == 0){
		$filename = 'modelinfo';
		$where['type'] = 0;
	}else{
		$filename = 'modelinfo_all';
	}
	
	if(!$modelinfo = getcache($filename)){
		$modelinfo = D('model')->where($where)->order('modelid ASC')->select();
		setcache($filename, $modelinfo);
	}
	return $modelinfo;	
}


/**
 * 发送邮件    必须做好配置邮箱
 * @param $email    收件人邮箱
 * @param $title    邮件标题
 * @param $content     邮件内容
 * @param $mailtype    邮件内容类型
 * @return true or false
 */
function sendmail($email, $title = '', $content = '', $mailtype = 'HTML'){
	$mail_pass = get_config('mail_pass');
	if(!is_email($email)) return false;
	yzm_base::load_sys_class('smtp', '', 0);
	$smtp = new smtp(get_config('mail_server'),get_config('mail_port'),get_config('mail_auth'),get_config('mail_user'),get_config('mail_pass'));
	$state = $smtp->sendmail($email, get_config('mail_from'), $title.' - '.get_config('site_name'), $content, $mailtype);
	if(!$state) return false;
	return true;
}


/**
 * 返回json数组，默认提示 “数据未修改！” 
 * @param $arr
 * @return string
 */
function return_json($arr = array()){
    header('Content-Type:application/json; charset=utf-8');
	if(!$arr) exit(json_encode(array('status'=>0,'message'=>L('data_not_modified'))));
	exit(json_encode($arr));	
}


/**
 * 判断是否为手机访问
 * @return bool
 */
function ismobile(){
    if(isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        return true;
    }
    if(isset ($_SERVER['HTTP_USER_AGENT'])){
        $clientkeywords = array ('android','iphone','ipod','mobile','comfront','midp','symbianos','wap');
        foreach($clientkeywords as $value){
            if(stristr($_SERVER['HTTP_USER_AGENT'], $value)) return true;
        }
    }
    return false;
}


/**
 * 返回附件类型图标
 * @param $file 附件名称
 */
function file_icon($file){
	$ext_arr = array('doc','docx','ppt','xls','txt','pdf','mdb','jpg','gif','png','bmp','jpeg','rar','zip','swf','flv');
	$ext = fileext($file);
	if(in_array($ext,$ext_arr)) return STATIC_URL.'images/ext/'.$ext.'.gif';
	else return STATIC_URL.'images/ext/hlp.gif';
}


/**
 * 会员登录跳转url
 * @param $referer
 * @return string
 */
function url_referer($referer){
	
	$referer = urlencode($referer);
	if(URL_MODEL != 0) return U('member/index/login').'?referer='.$referer;	
	return U('member/index/login').'&referer='.$referer;
}


/**
 * 广告调用
 * @param $id
 * @return string
 */
function adver($id){
	
	global $adver;
	$adver = isset($adver) ? $adver : D('adver');
	$data = $adver->field('start_time, end_time, code')->where(array('id'=>$id))->find();
	if(!$data)  return '广告位不存在！';
	if($data['start_time']!=0 && $data['start_time']>SYS_TIME){
		return '广告未开始！';
	}
	
	if($data['end_time']!=0 && $data['end_time']<SYS_TIME){
		return '广告已过期！';
	}
	return $data['code'];
}


/**
 * 生成Tag URL
 * @param $tid
 * @return string
 */
function tag_url($tid){
	return U('search/index/tag',array('id'=>$tid));
}


/**
 * 生成手机版内容URL
 * @param $catid
 * @param $id
 * @return string
 */
function mobile_url($catid, $id){
	return U('mobile/index/show', array('catid'=>$catid,'id'=>$id));
}


/**
 * 渲染title颜色
 * @param $title
 * @param $color
 * @return string
 */
function title_color($title, $color = ''){
	if(!$color) return '<span class="title_color">'.$title.'</span>';
	return '<span class="title_color" style="color:'.$color.'">'.$title.'</span>';
}


/**
 * 获取内容缩略图
 * @param $thumb
 * @param $default
 * @return string
 */
function get_thumb($thumb, $default = ''){
	if($thumb) return $thumb;
	return $default ? $default : STATIC_URL.'images/nopic.jpg';
}


/**
 * 获取栏目的select
 * @param $name     select的名称
 * @param $value    选中的id，用于修改
 * @param $root     顶级分类名称
 * @param $member_publish 是否仅显示投稿栏目
 * @param $attribute 外加属性
 * @param $parent_disabled 是否禁父级栏目
 * @param $disabled 是否禁单页和外部链接
 * @param $modelid  modelid
 * @return string
 */
function select_category($name="parentid", $value="0", $root="", $member_publish=0, $attribute='', $parent_disabled=true, $disabled=true, $modelid=0){
	if($root == '') $root="≡ 作为一级栏目 ≡";
	$categorys = array();
	$html='<select id="select" name="'.$name.'" class="select" '.$attribute.'>';
	$html.='<option value="0">'.$root.'</option>';
	
	if($member_publish){
		$where = array('member_publish'=>1);
		$data = D('category')->field('catid,arrparentid')->where($where)->select(); 
		$arr = array();
		foreach($data as $val){
			$arr[$val['catid']] = $val['arrparentid'];
		}
		$arr = array_merge(array_unique(explode(',', join(',', $arr))), array_keys($arr));		
	}

	$where = array();
	if($modelid) $where['modelid'] = $modelid;
	$tree = yzm_base::load_sys_class('tree');
	$data = D('category')->field('catid AS id,catname AS name,parentid,arrchildid,type')->where($where)->select(); 
	foreach($data as $val){
		if($member_publish){
			if(!in_array($val['id'], $arr)) continue;
		}
		
		$val['html_disabled'] = 0;
		if($parent_disabled){
			if($val['id'] != $val['arrchildid']) $val['html_disabled'] = 1;
		}
		if($disabled){
			if($val['type'] != 0) $val['html_disabled'] = 1;
		} 
		$categorys[$val['id']] = $val;
	}
	$tree->init($categorys);
	$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
	$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
	$html .= $tree->get_tree_category(0, "<option value='\$id' \$selected>\$spacer\$name</option>", "<optgroup label='\$spacer \$name'></optgroup>", $value);

	$html .= '</select>';
	return $html;
}


/**
 * 获取栏目信息
 *
 * @param  int $catid
 * @param  string $parameter
 * @return array or string
 */
function get_category($catid = '', $parameter = ''){
    if(!$categoryinfo = getcache('categoryinfo')){
		$categoryinfo = D('category')->order('listorder ASC, catid ASC')->select();
		setcache('categoryinfo', $categoryinfo);
	}
	if($catid){
		$catid_arr = yzm_array_column($categoryinfo, 'catid');
        $catid = array_search($catid, $catid_arr);
        if ($catid === false) {
            return array();
		}
		return $parameter ? (isset($categoryinfo[$catid][$parameter]) ? $categoryinfo[$catid][$parameter] : '') : $categoryinfo[$catid];

	}else{
		return $categoryinfo;	
	}
    
}


/**
 * 根据栏目ID获取栏目名称
 *
 * @param  int $catid
 * @return string
 */
function get_catname($catid){
	$catid = intval($catid);
    $data = get_category($catid);
	if(!$data) return '';
    return $data['catname']; 	
}


/**
 * 根据栏目ID获取子栏目信息
 *
 * @param  int $catid
 * @param  boll $is_show 前端不显示栏目是否显示
 * @return array
 */
function get_childcat($catid, $is_show = false){
	$catid = intval($catid);
    $data = get_category();
	$r = array();
	foreach($data as $v){
		if(!$v['display'] && !$is_show) continue;
		if($v['parentid'] == $catid) $r[] = $v;
	}
    return $r; 	
}


/**
 * 根据栏目ID获取当前位置
 *
 * @param  int $catid
 * @param  bool $is_mobile 是否为手机版
 * @param  bool $self 是否包含本身 false为不包含
 * @param  string $symbol 栏目间隔符
 * @return string
 */
function get_location($catid, $is_mobile=false, $self=true, $symbol=' &gt; '){
	$catid = intval($catid);
	$data = explode(',', get_category($catid, 'arrparentid'));
	if($is_mobile){
		$str = '<a href="'.U('mobile/index/init').'">首页</a>';
		foreach($data as $v){
			if($v) $str .= $symbol.'<a href="'.U('mobile/index/lists', array('catid'=>$v)).'">'.get_category($v, 'mobname').'</a>';
		}
		if($self) $str .= $symbol.'<a href="'.U('mobile/index/lists', array('catid'=>$catid)).'">'.get_category($catid, 'mobname').'</a>';
	}else{
		$str = '<a href="'.SITE_URL.'">首页</a>';
		foreach($data as $v){
			if($v) $str .= $symbol.'<a href="'.get_category($v, 'pclink').'">'.get_category($v, 'catname').'</a>';
		}
		if($self) $str .= $symbol.'<a href="'.get_category($catid, 'pclink').'">'.get_category($catid, 'catname').'</a>';
	}

    return $str;	
}



/**
 * 根据模型ID获取model信息
 *
 * @param  int $modelid
 * @param  bool $parameter 获取键名称
 * @return string
 */
function get_model($modelid, $parameter = 'tablename'){
	$modelinfo = get_modelinfo();
	$modelarr = array();
	foreach($modelinfo as $val){
		$modelarr[$val['modelid']] = $val;
	}
	if(!isset($modelarr[$modelid])) return false;
	return $modelarr[$modelid][$parameter];
}


/**
 * 获取内容关键字
 *
 * @param  int $catid
 * @param  string $parameter
 * @return array or string
 */
function get_content_keyword(){
	$res = getcache('keyword_link');
    if($res === false){
		$res = D('keyword_link')->field('keyword,url')->order('id DESC')->limit(300)->select();
		setcache('keyword_link', $res);
	}
	return $res;
}


/**
 * 获取内容评论数
 *
 * @param  int $id
 * @param  int $catid
 * @param  int $modelid
 * @return string
 */
function get_comment_total($id, $catid = 0, $modelid = 1){
	if(!$catid) return 0;
	$commentid = $modelid.'_'.$catid.'_'.$id;
	$total = D('comment_data')->field('total')->where(array('commentid'=>$commentid))->one();
	return $total ? $total : 0;
}


/**
 * 获取组别信息
 *
 * @param  int $groupid
 * @return array
 */
function get_groupinfo($groupid = ''){
    if(!$member_group = getcache('member_group')){
		$data = D('member_group')->select();
		$member_group = array();
		foreach($data as $val){
			$member_group[$val['groupid']] = $val;
		}
		setcache('member_group', $member_group);
	}
	if($groupid){
		return array_key_exists($groupid,$member_group) ? $member_group[$groupid] : array();
	}else{
		return $member_group;	
	}
    
}


/**
 * 根据组别ID获取组别名称
 *
 * @param  int $catid
 * @return string
 */
function get_groupname($groupid){
	$groupid = intval($groupid);
    $data = get_groupinfo($groupid);
	if(!$data) return '';
    return $data['name']; 	
}


/**
 * 获取用户头像
 * @param $user userid或者username
 * @param $type 1为根据userid查询，其他为根据username查询, 建议根据userid查询
 * @param default 如果用户头像为空，是否显示默认头像
 * @return string
 */
function get_memberavatar($user, $type=1, $default=true) {
	global $member_detail;
	$member_detail = isset($member_detail) ? $member_detail : D('member_detail');
	if($type == 1){
		$data = $member_detail->field('userpic')->where(array('userid' => $user))->find();
	}else{
		$data = $member_detail->field('userpic')->join('yzmcms_member b ON yzmcms_member_detail.userid=b.userid')->where(array('username' => $user))->find();
	}	
	return $data['userpic'] ? $data['userpic'] : ($default ? STATIC_URL.'images/default.gif' : '');
}


/**
 * 设置路由映射
 */
function set_mapping() {
    if(!$mapping = getcache('mapping')){
		$data = D('category')->field('catid,`type`,catdir,arrchildid')->where(array('`type`<' => 2))->order('catid ASC')->select();
		$mapping = array();
		foreach($data as $val){
			$mapping['^'.str_replace('/', '\/', $val['catdir']).'$'] = 'index/index/lists/catid/'.$val['catid'];
			if($val['type']) continue;	
			$mapping['^'.str_replace('/', '\/', $val['catdir']).'\/list_(\d+)$'] = 'index/index/lists/catid/'.$val['catid'].'/page/$1';
			if($val['catid']!=$val['arrchildid']) continue;	
			$mapping['^'.str_replace('/', '\/', $val['catdir']).'\/(\d+)$'] = 'index/index/show/catid/'.$val['catid'].'/id/$1';				
		}
		//结合自定义URL规则
		$route_rules = get_urlrule();
		if(!empty($route_rules)) $mapping = array_merge($route_rules, $mapping); 
		setcache('mapping', $mapping);
	}
	return array_merge($mapping, C('route_rules'));
}



/**
 * 获取自定义URL规则
 * @return array
 */
function get_urlrule() {
    if(!$urlrule = getcache('urlrule')){
		$data = D('urlrule')->select();
		$urlrule = array();
		foreach($data as $val){
			$val['urlrule'] = '^'.str_replace('/', '\/', $val['urlrule']).'$';
			$urlrule[$val['urlrule']] = $val['route'];
		}
		setcache('urlrule', $urlrule);
	}
	return $urlrule;	
}


/**
 * 获取用户所有信息
 * @param $userid 
 * @param $additional  是否获取附表信息
 * @return array or false
 */
function get_memberinfo($userid, $additional=false){	
	$memberinfo = array();
	global $member;
	$member = isset($member) ? $member : D('member');
	$memberinfo = $member->field('username,regdate,lastdate,lastip,loginnum,email,groupid,amount,experience,point,status,vip,overduedate,email_status')->where(array('userid' => $userid))->find();
	if(!$memberinfo) return false;
	
	if($additional){
		global $member_detail;
		$member_detail = isset($member_detail) ? $member_detail : D('member_detail');
		$data = $member_detail->field('sex,realname,nickname,qq,mobile,phone,userpic,birthday,industry,area,motto,introduce,guest')->where(array('userid' => $userid))->find();
		$memberinfo = array_merge($memberinfo, $data);
	}
	return $memberinfo;
}


/**
 * 字段排序
 * @param $field 字段名
 * @return string 
 */
function field_order($field){
	$str = isset($_GET['of'])&&$_GET['of']==$field&&in_array($_GET['or'],array('ASC', 'DESC')) ? strtolower($_GET['or']) : '';
	unset($_GET['m'],$_GET['c'],$_GET['a'],$_GET['page']);
	return '<span class="yzm-caret-wrapper '.$str.'">
            <a class="yzm-sort-caret yzm-ascending" href="'.U(ROUTE_A, array_merge($_GET, array('of'=>$field,'or'=>'ASC'))).'" title="正序排列"></a><a class="yzm-sort-caret yzm-descending" href="'.U(ROUTE_A, array_merge($_GET, array('of'=>$field,'or'=>'DESC'))).'" title="倒序排列"></a>
          </span>';
}


/**
 * 对用户的密码进行加密
 * @param $pass 字符串
 * @return string 
 */
function password($pass) {
	return md5(substr(md5(trim($pass)), 3, 26));
}