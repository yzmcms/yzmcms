<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>	
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, maximum-scale=1.0, initial-scale=1.0, user-scalable=no">
	<?php if(!$stop){?>
    <meta http-equiv="refresh" content="<?php echo $limittime;?>;URL=<?php echo $gourl;?>" />
	<?php }?>
    <title>YzmCMS提示信息</title>
	<link rel="Shortcut Icon" href="<?php echo STATIC_URL;?>admin/yzm_admin/images/favicon.ico" />
    <style type="text/css">
	  *{padding:0;margin:0;}
	  body{background:#fff;color:#000;font-family:Tahoma,Arial,sans-serif;}
	  .yzm-msg{width:500px;position:absolute;top:44%;left:50%;margin:-87px 0 0 -250px;line-height:30px;text-align:center;font-size:14px;background:#fff;box-shadow: 0px 0px 25px #999;border-radius:3px;overflow:hidden;}
	  .yzm-msg-title{height:35px;line-height:35px;color:#fff;background:#333;}
	  .yzm-msg-body{margin:20px 0;text-align:center}
	  .yzm-info{margin-bottom:10px;word-break:break-all;padding:0 5px}
	  .yzm-msg-body p{font-size:12px;}
	  .yzm-msg-body p a{font-size:12px;color:#333;text-decoration:none;}
	  .yzm-msg-body p a:hover{color:#337ab7;}
	</style>
</head>
<body>
	<div class="yzm-msg">        	
		<div class="yzm-msg-title">提示信息</div>
		<div class="yzm-msg-body">
			<div class="yzm-info"><?php echo $msg;?></div>
			<?php if(!$stop){?>
			<p>本页面将在<span style="color:red;font-weight:bold;margin:0 5px;"><?php echo $limittime;?></span>秒后跳转...</p>
			<?php }else{?>
			<p><a href="<?php echo htmlspecialchars(HTTP_REFERER); ?>" title="点击返回上一页">点击返回上一页</a></p>
			<?php }?>
		</div>
	</div> 
</body>
</html>