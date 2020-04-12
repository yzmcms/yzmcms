<?php
/**
 * 自定义表单 - 前台操作类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-01-15
 */
 
 
defined('IN_YZMPHP') or exit('Access Denied'); 

class index{
	
	public $modelid, $modelinfo;
	function __construct() {
		
		$this->modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : (isset($_POST['modelid']) ? intval($_POST['modelid']) : 0);
		if(ROUTE_A != 'init')	$this->_check_model();
	}

	
	/**
	 * 自定义表单列表
	 */	
	public function init(){	
		yzm_base::load_sys_class('page','',0);
		$model = D('model');
		$total = $model->where(array('type'=>1, 'disabled'=>0))->total();
		$page = new page($total, 15);
		$formdata = $model->where(array('type'=>1, 'disabled'=>0))->order('modelid DESC')->limit($page->limit())->select();
		
		//SEO相关设置
		$site = get_config();
		$seo_title = '自定义表单列表_'.$site['site_name'];
		$keywords = $site['site_keyword'];
		$description = $site['site_description'];
		$pages = '<span class="pageinfo">共'.$total.'条记录</span>'.$page->getfull();
		include template('index', 'list_diyform');	
	}
	
	
	
	/**
	 * 自定义表单显示页
	 */	
	public function show(){
		$modelid = $this->modelid;
		$modelinfo = $this->modelinfo;
		$title =  $modelinfo['name'];
		$template = $modelinfo['show_template'];
		
		//SEO相关设置
		$site = get_config();
		$seo_title = $title.'_'.$site['site_name'];
		$keywords = $site['site_keyword'];
		$description = $modelinfo['description'] ? $modelinfo['description'] : $site['site_description'];
		
		//获取当前位置
		$location = '<a href="'.SITE_URL.'">首页</a> &gt; <a href="'.U('init').'">'.$modelinfo['name'].'</a> &gt;显示页';
		$fieldstr = $this->_get_model_str($modelid);
		$field_check = $this->_get_model_str($modelid, true);
		include template('index', $template);	
	}
	
	
	
	/**
	 * 自定义表单提交
	 */	
	public function post(){	
		if(isset($_POST['dosubmit'])){

			if($this->modelinfo['check_code']){
				if(empty($_SESSION['code']) || strtolower($_POST['code'])!=$_SESSION['code']){
					$_SESSION['code'] = '';
					showmsg(L('code_error'));
				}
				$_SESSION['code'] = '';
			}

			$field_check = $this->_get_model_str($this->modelid, true);
			foreach($field_check as $k => $v){
				if($v['isrequired']){
					if(empty($_POST[$k])) showmsg($v['errortips']);
				}
			}

			foreach($_POST as $_k=>$_v) {
				$_POST[$_k] = !is_array($_POST[$_k]) ? new_html_special_chars(trim_script($_v)) : $this->_content_dispose($_v);
			}
			
			$_POST['userid'] = isset($_SESSION['_userid']) ? $_SESSION['_userid'] : 0;
			$_POST['username'] = isset($_SESSION['_username']) ? $_SESSION['_username'] : '';
			$_POST['ip'] = getip();
			$_POST['inputtime'] = SYS_TIME;
			
			$tablename = D($this->modelinfo['tablename']);
			$id = $tablename->insert($_POST);
			
			if(!$id) showmsg(L('operation_failure'), 'stop');
			D('model')->update('`items`=`items`+1', array('modelid'=>$this->modelid));

			//发送邮件通知
			if($this->modelinfo['sendmail']){
				sendmail(get_config('mail_inbox'), '表单消息提醒：', 
				'您的网站-表单（'.$this->modelinfo['name'].'）有新的消息，<a href="'.get_config('site_url').'">请查看</a>！<br> <b>'.get_config('site_name').'</b>');
			}
			
			showmsg(L('operation_success'));
		}
	}
	

	
	/**
	 * 检查model
	 */	
	private function _check_model() {
		session_start();
		$data = D('model')->where(array('modelid'=>$this->modelid))->find();
		if(!$data || $data['type']!=1 || $data['disabled']==1){
			showmsg('表单不存在或已禁用!', 'stop');
		}
		
		$setting = json_decode($data['setting'], true);
		if(!$setting['allowvisitor'] && empty($_SESSION['_userid'])) showmsg('请登录会员！', url_referer(get_url()), 2);
		
		$this->modelinfo = array_merge($data, $setting);
	}



	/**
	 * 内容处理
	 * @param $content 
	 */	
	private function _content_dispose($content) {
		$is_array = false;
		foreach($content as $val){
			if(is_array($val)) $is_array = true;
			break;
		}
		if(!$is_array) return safe_replace(implode(',', $content));
		
		//这里认为是多文件上传
		$arr = array();
		foreach($content['url'] as $key => $val){
			$arr[$key]['url'] = safe_replace($val);
			$arr[$key]['alt'] = safe_replace($content['alt'][$key]);
		}		
		return array2string($arr);
	}

	
	
	/**
	 * 获取不同模型获取HTML表单
	 */	
	private function _get_model_str($modelid, $field = false, $data = array()) {
		yzm_base::load_sys_class('form','',0);
		$modelinfo = getcache($modelid.'_model');
		if($modelinfo === false){
			$modelinfo = D('model_field')->where(array('modelid' => $modelid, 'disabled' => 0))->order('listorder ASC, fieldid ASC')->select();
			setcache($modelid.'_model', $modelinfo);
		}
		
		$fields = $fieldstr = array();
		foreach($modelinfo as $val){
			$fieldtype = $val['fieldtype'];
			if($data){
				$val['defaultvalue'] = isset($data[$val['field']]) ? $data[$val['field']] : '';
			}
			$setting = $val['setting'] ? string2array($val['setting']) : 0;
			$required = $val['isrequired'] ? '<span class="red">*</span>' : '';
			$fieldstr[] = '<td class="yzm-table-title">'.$val['name'].$required.'</td><td>'.form::$fieldtype($val['field'], $val['defaultvalue'], $setting).'</td>';	
			$fields[$val['field']] = $val['isrequired'] ? array('isrequired'=>1, 'fieldtype'=>$fieldtype, 'errortips'=>$val['errortips'] ? $val['errortips'] : $val['name'].'不能为空！') : array('isrequired'=>0);
		}
		
		return $field ? $fields : $fieldstr;
	}

}