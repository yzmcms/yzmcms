<?php defined('IN_YZMCMS') or exit('No Define YzmCMS.'); ?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8" />
<title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
<link rel="stylesheet" href="./css/install.css?v=yzmcms" />
<script src="../../common/static/js/jquery-1.8.2.min.js"></script>
<script src="../../common/static/plugin/layer/layer.js"></script>
</head>
<body>
<div class="wrap">
  <?php require './templates/header.php';?>
  <section class="section">
    <div class="step">
      <ul>
            <li class="first current">检测环境</li>
            <li class="current">创建数据</li>
            <li class="current">完成安装</li>
      </ul>
    </div>
    <div class="install" id="log">
      <ul id="loginner">
      </ul>
    </div>
    <div class="bottom tac"> <a href="javascript:;" class="btn_old"><img src="./images/loading.gif" class="yzm-loading" align="absmiddle" />&nbsp;正在安装...</a> </div>
  </section>
  <script type="text/javascript">
var n=0;
    var data = <?php echo json_encode($_POST);?>;
    $.ajaxSetup ({ cache: false });
    function reloads(n) {
        var url =  "./index.php?step=4&install=1&n="+n;
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            dataType: 'json',
            beforeSend:function(){
            },
            success: function(msg){
                if(msg.n=='999999'){
                    $('#dosubmit').attr("disabled",false);
                    $('#dosubmit').removeAttr("disabled");
                    $('#dosubmit').removeClass("nonext");
                    setTimeout('gonext()',2000);
                }else{
					if(msg.n){
						$('#loginner').append(msg.msg);
						reloads(msg.n);
					}else{
						//alert('指定的数据库不存在，系统也无法创建，请先通过其他方式建立好数据库！');
						layer.alert(msg.msg);
					}					
				}
            },
            error: function (xhr) {
                if(xhr.status != 200){
                    layer.alert('安装时出错，可能被系统防火墙拦截，状态码：'+xhr.status);
                    return false;
                }
            }
        });
    }
    function gonext(){
        window.location.href='./index.php?step=5';
    }
    $(document).ready(function(){
        reloads(n);
    })
</script>
</div>
<?php require './templates/footer.php';?>
</body>
</html>