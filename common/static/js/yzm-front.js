/**
 * YZMCMS内容管理系统  (yzm cms轻量级开源CMS)
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 */

$(function(){ 

	$("#searchselected").click(function(){ 
		$("#searchtab").toggle();
		if($(this).hasClass('searchopen')){
			$(this).removeClass("searchopen");
		}else{
			$(this).addClass("searchopen");
		}
	}); 

	$("#searchtab li").hover(function(){
		$(this).addClass("selected");
	},function(){
		$(this).removeClass("selected");
	});
	 
	$("#searchtab li").click(function(){
		$("#modelid").val($(this).attr('data') );
		$("#searchselected").html($(this).html());
		$("#searchtab").hide();
		$("#searchselected").removeClass("searchopen");
	});


	$(".yzm-nav>li").hover(function(){
		$(this).children('ul').stop(true,true).slideDown(200);
	},function(){
		$(this).children('ul').stop(true,true).slideUp(200);
	})
	
});

// 做最好用的开源CMS: YzmCMS ，官方QQ交流群：161208398

function toreply(obj){
    if($("#rep_" + obj).css("display") == "none"){
        $("#rep_" + obj + " .yzm-comment-reply-code img").attr('src', $("#rep_" + obj + " .yzm-comment-reply-code img").attr("src") + "?");
        $("#rep_" + obj).css("display", "block");
    }else{
        $("#rep_" + obj).css("display", "none");
    }
}

function check_comm(obj){
	if(obj.content.value === ''){
	    layer.msg('你不打算说点什么吗？', {icon:2});
		return false;
	}
	$.ajax({
		type: "POST",
		url: $(obj).attr("action"), 
		data: $(obj).serialize(),
		dataType: "json", 
		success: function (msg) {
			if(msg.status == 1){
				layer.msg(msg.message, {icon:1}, function(){
					location.reload();
				});
			}else{
				$(obj).find('img').attr('src',$(obj).find('img').attr('src') + '?' + Math.random());
				layer.msg(msg.message, {icon:2});
			}
		}
	})		 
	return false;
}

function check_rep(obj){
	if(obj.content.value === ''){
	    layer.msg('你不打算说点什么吗？', {icon:2});
		return false;
	}
	$.ajax({
		type: "POST",
		url: $(obj).attr("action"), 
		data: $(obj).serialize(),
		dataType: "json", 
		success: function (msg) {
			if(msg.status == 1){
				layer.msg(msg.message, {icon:1}, function(){
					location.reload();
				});
			}else{
				$(obj).find('img').attr('src',$(obj).find('img').attr('src') + '?' + Math.random());
				layer.msg(msg.message, {icon:2});
			}
		}
	})		 
	return false;
}