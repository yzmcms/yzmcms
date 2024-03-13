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
 * 获取系统SEO后缀
 * @return string
 */
function get_seo_suffix(){
	$site_seo_division = get_config('site_seo_division');
	$siteid = get_siteid();
	$site = $siteid ? get_site($siteid) : get_config();
	return $site['site_seo_suffix'] ? $site_seo_division.$site['site_seo_suffix'] : $site_seo_division.$site['site_name'];
}


/**
 * 获取当前站点首页URL
 * @return string
 */
function get_site_url(){
	$siteid = get_siteid();
	if(!$siteid) return get_config('site_url');
	$site_url = get_site($siteid, 'site_url');
	return $site_url ? $site_url : get_config('site_url');
}


/**
 * 获取当前站点SEO
 * @return string
 */
function get_site_seo($siteid = null, $title = '', $keyword = '', $site_description = ''){
	$siteid = $siteid===null ? get_siteid() : $siteid;
	$siteinfo = $siteid ? get_site($siteid) : get_config();
	$site_name = ($title ? $title : $siteinfo['site_name']).get_seo_suffix();
	$site_keyword = $keyword ? $keyword.','.$siteinfo['site_keyword'] : $siteinfo['site_keyword'];
	$site_description = $site_description ? $site_description : $siteinfo['site_description'];
	return array(
		$site_name,
		$site_keyword,
		$site_description
	);
}


/**
 * 获取内容页URL 
 * @param $catid 
 * @param $id 
 * @return string
 */
function get_content_url($catid, $id){
	if(!$catid || !$id) return '';
	$catinfo = get_category($catid);
	$url_mode = get_config('url_mode');
	if($url_mode==1 || $url_mode==2){
		return get_site_url().$catinfo['catdir'].'/'.$id.C('url_html_suffix');
	}
	return SITE_PATH.$catinfo['catdir'].'/'.$id.C('url_html_suffix');
}


/**
 * 单页面标签，用于在首页或频道页调取单页的简介...
 * @param $catid
 * @param $limit
 * @param $strip
 * @param $field
 * @return string
 */
function page_content($catid = 1, $limit = 300, $strip = true, $field = 'content'){
	global $catpage;
	$catpage = isset($catpage) ? $catpage : D('page');
	$string = $catpage->where(array('catid'=>$catid))->field($field)->find();
	$string = $string ? str_cut(($strip ? strip_tags($string[$field]) : $string[$field]), $limit) : '';
	return $string;	
}


/**
 * 获取来源
 * @param  $modelid
 * @return array
 */
function get_copyfrom($modelid = 1){
	$arr = array('网络', '原创');
	$db = get_model($modelid);
	if(!$db) return $arr;
	$res = D($db)->field('copyfrom')->order('id DESC')->limit(100)->select();
	$res = yzm_array_column($res, 'copyfrom');
	return array_unique(array_merge(array_filter($res), $arr));
}


/**
 * 获取当前站点模型信息
 * @return array
 */
