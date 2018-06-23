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
	$string = $string ? str_cut(strip_tags($string['content']), $limit) : '';
	return $string;	
}


/**
 * 获取TAG标签
 * @param $tags  TAG，编辑内容时用
 * @return string
 */
function get_tags($tags = ''){
	$data = D('tag')->field('id,tag,total')->order('id DESC')->select();
	$string = '';
    if($data){
		foreach($data as $val){
			$str = strpos($tags, $val['id']) === false ? '' : ' checked="checked" ';
			$string .= '<label>'.$val['tag'].' <input name="tag[]" type="checkbox" '.$str.'value="'.$val['id'].'"/></label>&nbsp;&nbsp;';
		}
	}
	$string .= '<a href="javascript:;" onclick="yzm_open(\'添加TAG\',\''.U('admin/tag/add').'\',\'600\',\'300\')" class="yzmcms_a">点击添加</a>';
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
	if(!is_email($email) || empty($mail_pass)) return false;
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
	if(!$arr) exit(json_encode(array('status'=>0,'message'=>L('data_not_modified'))));
	exit(json_encode($arr));	
}


/**
 * 判断是否为手机访问
 * @return bool
 */
function ismobile(){ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA'])){ 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT'])){
        $clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT'])){ 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
            return true;
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
		$data = D('category')->order('listorder ASC, catid ASC')->select();
		$categoryinfo = array();
		foreach($data as $val){
			$categoryinfo[$val['catid']] = $val;
		}
		setcache('categoryinfo', $categoryinfo);
	}
	if($catid){
		if(empty($parameter))
			return array_key_exists($catid,$categoryinfo) ? $categoryinfo[$catid] : array();
		else
			return array_key_exists($catid,$categoryinfo) ? $categoryinfo[$catid][$parameter] : '';
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
 * @return array
 */
function get_childcat($catid){
	$catid = intval($catid);
    $data = get_category();
	$r = array();
	foreach($data as $v){
		if($v['parentid'] == $catid) $r[] = $v;
	}
    return $r; 	
}


/**
 * 根据栏目ID获取当前位置
 *
 * @param  int $catid
 * @param  bool $self 是否包含本身 0为不包含
 * @param  string $symbol 栏目间隔符
 * @return string
 */
function get_location($catid, $self=true, $symbol=' &gt; '){
	$catid = intval($catid);
    $catdata = get_category();
	$data = explode(',', $catdata[$catid]['arrparentid']);
	$str = '<a href="'.SITE_URL.'">首页</a>';
	foreach($data as $v){
		if($v) $str .= $symbol.'<a href="'.$catdata[$v]['pclink'].'" title="'.$catdata[$v]['catname'].'">'.$catdata[$v]['catname'].'</a>';
	}
	if($self) $str .= $symbol.'<a href="'.$catdata[$catid]['pclink'].'" title="'.$catdata[$catid]['catname'].'">'.$catdata[$catid]['catname'].'</a>';
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
		$data = D('category')->field('type,catid,catdir')->where(array('type!=' => 2))->select();
		$mapping = array();
		foreach($data as $val){
			$mapping['^'.$val['catdir'].'$'] = 'index/index/lists/catid/'.$val['catid'];
			if(!$val['type']){
			$mapping['^'.$val['catdir'].'\/list_(\d+)$'] = 'index/index/lists/catid/'.$val['catid'].'/page/$1';
			$mapping['^'.$val['catdir'].'\/(\d+)$'] = 'index/index/show/catid/'.$val['catid'].'/id/$1';				
			}
		}
		//结合自定义URL规则
		$route_rules = get_urlrule();
		if(!empty($route_rules)) $mapping = array_merge($route_rules, $mapping); 
		setcache('mapping', $mapping);
	}
	return $mapping;
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
 * 对用户的密码进行加密
 * @param $pass 字符串
 * @return string 字符串
 */
function password($pass) {
	return md5(substr(md5(trim($pass)), 3, 26));
}