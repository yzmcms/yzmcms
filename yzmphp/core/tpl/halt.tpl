<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>	
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>系统发生错误</title>
	<link rel="Shortcut Icon" href="<?php echo STATIC_URL;?>admin/yzm_admin/images/favicon.ico" />
    <style>
	  *{padding:0;margin:0;}
	  body{background:#fff;font-family:Tahoma,Arial,sans-serif;color:#333;font-size:16px;}
	  #msg{text-align:center;width:750px;margin:100px auto 0;overflow:hidden;word-break:keep-all;word-wrap:break-word;}
	  #title{font-size:120px;}
	  #body{font-size:50px;}
	  #footer{font-size:14px;text-align:right;color:#999;}
	  #footer a{color:#000;text-decoration:none;}
	</style>
</head>
<body>
    <div id="msg">        	
     <div id="title"> (>﹏<) </div>
     <div id="body"><?php echo htmlspecialchars($msg);?></div>
	 <div id="footer"><a href="http://www.yzmcms.com/" target="_blank" title="官方网站">YzmCMS</a><sup><?php echo YZMCMS_VERSION;?></sup></div>
    </div>
</body>
</html>