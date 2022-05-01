/**
 * YZMCMS内容管理系统  (yzm cms轻量级开源CMS)
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 */

function do_login(obj, url){
	if(obj.username.value==''){
		layer.msg('用户名不能为空！', {icon:2});
		return false;
	}
	if(obj.password.value==''){
		layer.msg('密码不能为空！', {icon:2});
		return false;
	}
    $.ajax({
        type: "post",
        url: url,
        data: $(obj).serialize(),
        dataType: "json", 
        success: function (msg) {
            if(msg.status == 1){
                layer.msg(msg.message, {icon:1, time:800}, function(){
                    location.href = msg.url;
                });
            }else if(msg.status == -1){
				layer.msg(msg.message, {icon:2,time: 1500});
				$("#codeimg").attr('src',$("#codeimg").attr('src') + '?' + Math.random());
			}else{
                layer.msg(msg.message, {icon:2});
                $("#codeimg").attr('src',$("#codeimg").attr('src') + '?' + Math.random());
            }
        }
    });
    return false;
}


function check_register(obj, url){ 
	if(obj.username.value==''){
		layer.msg('用户名不能为空！', {icon:2});
		return false;
	}
	if(obj.email.value==''){
		layer.msg('邮箱地址不能为空！', {icon:2});
		return false;
	}
	if(obj.password.value.length < 6){
		layer.msg('密码长度不能低于6位！', {icon:2});
		return false;				  
	}
	if(obj.password.value != obj.password2.value){
		layer.msg('两次密码不一致！', {icon:2});
		return false;				  
	}
	if(obj.code.value == ''){
		layer.msg('验证码不能为空！', {icon:2});
		return false;
	}
	if(!$('#agree').attr('checked')){
		layer.msg('你必须同意注册协议！', {icon:2});
		return false;
	} 
	layer.msg('正在注册……', { icon: 16, shade: 0.21, time:false }); 
	$.ajax({
	    type: "post",
	    url: url,
	    data: $(obj).serialize(),
	    dataType: "json", 
	    success: function (msg) {
	        if(msg.status == 1){
	            layer.msg(msg.message, {icon:1, time:2500}, function(){
	                location.href = msg.url;
	            });
	        }else if(msg.status == -1){
				layer.msg(msg.message, {icon:2,time: 1500});
				$("#codeimg").attr('src',$("#codeimg").attr('src') + '?' + Math.random());
			}else{
	            layer.msg(msg.message, {icon:2});
	            $("#codeimg").attr('src',$("#codeimg").attr('src') + '?' + Math.random());
	        }
	    }
	});
	return false;
} 


function show_protocol() {
    layer.open({
        type: 1,
        title: '会员注册协议',
        skin: 'layui-layer-rim',
        area: ['550px', '330px'],
        content: $("#register_protocol")
    });
}