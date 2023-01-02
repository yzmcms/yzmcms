<?php
/**
 * form.class.php  form类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-12-10 
 */

class form {
	
	/**
	 * input 
	 * @param $name name
	 * @param $value 默认值 如：YzmCMS
	 * @param $required  是否为必填项 默认false
	 * @param $width  宽度 如：100
	 * @param $attribute 外加属性
	 */
	public static function input($name = '', $value = '', $required=false, $width = 0, $attribute='') {
		$string = '<input class="yzm-input-text" ';
		if($width) $string .= ' style="width:'.$width.'px" ';
		if($required) $string .= ' required="required" ';
		$string .= ' name="'.$name.'" id="'.$name.'" '.$attribute;
		$string .= ' type="text" value="'.$value.'">';
		return $string;
	}


	/**
	 * number 
	 * @param $name name
	 * @param $value 默认值 如：0
	 * @param $required  是否为必填项 默认false
	 * @param $width  宽度 如：100
	 * @param $attribute 外加属性
	 */
	public static function number($name = '', $value = '', $required=false, $width = 0, $attribute='') {
		$string = '<input class="yzm-input-text" ';
		if($width) $string .= ' style="width:'.$width.'px" ';
		if($required) $string .= ' required="required" ';
		$string .= ' name="'.$name.'" id="'.$name.'" '.$attribute;
		$string .= ' type="number" value="'.$value.'">';
		return $string;
	}
		

