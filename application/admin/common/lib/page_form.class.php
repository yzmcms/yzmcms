<?php
/**
 * YzmCMS内容管理系统
 * 商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * 功能定制QQ: 21423830
 * 版权所有 WWW.YZMCMS.COM
 */

defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_sys_class('form','',0);

class page_form {
	
	public $modelid;

    public function __construct() {
		$this->modelid = D('model')->field('modelid')->where(array('type'=>2))->one();
    }
	
	
	public function content_edit($data) {
		$modelinfo = $this->get_modelinfo($data);
		$string = '';
		foreach($modelinfo as $val){
			$fieldtype = $val['fieldtype'];
			if($fieldtype == 'input' || $fieldtype == 'number' || $fieldtype == 'decimal'){
					$errortips = !empty($val['errortips']) ? $val['errortips'] : '必填项不能为空';
					$required = $val['isrequired'] ? ' required" errortips="'.$errortips : '';
					$string .= $this->tag_start($val['name'], $val['isrequired']).'<input type="text" class="input-text'.$required.'" value="'.$data[$val['field']].'" name="'.$val['field'].'" placeholder="'.$val['tips'].'">'.$this->tag_end();		   
			}elseif($fieldtype == 'textarea'){
					$errortips = !empty($val['errortips']) ? $val['errortips'] : '必填项不能为空';
					$required = $val['isrequired'] ? ' required" errortips="'.$errortips : '';
					$string .= $this->tag_start($val['name'], $val['isrequired']).'<textarea name="'.$val['field'].'" class="textarea'.$required.'"  placeholder="'.$val['tips'].'" >'.$data[$val['field']].'</textarea>'.$this->tag_end();
			}elseif($fieldtype == 'select'){
					$string .= $this->tag_start($val['name'], $val['isrequired']).'<span class="select-box">'.form::select($val['field'],$data[$val['field']],string2array($val['setting'])).'</span>'.$this->tag_end();
			}elseif($fieldtype == 'radio' || $fieldtype == 'checkbox'){
					$string .= $this->tag_start($val['name'], $val['isrequired']).form::$fieldtype($val['field'],$data[$val['field']],string2array($val['setting'])).$this->tag_end();
			}elseif($fieldtype == 'datetime'){
					$string .= $this->tag_start($val['name'], $val['isrequired']).form::datetime($val['field'],$data[$val['field']], $val['setting']).$this->tag_end();
			}else{
					$string .= $this->tag_start($val['name'], $val['isrequired']).form::$fieldtype($val['field'],$data[$val['field']]).$this->tag_end();
			}
		}		
		return $string;
	}
	
	
	public function tag_start($tip, $isrequired) {
		$str = $isrequired ? '<em class="c-red">*</em>' : '';
		return '<div class="yzm-form-item"><label class="yzm-form-item-label">'.$str.$tip.'：</label><div class="yzm-form-item-content">';
	}
	

	public function tag_end() {
		return '</div></div>';
	}

	
	public function get_modelinfo($data) {
		$modelinfo = getcache($this->modelid.'_model');
		if($modelinfo === false){
			if(!D('model')->where(array('modelid' => $this->modelid))->find()) return_message('模型不存在！', 0);
			$modelinfo = D('model_field')->where(array('modelid' => $this->modelid, 'disabled' => 0))->order('listorder ASC,fieldid ASC')->select();
			setcache($this->modelid.'_model', $modelinfo);
			delcache($this->modelid.'_model_string');
		}

		foreach($modelinfo as $key=>$val){
			if($val['setting_catid']!='0' && strpos($val['setting_catid'], strval($data['catid']))===false) {
				unset($modelinfo[$key]);
			}
		}
		return $modelinfo;
	}


	/**
	 * 内容处理
	 * @param $content 
	 */	
	public function content_dispose($content) {
		$is_array = false;
		foreach($content as $val){
			if(is_array($val)) $is_array = true;
			break;
		}
		if(!$is_array) return implode(',', $content);
		
		//这里认为是多文件上传
		$arr = array();
		foreach($content['url'] as $key => $val){
			$arr[$key]['url'] = $val;
			$arr[$key]['alt'] = $content['alt'][$key];
		}		
		return array2string($arr);
	}


	/**
	 * 获取模型非过滤字段
	 */	
	public function get_notfilter_field() {
		$arr = array('content');
		$data = D('model_field')->field('field,fieldtype')->where(array('modelid' => $this->modelid))->select();
		foreach($data as $val){
			if($val['fieldtype'] == 'editor' || $val['fieldtype'] == 'editor_mini') $arr[] = $val['field'];
		}

		return $arr;
	} 
}