function get_site_modelinfo(){
	$siteid = get_siteid();
	$filename = 'modelinfo_siteid_'.$siteid;
	
	if(!$modelinfo = getcache($filename)){
		$modelinfo = D('model')->where(array('disabled'=>0,'type'=>0,'siteid'=>$siteid))->order('modelid ASC')->select();
		setcache($filename, $modelinfo);
	}
	return $modelinfo;	
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
		$where['type!='] = 2;
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
 * @return bool
 */
function sendmail($email, $title = '', $content = '', $mailtype = 'HTML'){
    if(!is_email($email)) return false;
    yzm_base::load_sys_class('smtp', '', 0);
    $smtp = new smtp(get_config('mail_server'),get_config('mail_port'),get_config('mail_auth'),get_config('mail_user'),get_config('mail_pass'));
    $state = $smtp->sendmail($email, get_config('mail_from'), $title.' - '.get_config('site_name'), $content, $mailtype);
    if(!$state) return false;
    return true;
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
 * @param  string $file 附件名称
 * @return string
 */
function file_icon($file){
    $ext_arr = array('code','css','dir','doc','docx','gif','html','jpeg','jpg','js','mp3','mp4','pdf','php','png','ppt','pptx','psd','rar','sql','swf','txt','xls','xlsx','xml','zip');
    $ext = fileext($file);
    if(in_array($ext,$ext_arr)) return STATIC_URL.'images/ext/'.$ext.'.png';
    return STATIC_URL.'images/ext/hlp.png';
}


/**
 * 会员登录/退出跳转url
 * @param $is_login 1登录，0退出
 * @return string
 */
function url_referer($is_login = 1, $referer = ''){
	
	$referer = $referer ? urlencode($referer) : urlencode(get_url());
	$url = $is_login ? U('member/index/login') : U('member/index/logout');
	if(URL_MODEL) return $url.'?referer='.$referer;	
	return $url.'&referer='.$referer;
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
	if(!$data)  return '广告位不存在';
	if($data['start_time']!=0 && $data['start_time']>SYS_TIME){
		return '广告未开始';
	}
	
	if($data['end_time']!=0 && $data['end_time']<SYS_TIME){
		return '广告已过期';
	}
	return $data['code'];
}


/**
 * 生成Tag URL
 * @param $tid
 * @param $domain
 * @return string
 */
function tag_url($tid, $domain=null){
	if($domain){
		$domain = rtrim($domain, '/');
		return $domain.U('search/index/tag',array('id'=>$tid),false);
	}
	return U('search/index/tag',array('id'=>$tid),$domain);
}


/**
 * 获取内容列表页TAG标签
 * @param  int $catid 
 * @param  int $id    
 * @param  int $limit 
 * @return array
 */
function content_list_tag($catid = 0, $id = 0, $limit = 5){
	if(!$catid || !$id) return false;
	$modelid = get_category($catid, 'modelid', true);
	if(!$modelid) return false;
	$id = intval($id);
	return D('tag_content')->alias('a')->field('id,tag')->join('yzmcms_tag AS b ON a.tagid=b.id')->where("modelid=$modelid AND aid=$id")->order('id ASC')->limit($limit)->select();
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
	return $default ? $default : SITE_PATH.'common/static/images/nopic.jpg';
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
function select_category($name='parentid', $value='0', $root='', $member_publish=0, $attribute='', $parent_disabled=true, $disabled=true, $modelid=0){
	if($root == '') $root = '≡ 作为一级栏目 ≡';
	$categorys = array();
	$html='<select id="select" name="'.$name.'" class="select" '.$attribute.'>';
	$html.='<option value="0">'.$root.'</option>';

	$tree = yzm_base::load_sys_class('tree');
	$data = D('category')->field('catid AS id,catname AS name,parentid,arrparentid,arrchildid,type,modelid,member_publish')->where(array('siteid'=>get_siteid()))->order('listorder ASC,catid ASC')->select(); 

	$arrparentid = array();
	if($modelid){
		foreach($data as $catinfo){
		    if($catinfo['modelid']!=$modelid) continue;
		    $key = md5($catinfo['arrparentid']);
			$arrparentid[$key] = isset($arrparentid[$key]) ? $arrparentid[$key].','.$catinfo['id'] : $catinfo['arrparentid'].','.$catinfo['id'];
		}
		$arrparentid = array_unique(explode(',', join(',', $arrparentid)));
	}

	$publish_arr = array();
	if($member_publish){
		foreach($data as $catinfo){
		    if($catinfo['member_publish']==0) continue;
		    $key = md5($catinfo['arrparentid']);
			$publish_arr[$key] = isset($publish_arr[$key]) ? $publish_arr[$key].','.$catinfo['id'] : $catinfo['arrparentid'].','.$catinfo['id'];
		}
		$publish_arr = array_unique(explode(',', join(',', $publish_arr)));
	}

	foreach($data as $val){
		if($modelid && !array_search($val['id'], $arrparentid)) continue;
		if($member_publish && !array_search($val['id'], $publish_arr)) continue;
		
		$val['html_disabled'] = 0;
		if($parent_disabled && strpos($val['arrchildid'], ',')) $val['html_disabled'] = 1;
		if($disabled && $val['type']) $val['html_disabled'] = 1;
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
 * 下载远程图片
 * @param  string $content   
 * @param  string $targeturl 
 * @return string
 */
function down_remote_img($content, $targeturl = ''){
	preg_match_all("/(src)=([\"|']?)([^ \"'>]+\.(gif|jpg|jpeg|bmp|png|webp))\\2/i", $content, $img_array);
	$img_array = isset($img_array[3]) ? array_unique($img_array[3]) : array();
	
	if($img_array) {
		$path =  C('upload_file').'/'.date('Ym/d');
		$urlpath = SITE_PATH.$path;
		$imgpath =  YZMPHP_PATH.$path;
		if(!is_dir($imgpath)) @mkdir($imgpath, 0777, true);
	}

	$down_ignore_domain = get_config('down_ignore_domain');
	$down_ignore_domain = $down_ignore_domain ? explode(',', $down_ignore_domain) : array();
	array_push($down_ignore_domain, HTTP_HOST);

	foreach($img_array as $value){

		foreach($down_ignore_domain as $ignore_domain){
		    if(stristr($value, $ignore_domain)) continue 2;
		}

		$val = $value;		
		if(strpos($value, 'http') === false){
			if(!$targeturl) continue;
			$value = $targeturl.$value;
		}
		if(strpos($value, '?')){ 
			$value = explode('?', $value);
			$value = $value[0];
		}	
		$ext = fileext($value);
		if(!is_img($ext)) continue;
		$imgname = date('YmdHis').rand(100,999).'.'.$ext;
		$filename = $imgpath.'/'.$imgname;
		$urlname = $urlpath.'/'.$imgname;
		
		ob_start();
		readfile($value);
		$data = ob_get_contents();
		ob_end_clean();
		$len = $data ? file_put_contents($filename, $data) : 0;
		if($len){  
			$arr = array(
				'siteid' => get_siteid(),
				'originname' => '远程下载-'.htmlspecialchars(basename($value)),
				'filename' => $imgname,
				'filepath' => $urlpath.'/',
				'filesize' => $len,
				'fileext' => $ext,
				'module' => ROUTE_M,
				'isimage' => 1,
				'userid' => isset($_SESSION['adminid']) ? $_SESSION['adminid'] : $_SESSION['_userid'],
				'username' => isset($_SESSION['adminname']) ? $_SESSION['adminname'] : $_SESSION['_username'],
				'uploadtime' => SYS_TIME,
				'uploadip' => getip()
			);
			D('attachment')->insert($arr);                       
			$content = str_replace($val, $urlname, $content);
		}
	}
	return $content;
}


/**
 * 更新内容附件
 * @param   $modelid 
 * @param   $id
 * @return bool
 */
function update_attachment($modelid, $id){
	$attachmentid = isset($_SESSION['roleid'])&&isset($_SESSION['attachmentid']) ? $_SESSION['attachmentid'] : get_cookie('attachmentid');
	if(!$attachmentid) return false;
	$attachment = D('attachment');
	$arr = explode('|', $attachmentid);
	foreach($arr as $attid){
		$attachment->update(array('contentid'=>$modelid.'_'.$id), array('id'=>intval($attid)));
	}

	if(isset($_SESSION['roleid'])){
		unset($_SESSION['attachmentid']);
	}else{
		del_cookie('attachmentid');
	}
	return true;
}


/**
 * 删除内容附件
 * @param   $modelid 
 * @param   $id
 * @return bool
 */
function delete_attachment($modelid, $id){
	if(!get_config('att_relation_content')) return false;
	$attachment = D('attachment');
	$upload_type = C('upload_type', 'host');
	yzm_base::load_model($upload_type, 'attachment', 0);
	if(!class_exists($upload_type)) return false;
	$res = $attachment->field('id,filepath,filename')->where(array('contentid'=>$modelid.'_'.$id))->select();
	$upload = new $upload_type();
	foreach($res as $val){
		$res = $upload->deletefile($val);
		if(!$res)  continue;
		$attachment->delete(array('id' => $val['id']));
	}
	return true;
}


/**
 * 获取当前的站点ID
 * 兼容站群模块/联系QQ：214243830
 * @return int
 */
function get_siteid() {

	if(!is_file(APP_PATH.'site/common/function/function.php')) return 0;

	include_once APP_PATH.'site/common/function/function.php';
	
	return public_get_siteid();
}


/**
 * 获取站点信息
 * 兼容站群模块/联系QQ：214243830
 * @param  int $siteid
 * @param  string $parameter
 * @return array|string     
 */
function get_site($siteid = 0, $parameter = ''){

	$siteid = intval($siteid);
	if(!is_file(APP_PATH.'site/common/function/function.php')) {
		if($siteid){
			return $parameter ? '' : array();
		}else{
			return  array();
		}
	}

	include_once APP_PATH.'site/common/function/function.php';
	
	return public_get_site($siteid, $parameter);
}



/**
 * 是否存在子栏目
 * @param  array  $data 栏目信息或栏目ID
 * @return bool
 */
function is_childid($data){
	if(is_numeric($data)) $data = get_category($data);
	if(!is_array($data) || !isset($data['arrchildid'])) return false;
	return strpos($data['arrchildid'], ',');
}



/**
 * 获取栏目信息
 *
 * @param  int $catid
 * @param  string $parameter
 * @param  bool  $all
 * @return array|string
 */
function get_category($catid = 0, $parameter = '', $all = false){
	if($all){
		$where = array();
		$filename = 'categoryinfo';
	}else{
		$siteid = get_siteid();
		$where = array('siteid' => $siteid);
		$filename = 'categoryinfo_siteid_'.$siteid;
	}
	
    if(!$categoryinfo = getcache($filename)){
		$categoryinfo = D('category')->where($where)->order('listorder ASC, catid ASC')->select();
		setcache($filename, $categoryinfo);
	}
	if($catid){
		$catid_arr = yzm_array_column($categoryinfo, null, 'catid');
        if (!isset($catid_arr[$catid])) {
            return $parameter ? '' : array();
		}
		return $parameter ? (isset($catid_arr[$catid][$parameter]) ? $catid_arr[$catid][$parameter] : '') : $catid_arr[$catid];

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
    $data = get_category($catid, '', true);
	if(!$data) return '';
    return $data['catname']; 	
}


/**
 * 根据栏目ID获取子栏目信息
 *
 * @param  int $catid
 * @param  bool $is_show 前端不显示栏目是否显示
 * @param  int $limit 限制数量
 * @return array
 */
function get_childcat($catid, $is_show = false, $limit = 0){
	$catid = intval($catid);
    $data = get_category();
	$r = array();
	foreach($data as $v){
		if(!$v['display'] && !$is_show) continue;
		if($v['parentid'] == $catid) $r[] = $v;
	}
    return $limit ? array_slice($r, 0, $limit) : $r; 	
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
	$data = $catid ? explode(',', get_category($catid, 'arrparentid')) : array();
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
		if($catid && $self) $str .= $symbol.'<a href="'.get_category($catid, 'pclink').'">'.get_category($catid, 'catname').'</a>';
	}

    return $str;	
}


/**
 * 根据模型ID获取model信息
 *
 * @param  int $modelid
 * @param  bool $parameter 获取键名称
 * @param  bool $is_site 是否只加载当前站点信息
 * @return string
 */
function get_model($modelid, $parameter = 'tablename', $is_site = false){
	$modelinfo = $is_site ? get_site_modelinfo() : get_modelinfo(1);
	$modelarr = array();
	foreach($modelinfo as $val){
		$modelarr[$val['modelid']] = $val;
	}
	if(!isset($modelarr[$modelid])) return false;
	return $parameter ? $modelarr[$modelid][$parameter] : $modelarr[$modelid];
}


/**
 * 获取当前站点默认模型
 * @param   $key 获取的key
 * @return string|array
 */
function get_default_model($key = false){
	$default_model = array();
	$data = get_site_modelinfo();
	foreach ($data as $value) {
		if($value['isdefault']){
			$default_model = $value;
			break;
		}
	}

	if($data && empty($default_model)) $default_model = array_shift($data);
	return $key ? (isset($default_model[$key]) ? $default_model[$key] : null) : $default_model;
}


/**
 * 获取内容关键字
 *
 * @param  int $catid
 * @param  string $parameter
 * @return array|string
 */
function get_content_keyword(){
	$res = getcache('keyword_link');
    if($res === false){
		$res = D('keyword_link')->field('keyword,url')->order('id DESC')->limit(500)->select();
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
	if(!$user) return STATIC_URL.'images/default.gif';
	global $member_detail;
	$member_detail = isset($member_detail) ? $member_detail : D('member_detail');
	if($type == 1){
		$data = $member_detail->field('userpic')->where(array('userid' => $user))->find();
	}else{
		$data = $member_detail->field('userpic')->join('yzmcms_member b ON yzmcms_member_detail.userid=b.userid')->where(array('username' => $user))->find();
	}	
	return $data&&$data['userpic'] ? $data['userpic'] : ($default ? STATIC_URL.'images/default.gif' : '');
}


/**
 * 设置路由映射
 * @param string $m 模块名
 * @return array
 */
function set_mapping($m) {
	$siteid = get_siteid();
    $site_mapping = 'site_mapping_'.$m.'_'.$siteid;
    if(!$mapping = getcache($site_mapping)){
        $data = D('category')->field('catid,`type`,catdir,arrchildid')->where(array('siteid'=>$siteid,'`type`<' => 2))->order('catid ASC')->select();
        $mapping = array();
        foreach($data as $val){
            $mapping['^'.str_replace('/', '\/', $val['catdir']).'$'] = $m.'/index/lists/catid/'.$val['catid'];
            if($val['type']) continue;  
            $mapping['^'.str_replace('/', '\/', $val['catdir']).'\/list_(\d+)$'] = $m.'/index/lists/catid/'.$val['catid'].'/page/$1';
            if(strpos($val['arrchildid'], ',')) continue; 
            $mapping['^'.str_replace('/', '\/', $val['catdir']).'\/(\d+)$'] = $m.'/index/show/catid/'.$val['catid'].'/id/$1';             
        }
        //结合自定义URL规则
        $route_rules = get_urlrule();
        if(!empty($route_rules)) $mapping = array_merge($route_rules, $mapping); 
        setcache($site_mapping, $mapping);
    }
    return array_merge($mapping, C('route_rules'));
}



/**
 * 获取自定义URL规则
 * @return array
 */
function get_urlrule() {
    if(!$urlrule = getcache('urlrule')){
		$data = D('urlrule')->order('listorder ASC,urlruleid ASC')->limit(300)->select();
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
 * @return array|false
 */
function get_memberinfo($userid, $additional=false){	
	$memberinfo = array();
	if(!$userid) return $memberinfo;
	global $member;
	$member = isset($member) ? $member : D('member');
	$memberinfo = $member->field('username,regdate,lastdate,lastip,loginnum,email,groupid,amount,experience,point,status,vip,overduedate,email_status')->where(array('userid' => $userid))->find();
	if(!$memberinfo) return false;
	
	if($additional){
		global $member_detail;
		$member_detail = isset($member_detail) ? $member_detail : D('member_detail');
		$data = $member_detail->where(array('userid' => $userid))->find();
		$memberinfo = array_merge($memberinfo, $data);
	}
	return $memberinfo;
}


/**
 * 获取内容总数
 * @param   $modelid 
 * @param   $catid   
 * @return  int 
 */
function content_total($modelid, $catid = 0){
	$where = array('modelid' => $modelid, 'status' => 1);
	if($catid) $where['catid'] = $catid;
	return D('all_content')->where($where)->total();
}


/**
 * 获取自定义多选字段生成的value值
 * 调用方式如：get_field_val($systems, 'systems', $modelid)
 * @param   $value  
 * @param   $field   
 * @param   $modelid 
 * @return  string          
 */
function get_field_val($value, $field, $modelid){
	$modelinfo = getcache($modelid.'_model');
	if($modelinfo === false){
		if(!D('model')->where(array('modelid' => $modelid))->find()) return $value;
		$modelinfo = D('model_field')->where(array('modelid' => $modelid, 'disabled' => 0))->order('listorder ASC,fieldid ASC')->select();
		setcache($modelid.'_model', $modelinfo);
	}

	$data = yzm_array_column($modelinfo, null, 'field');
	if(!isset($data[$field])) return $value;
	$arr = string2array($data[$field]['setting']);

	$data = array();
	foreach($arr as $val){
		$varr = explode(':', $val);
		if(isset($varr[1])){
			$data[$varr[0]] = $varr[1];  
		}
	}
	if(!$data) return $value;
	return isset($data[$value]) ? $data[$value] : $value;
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
 * 处理上传文件类型
 * @param $type 1图片类型,2附件类型
 * @return array 
 */
function handle_upload_types($type = 1){
    $arr = explode('|', ($type==1 ? get_config('upload_image_types') : get_config('upload_types')));
    $allow = array('png', 'gif', 'jpg', 'jpeg', 'webp', 'bmp', 'ico', 'zip', 'rar', '7z', 'gz', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'csv', 'mp3', 'mp4', 'avi', 'wmv', 'rmvb', 'flv', 'wma', 'wav', 'amr', 'ogg', 'ogv', 'webm', 'swf', 'mkv', 'torrent');
    foreach($arr as $key => $val){
        if(!in_array($val, $allow)) unset($arr[$key]);
    }
    
    return $arr;
}


/**
 * 对用户的密码进行加密
 * @param $pass 字符串
 * @return string 
 */
function password($pass) {
	return md5(substr(md5(trim($pass)), 3, 26));
}