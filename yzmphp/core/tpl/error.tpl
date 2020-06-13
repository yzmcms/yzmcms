<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>	
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>系统发生错误</title>
	<link rel="Shortcut Icon" href="<?php echo STATIC_URL;?>admin/yzm_admin/images/favicon.ico" />
	<style type="text/css">
	::selection{background-color: #e13300;color: white;}
	::-moz-selection{background-color: #e13300;color: white;}
	body{background-color: #fff;margin: 40px;font: 13px/20px normal Tahoma,Arial,sans-serif;color: #4f5155;}
	a{color: #4f5155;background-color: transparent;font-weight: bold;}
	h1{color: #444;background-color: transparent;border-bottom: 1px solid #d0d0d0;font-size: 19px;font-weight: normal;margin: 0 0 14px 0;padding: 14px 15px 10px 15px;}
	code{font-family: consolas, monaco, courier new, courier, monospace;font-size: 12px;background-color: #f9f9f9;border: 1px solid #d0d0d0;color: #002166;display: block;margin: 14px 0 14px 0;padding: 12px 10px 12px 10px;}
	#body{margin: 0 15px 0 15px;}
	p.footer{text-align: right;font-size: 11px;border-top: 1px solid #d0d0d0;line-height: 32px;padding: 0 10px 0 10px;margin: 20px 0 0 0;}
	#container{margin: 10px;border: 1px solid #d0d0d0;box-shadow: 0 0 8px #d0d0d0;}
	</style>
</head>
<body>

<div id="container">
	<h1><?php echo $type==1 ? 'PHP' : 'MySQL';?> FatalError!</h1>

	<div id="body">
		<p>Message ： </p>
		<p><?php echo $msg;?></p>
		<code><?php echo $detailed ? $detailed : $msg;?> </code>
	</div>

	<p class="footer">Powered by <a href="http://www.yzmphp.com/" target="_blank">YZMPHP</a> Version <strong><?php echo YZMPHP_VERSION;?></strong></p>
</div>

</body>
</html>