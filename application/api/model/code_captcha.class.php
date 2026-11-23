<?php
/**
 * YzmCMS系统默认code验证码处理类，如需自定义扩展，需在本目录中新建自定义类，且包含show_captcha和check_captcha方法
 * www.yzmcms.com
 * 袁志蒙工作室，并保留所有权利
 */

defined('IN_YZMPHP') or exit('Access Denied'); 
class code_captcha{

    /**
     * 显示验证码
     */
    public function show_captcha(){
        $code = yzm_base::load_sys_class('code');
		if(isset($_GET['width']) && intval($_GET['width'])) $code->width = intval($_GET['width']);
		if(isset($_GET['height']) && intval($_GET['height'])) $code->height = intval($_GET['height']);
		if(isset($_GET['code_len']) && intval($_GET['code_len'])) $code->code_len = intval($_GET['code_len']);
		if(isset($_GET['font_size']) && intval($_GET['font_size'])) $code->font_size = intval($_GET['font_size']);
		if($code->width > 500 || $code->width < 10)  $code->width = 100;
		if($code->height > 300 || $code->height < 10)  $code->height = 35;
		if($code->code_len > 8 || $code->code_len < 4)  $code->code_len = 4;
		$code->show_code();
		$_SESSION['code'] = $code->get_code();
		$_SESSION['code_time'] = SYS_TIME;
    }
    
    /**
     * 校验验证码
     * @return bool 
     */
    public function check_captcha(){
        new_session_start();
        $input_code = isset($_POST['code'])&&is_string($_POST['code']) ? trim($_POST['code']) : '';
        $session_code = isset($_SESSION['code']) ? $_SESSION['code'] : '';
        $code_time = isset($_SESSION['code_time']) ? intval($_SESSION['code_time']) : 0;
        $captcha_error = false;

        if(!$code_time || !$session_code){
            $captcha_error = true;
        }
        if(!is_string($input_code) || $session_code !== strtolower($input_code) || SYS_TIME-$code_time > 120){
            $captcha_error = true;
        }
        unset($_SESSION['code'], $_SESSION['code_time']);
        return !$captcha_error;
    }
}