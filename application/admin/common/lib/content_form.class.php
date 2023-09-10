<?php
/**
 * YzmCMS内容管理系统
 * 商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * 功能定制QQ: 21423830
 * 版权所有 WWW.YZMCMS.COM
 */

defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_sys_class('form','',0);

class content_form {
	
	public $modelid;

    public function __construct($modelid) {
		$this->modelid = $modelid;
    }

	public function content_add() {
		$modelinfo = $this->get_modelinfo();
		$string = getcache($this->modelid.'_model_string');
		if($string === false){
			$string = '';
			foreach($modelinfo as $val){
				$fieldtype = $val['fieldtype'];
				if($fieldtype == 'input' || $fieldtype == 'number' || $fieldtype == 'decimal'){
						$errortips = !empty($val['errortips']) ? $val['errortips'] : '必填项不能为空';
						$required = $val['isrequired'] ? ' required" errortips="'.$errortips : '';
						$string .= $this->tag_start($val['name'], $val['isrequired']).'<input type="text" class="input-text'.$required.'" value="'.$val['defaultvalue'].'" name="'.$val['field'].'" placeholder="'.$val['tips'].'">'.$this->tag_end();		   
				}elseif($fieldtype == 'textarea'){
						$errortips = !empty($val['errortips']) ? $val['errortips'] : '必填项不能为空';
						$required = $val['isrequired'] ? ' required" errortips="'.$errortips : '';
						$string .= $this->tag_start($val['name'], $val['isrequired']).'<textarea name="'.$val['field'].'" class="textarea'.$required.'"  placeholder="'.$val['tips'].'" >'.$val['defaultvalue'].'</textarea>'.$this->tag_end();
				}elseif($fieldtype == 'select'){
						$string .= $this->tag_start($val['name'], $val['isrequired']).'<span class="select-box">'.form::select($val['field'],$val['defaultvalue'],string2array($val['setting'])).'</span>'.$this->tag_end();
				}elseif($fieldtype == 'radio' || $fieldtype == 'checkbox'){
						$string .= $this->tag_start($val['name'], $val['isrequired']).form::$fieldtype($val['field'],$val['defaultvalue'],string2array($val['setting'])).$this->tag_end();
				}elseif($fieldtype == 'datetime'){
						$string .= $this->tag_start($val['name'], $val['isrequired']).form::datetime($val['field'], '', $val['setting']).$this->tag_end();
				}else{
						$string .= $this->tag_start($val['name'], $val['isrequired']).form::$fieldtype($val['field']).$this->tag_end();
				}
				
			}
			setcache($this->modelid.'_model_string', $string);
		}		
		return $string;
	}
	
	
	public function content_edit($data) {
		$modelinfo = $this->get_modelinfo();
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
		$str = $isrequired ? '<span class="c-red">*</span>' : '';
		return '<div class="row cl"><label class="form-label col-xs-4 col-sm-2">'.$str.$tip.'：</label><div class="formControls col-xs-8 col-sm-9">';
	}
	

	public function tag_end() {
		return '</div></div>';
	}

	
	public function get_modelinfo() {
		$modelinfo = getcache($this->modelid.'_model');
		if($modelinfo === false){
			if(!D('model')->where(array('modelid' => $this->modelid))->find()) return_message('模型不存在！', 0);
			$modelinfo = D('model_field')->where(array('modelid' => $this->modelid, 'disabled' => 0))->order('listorder ASC,fieldid ASC')->select();
			setcache($this->modelid.'_model', $modelinfo);
			delcache($this->modelid.'_model_string');
		}
		return $modelinfo;
	}
}