	/**
	 * textarea
	 * @param $name name
	 * @param $value 默认值 如：YzmCMS
	 * @param $required  是否为必填项 默认false
	 * @param $width  宽度 如：100
	 * @param $attribute 外加属性
	 */
	public static function textarea($name = '', $value = '', $required=false, $width = 0, $attribute='') {
		$string = '<textarea class="textarea yzm-textarea" name="'.$name.'" id="'.$name.'" ';
		if($width) $string .= ' width="'.$width.'px" ';
		if($required) $string .= ' required="required" ';
		$string .= $attribute.' >'.$value.'</textarea>';
		return $string;
	}

	
	/**
	 * 下拉选择框
	 * @param $name name
	 * @param $val 默认选中值 如：1
	 * @param $array 一维数组 如：array('交易成功', '交易失败', '交易结果未知');
	 * @param $default_option 提示词 如：请选择交易 
	 * @param $attribute 外加属性
	 */
	public static function select($name, $val = 0, $array = array(), $default_option = '', $attribute='') {
		$string = '<select name="'.$name.'" id="'.$name.'" class="select yzm-select" '.$attribute.'>';
		if($default_option) $string .= "<option value=''>$default_option</option>";
		if(!is_array($array) || count($array)== 0) return false;
		$ids = array();
		if(isset($val)) $ids = explode(',', $val);
		foreach($array as $value) {
			$arr = explode(':', $value);
			$key = trim($arr[0]);
			$value = isset($arr[1]) ? $arr[1] : $arr[0];  
			$selected = in_array($key, $ids) ? 'selected' : '';
			$string .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
		}
		$string .= '</select>';
		return $string;
	}
	
	
	/**
	 * 复选框
	 * 
	 * @param $name name
	 * @param $val 默认选中值，多个用 '逗号'分割 如：'1,2'
	 * @param $array 一维数组 如：array('交易成功', '交易失败', '交易结果未知');
	 */
	public static function checkbox($name, $val = '', $array = array()) {
		$string = '<input type="hidden" name="'.$name.'" value="">';
		$val = trim($val);
		if($val != '') $val = strpos($val, ',') ? explode(',', $val) : array($val);
		$i = 1;
		foreach($array as $value) {
			$arr = explode(':', $value);
			$key = trim($arr[0]);
			$value = isset($arr[1]) ? $arr[1] : $arr[0]; 
			$checked = ($val && in_array($key, $val)) ? 'checked' : '';
			$string .= '<label class="option_label option_box" >';
			$string .= '<input type="checkbox" class="yzm-checkbox" name="'.$name.'[]" id="'.$name.'_'.$i.'" '.$checked.' value="'.$key.'">'.$value;
			$string .= '</label>';
			$i++;
		}
		return $string;
	}

	
	/**
	 * 单选框
	 * 
	 * @param $name name
	 * @param $val 默认选中值 如：1
	 * @param $array 一维数组 如：array('交易成功', '交易失败', '交易结果未知');
	 */
	public static function radio($name, $val = 0, $array = array()) {
		$string = '';
		foreach($array as $value) {
			$arr = explode(':', $value);
			$key = trim($arr[0]);
			$value = isset($arr[1]) ? $arr[1] : $arr[0]; 
			$checked = trim($val)==$key ? 'checked' : '';
			$string .= '<label class="option_label option_radio" >';
			$string .= '<input type="radio" class="yzm-radio" name="'.$name.'" id="'.$name.'_'.$key.'" '.$checked.' value="'.$key.'">'.$value;
			$string .= '</label>';
		}
		return $string;
	}
	
	
	/**
	 * 验证码
	 * @param string $id   验证码ID
	 */
	public static function code($id='code') {
		return '<img src="'.U('api/index/code/').'" id="'.$id.'" onclick="this.src=this.src+\'?\'" style="cursor:pointer;" title="看不清？点击更换">';
	}
	
	
	/**
	 * 日期时间控件
	 * 
	 * @param $name name
	 * @param $val 默认值
	 * @param $isdatetime 是否显示时分秒
	 * @param $loadjs 是否重复加载js，防止页面程序加载不规则导致的控件无法显示
	 * @param $attribute 外加属性
	 */
	public static function datetime($name, $val = '', $isdatetime = 0, $loadjs = 0, $attribute = '') {		
		$string = '';
		if($loadjs || !defined('DATETIME')) {
			define('DATETIME', 1);
			$string .= '<script type="text/javascript" src="'.STATIC_URL.'plugin/laydate/1.1/laydate.js"></script>';
		}
		
		$string .= '<input class="laydate-icon date"  value="'.$val.'" name="'.$name.'" id="'.$name.'" '.$attribute.'>';	
		$string .= '<script type="text/javascript"> laydate({elem: "#'.$name.'",';
		if($isdatetime) $string .= 'istime: true,format: "YYYY-MM-DD hh:mm:ss",';
		$string .= '});</script>';
		return $string;
	}
	
	
	/**
	 * 图片上传
	 * 
	 * @param $name name
	 * @param $val 默认值
	 * @param $style 样式
	 * @param $attribute 外加属性	 
	 */
	public static function image($name, $val = '', $style = '', $iscropper = false, $attribute = '') {	
		$style = $style ? $style : 'width:370px';		
		$string = '<input type="hidden" name="'.$name.'_attid" id="'.$name.'_attid" value="0"><input class="input-text uploadfile" type="text" name="'.$name.'"  value="'.$val.'"  onmouseover="yzm_img_preview(\''.$name.'\', this.value)" onmouseout="layer.closeAll();" id="'.$name.'" style="'.$style.'" '.$attribute.'> <a href="javascript:;" onclick="yzm_upload_att(\''.U('attachment/api/upload_box', array('module'=>ROUTE_M, 'pid'=>$name), false).'\')" class="btn btn-primary radius upload-btn"><i class="yzm-iconfont yzm-iconshangchuan"></i> 浏览文件</a>';
		
		if($iscropper) $string = $string .' '.form::cropper($name);
		return $string;
	}
	
	
	/**
	 * 多图上传
	 * 
	 * @param $name name
	 * @param $val 默认值
	 * @param $n 上传数量
	 */
	public static function images($name, $val = '', $n = 20) {
		$n = $n ? $n : 20;
		$string = '';
		$string .= '<fieldset class="fieldset_list"><legend>图片列表</legend><div class="fieldset_tip">您最多可以同时上传 <span style="color:red">'.$n.'</span> 个文件</div>
					<ul id="'.$name.'" class="file_ul">';
		if($val){
			$string .= '<input type="hidden" name="'.$name.'" value="">';
			$arr = string2array($val);
			foreach($arr as $key => $val){
				$string .= '<li>文件：<input type="text" name="'.$name.'[url][]" value="'.$val['url'].'" id="'.$name.'_'.$key.'" onmouseover="yzm_img_preview(\''.$name.'_'.$key.'\', this.value)" onmouseout="layer.closeAll();" class="input-text yzm-input-url"> 描述：<input type="text" name="'.$name.'[alt][]" value="'.$val['alt'].'" class="input-text yzm-input-alt"><a href="javascript:;" class="secondary" onclick="yzm_move_li(this, 1);">上移</a> <a href="javascript:;" class="secondary" onclick="yzm_move_li(this, 0);">下移</a> <a href="javascript:;" class="danger" onclick="yzm_delete_li(this);">删除</a></li>';
			}
		}					
		$string .= 	'</ul></fieldset>
				<a href="javascript:;" onclick="yzm_upload_att(\''.U('attachment/api/upload_box', array('module'=>ROUTE_M, 'pid'=>$name, 'n'=>$n), false).'\')" class="btn btn-primary radius upload-btn mt-5"><i class="yzm-iconfont yzm-iconshangchuan"></i> 浏览文件</a> <a href="javascript:;" onclick="yzm_add_attachment(\''.$name.'\')" class="btn btn-secondary radius upload-btn mt-5"><i class="yzm-iconfont yzm-icontianjia"></i> 添加远程地址</a>';
		
		return $string;
	}
	

