<div id="yzmphp_debug" style="<?php if(!self::$info) echo 'display:none;';?>margin:0px;padding:0px;font-size:12px;font-family:Tahoma,Arial,sans-serif;line-height:20px;text-align:left;border-top:1px solid #ddd;color:#333;background:#fff;position:fixed;_position:absolute;bottom:0;left:0;width:100%;z-index:999999;box-shadow:-2px 2px 20px #757575">
	<div style="padding-left:15px;height:36px;line-height:36px;border-bottom:1px solid #ddd;background-color:#f5f5f5;color:#444"><span onclick="close_yzmphp_debug()" style="cursor:pointer;float:right;width:25px;color:#333;padding-top:10px;overflow:hidden;"><img style="height:18px;vertical-align:top;" title="关闭" alt="关闭" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAABOUlEQVRYR+2W0W0CMRBE33VACXQAdAAlpAJIBaGTKBUk6SAdJB0AHaQESogG3UknY+Pd9cfl4+7Tsj3v1rOr6Zj46ybWZwb4txVYAC/AJ/Db6JMlsAfegGt6V6kCB+C9P7ADzkGINfAN6IeegQ8rgA78AKsGiLH4Bdh6KiDQFgiTuERqJoxAmMUtAN5KuMStAFYIt7gHoAYREvcClCC0PrRa0e2lNq6ZMHcuNeYA5haPVGAAGkNoLSTeAjB+c92jERuamJEnSA0ngPDE9ALk3C6A8Nj2ADxqtcjEvPnJCmDp8xCEBcAinusOkzFrAB7xEMQjgIi4G6IEoBh16jNBdMikntjk4l0J4Ai8tky4vhRjiCfgyxrJtE8RSlnwLkg686EglDGVB82h1KkR317rgvjNxpMzwOQV+AM8QnIhRC5g4gAAAABJRU5ErkJggg=="></span><span onclick="min_yzmphp_debug()" style="cursor:pointer;float:right;color:#333;padding:0 10px;margin-right:10px;" title="最小化">—</span>
	<span style="font-size:14px">运行信息( <span style="color:red"><?php echo self::spent();?></span> 秒):</span>
	</div>
	<div style="clear:both;margin:0px;padding:0 10px;height:200px;overflow:auto;">
	<?php
		if(self::$info){
			echo '<div style="margin-top:5px;">［系统信息］</div>';
			foreach(self::$info as $info){
				echo '<div style="padding-left:20px">'.$info.'</div>';
			}
		}
		if(self::$sqls) {
			echo '<div style="margin-top:5px;">［SQL语句］</div>';
			foreach(self::$sqls as $sql){
				echo '<div style="padding-left:20px">'.$sql.'</div>';
			}
		}
		if(self::$request) {
			echo '<div style="margin-top:5px;">［REQUEST请求］</div>';
			foreach(self::$request as $qe){
				$method = $qe['data'] ? 'POST' : 'GET';
				$data = $qe['data'] ? '<span style="color:#d66b1d;margin:0 5px;">parameter：'.var_export($qe['data'], true).'</span>' : '';
				echo '<div style="padding-left:20px">'.$method.'：'.$qe['url'].$data.'</div>';
			}
		}		
		echo '<div style="margin-top:5px;">［其他信息］</div>';
		echo '<div style="padding-left:20px">WEB服务器：'.$_SERVER['SERVER_SOFTWARE'].'</div>';
		echo '<div style="padding-left:20px">PHP版本：'.PHP_VERSION.'</div>';
		echo '<div style="padding-left:20px">路由信息：模块( '.ROUTE_M.' )，控制器( '.ROUTE_C.' )，方法( '.ROUTE_A.' )，参数( '.$parameter.' )</div>';
		if(session_id()) {
			echo '<div style="padding-left:20px">会话信息：'.session_name().' = '.session_id().'</div>';
		}
		echo '<div style="padding-left:20px">框架版本：'.YZMPHP_VERSION.' <a href="http://www.yzmphp.com" target="_blank" style="color:#888">查看新版</a></div>';
	?>
	</div>
</div>
<div id="yzmphp_open" onclick="show_yzmphp_debug()" title="查看详细" style="<?php if(self::$info) echo 'display:none;';?>height:28px;line-height:28px;border-top-left-radius:3px;z-index:999998;font-family:Tahoma,Arial,sans-serif;float:right;text-align: right;overflow:hidden;position:fixed;_position:absolute;bottom:0;right:0;background:#232323;color:#fff;font-size:14px;padding:0 8px;cursor:pointer;"><?php echo self::spent();?>s
</div>	
<script type="text/javascript">
	function show_yzmphp_debug(){
		document.getElementById('yzmphp_debug').style.display = 'block';
		document.getElementById('yzmphp_open').style.display = 'none';
	}
	function min_yzmphp_debug(){
		document.getElementById('yzmphp_debug').style.display = 'none';
		document.getElementById('yzmphp_open').style.display = 'block';
	}
	function close_yzmphp_debug(){
		document.getElementById('yzmphp_debug').style.display = 'none';
		document.getElementById('yzmphp_open').style.display = 'none';
	}
</script>