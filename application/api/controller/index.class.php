<?php
/**
 * 系统API接口类
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-18
 */
 
class index{
	
	
	/**
	 * 验证码图像
	 */
	public function code(){	
		session_start();
		$code = yzm_base::load_sys_class('code');
		if(isset($_GET['width']) && intval($_GET['width'])) $code->width = intval($_GET['width']);
		if(isset($_GET['height']) && intval($_GET['height'])) $code->height = intval($_GET['height']);
		if(isset($_GET['code_len']) && intval($_GET['code_len'])) $code->code_len = intval($_GET['code_len']);
		if(isset($_GET['font_size']) && intval($_GET['font_size'])) $code->font_size = intval($_GET['font_size']);
		if($code->width > 500 || $code->width < 10)  $code->width = 100;
		if($code->height > 300 || $code->height < 10)  $code->height = 35;
		if($code->code_len > 8 || $code->code_len < 2)  $code->code_len = 4;
		$code->show_code();
		$_SESSION['code'] = $code->get_code();
	}
	

	/**
	 * 收藏文档，必须登录
	 * @param url 地址
	 * @param title 标题
	 * @return {1:成功;-1:未登录;-2:缺少参数}
	 */	
	public function favorite(){	
		session_start();
		if(isset($_POST['title']) && isset($_POST['url'])) {
			$title = htmlspecialchars(addslashes($_POST['title']));
			$url = safe_replace(addslashes($_POST['url']));
		} else {
			return_json(array('status' => -2));
		}

		//检查是否是否有存在已登录的用户
		if(!isset($_SESSION['_userid'])){
			return_json(array('status' => -1));
		}

		$data = array('title'=>$title, 'url'=>$url, 'inputtime'=>SYS_TIME, 'userid'=>$_SESSION['_userid']);

		$favorite = D('favorite');

		//根据url判断是否已经收藏过。
		$is_exists = $favorite->where(array('url'=>$url, 'userid'=>$_SESSION['_userid']))->find();
		if(!$is_exists) {
			$favorite->insert($data);
		}

		return_json(array('status' => 1));
	}

}