/**
 * YzmCMS系统更新功能
 * www.yzmcms.com
 * QQ:214243830
 * 袁志蒙
 */

function check_update(auto_update){
	if(!auto_update){
		var mymsg = layer.msg('正在检测更新……', {icon:16, time:0, shade:0.3});
	}
	$.ajax({
        type: "POST",
        url: yzm_admin_url+'admin/store/public_check_update', 
        data: {"yzm_csrf_token": yzm_csrf_token, "auto_update": auto_update},
	    dataType: "json", 
        success: function (msg) {
        	if(msg.status == 0){
        		if(!auto_update) layer.msg(msg.message, {icon:2,time:3000});
        	}else if(msg.status == 1){
        		if(!auto_update) layer.msg(msg.message, {icon:1,time:2000});
        	}else if(msg.status == 2){
        		if(!auto_update) layer.close(mymsg);
        		$("#new_version").html("(发现新版本)");
        		yzm_open('检测更新', yzm_admin_url+'admin/store/public_check_update', 500, 500);
        	}else{
        		$("#new_version").html("(发现新版本)");
        	}
        },
        error: function (xhr) {
        	if(!auto_update) {
        		layer.close(mymsg);
        		layer.alert('检查更新时出错，请确定是否为官方完整版本！<br>或加入QQ群寻求帮助：161208398 。', {icon:2, title:"错误警告"});
        	}
            return false;
        }
    })
}


function yzm_update_ignore(){
	layer.confirm('确定要忽略本次升级吗？<br><span class="c-red">不升级可能会有安全风险！</span>',function(index){
		$.ajax({
	        type: "GET",
	        url: yzm_admin_url+'admin/store/public_update_ignore', 
		    dataType: "json", 
	        success: function (msg) {
	        	if(msg.status){
	        		layer.msg(msg.message, {icon:1,time:1500}, function(){
	        			yzmcms_close();
	        		});
	        	}else{
	        		layer.msg(msg.message, {icon:2,time:3000});
	        	}
	        }
	    })
	});
}


// www.yzmask.com
function yzm_update_exec(){
	var mymsg = layer.msg('正在升级中，请勿刷新或关闭页面！', {icon:16, time:0, shade:0.3});
	$.ajax({
        type: "GET",
        url: yzm_admin_url+'admin/store/public_system_update', 
	    dataType: "json", 
        success: function (msg) {
        	if(msg.status == 0){
        		layer.alert(msg.message, {icon:2});
        	}else if(msg.status == 1){
        		layer.msg(msg.message, {icon:1,time:2000}, function(){
        			top.location.reload();
        		});
        	}else{
        		layer.alert(msg.message, {icon:0});
        	}
        },
        error: function (xhr) {
        	layer.close(mymsg);
            layer.alert('升级过程中出错，请加入QQ群寻求帮助：161208398！', {icon:2, title:"错误警告"});
            return false;
        }
    })
}