	/**
	 * 图像裁剪
	 * 
	 * @param $cid 		原图所在input的id
	 * @param $spec  	裁剪规则，1：4*3, 2:3*2, 3:1*1, 4:2*3
	 */
	public static function cropper($cid, $spec=2) {		
		$string = '<a href="javascript:;" onclick="yzm_img_cropper(\''.$cid.'\', \''.U('attachment/api/img_cropper', array('spec'=>$spec), false).'\')" class="btn btn-secondary radius upload-btn"><i class="yzm-iconfont yzm-iconcaijian"></i> 裁剪图片</a>';
		
		return $string;
	}
	
	
	/**
	 * 附件上传
	 * 
	 * @param $name name
	 * @param $val 默认值
	 * @param $style 样式
	 * @param $attribute 外加属性	
	 */
	public static function attachment($name, $val = '', $style='', $attribute='') {
		$style = $style ? $style : 'width:370px';		
		$string = '<input type="hidden" name="'.$name.'_attid" id="'.$name.'_attid" value="0"><input class="input-text uploadfile" type="text" name="'.$name.'"  value="'.$val.'"  id="'.$name.'" style="'.$style.'" '.$attribute.'> <a href="javascript:;" onclick="yzm_upload_att(\''.U('attachment/api/upload_box', array('module'=>ROUTE_M, 'pid'=>$name, 't'=>2), false).'\')" class="btn btn-primary radius upload-btn"><i class="yzm-iconfont yzm-iconshangchuan"></i> 浏览文件</a>';
		
		return $string;
	}	
	
	
	/**
	 * 多文件上传
	 * 
	 * @param $name name
	 * @param $val 默认值
	 * @param $n 上传数量
	 */
	public static function attachments($name, $val = '', $n = 20) {
		$n = $n ? $n : 20;
		$string = '';
		$string .= '<fieldset class="fieldset_list"><legend>文件列表</legend><div class="fieldset_tip">您最多可以同时上传 <span style="color:red">'.$n.'</span> 个文件</div>
					<ul id="'.$name.'" class="file_ul">';
		if($val){
			$string .= '<input type="hidden" name="'.$name.'" value="">';
			$arr = string2array($val);
			foreach($arr as $key => $val){
				$string .= '<li>文件：<input type="text" name="'.$name.'[url][]" value="'.$val['url'].'" id="'.$name.'_'.$key.'" class="input-text yzm-input-url"> 描述：<input type="text" name="'.$name.'[alt][]" value="'.$val['alt'].'" class="input-text yzm-input-alt"><a href="javascript:;" class="secondary" onclick="yzm_move_li(this, 1);">上移</a> <a href="javascript:;" class="secondary" onclick="yzm_move_li(this, 0);">下移</a> <a href="javascript:;" class="danger" onclick="yzm_delete_li(this);">删除</a></li>';
			}
		}					
		$string .= 	'</ul></fieldset>
				<a href="javascript:;" onclick="yzm_upload_att(\''.U('attachment/api/upload_box', array('module'=>ROUTE_M, 'pid'=>$name, 'n'=>$n, 't'=>2), false).'\')" class="btn btn-primary radius upload-btn mt-5"><i class="yzm-iconfont yzm-iconshangchuan"></i> 浏览文件</a> <a href="javascript:;" onclick="yzm_add_attachment(\''.$name.'\')" class="btn btn-secondary radius upload-btn mt-5"><i class="yzm-iconfont yzm-icontianjia"></i> 添加远程地址</a>';
		
		return $string;
	}

	
	/**
	 * 编辑器
	 * 
	 * @param $name name
	 * @param $val 默认值
	 * @param $style 样式
	 * @param $isload 是否加载js,当该页面加载过编辑器js后，无需重复加载
	 */
	public static function editor($name = 'content', $val = '', $style='', $isload=false) {
		$style = $style ? $style : 'width:100%;height:400px';
		$string = '';
		if($isload) {
			$string .= '<script type="text/javascript" charset="utf-8" src="'.STATIC_URL.'plugin/ueditor/ueditor.config.js"></script>
			<script type="text/javascript" charset="utf-8" src="'.STATIC_URL.'plugin/ueditor/ueditor.all.min.js"> </script>
			<script type="text/javascript" charset="utf-8" src="'.STATIC_URL.'plugin/ueditor/lang/zh-cn/zh-cn.js"></script>';
		}
		$string .= '<script id="'.$name.'" type="text/plain" style="'.$style.'" name="'.$name.'">'.$val.'</script>
			<script type="text/javascript"> var ue = UE.getEditor(\''.$name.'\'); </script>';
		
		return $string;
	}
	
	
	/**
	 * 编辑器-Mini版
	 * 
	 * @param $name name
	 * @param $val 默认值
	 * @param $style 样式
	 * @param $isload 是否加载js,当该页面加载过编辑器js后，无需重复加载
	 */
	public static function editor_mini($name = 'content', $val = '', $style='', $isload=false) {
		$style = $style ? $style : 'width:100%;height:400px';
		$string = '';
		if($isload) {
			$string .= '<script type="text/javascript" charset="utf-8" src="'.STATIC_URL.'plugin/ueditor/ueditor.config.js"></script>
			<script type="text/javascript" charset="utf-8" src="'.STATIC_URL.'plugin/ueditor/ueditor.all.min.js"> </script>
			<script type="text/javascript" charset="utf-8" src="'.STATIC_URL.'plugin/ueditor/lang/zh-cn/zh-cn.js"></script>';
		}		
		$string .= '<script id="'.$name.'" type="text/plain" style="'.$style.'" name="'.$name.'">'.$val.'</script>
			<script type="text/javascript"> var ue = UE.getEditor("'.$name.'",{
            toolbars:[[ "fullscreen","source","|","undo","redo","|", "removeformat", "formatmatch", "pasteplain",
            "bold","italic","underline","blockquote","forecolor","|","paragraph","fontsize","fontfamily","|","simpleupload","link","unlink","emotion","insertcode","date","time","drafts"]],
            //关闭字数统计
            wordCount:false,
            //关闭elementPath
            elementPathEnabled:false,
            //默认的编辑区域高度
            initialFrameHeight:300
        }); </script>';
		
		return $string;
	}
	
}
