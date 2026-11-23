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

	function close_reply_form() {
		$('.reply-form').remove();
	}

	$('.yzm-comment-list-box').on('click', '.comm_reply', function () {
		var $item = $(this).closest('.yzm-comment-item');

		if ($item.find('.reply-form').length) {
			close_reply_form();
			return;
		}

		close_reply_form();
		var info = $item.data('info');
		var captcha_code = info.comment_code ? '<span class="yzm-comment-reply-code"><img src="'+info.site_path+'api/index/code" onclick="this.src=this.src+\'?\'" title="看不清？点击更换"><input type="text" name="code" placeholder="请输入验证码" required="required"></span>' : '';	
		var $form = $(
			'<form class="reply-form" method="post" action="'+info.site_path+'comment/index/init">' +
			'  <textarea name="content" class="textarea textarea2" placeholder="我来说两句~"></textarea>' +
			'  <input type="hidden" name="modelid" value="'+info.modelid+'">' +
			'  <input type="hidden" name="catid" value="'+info.catid+'">' +
			'  <input type="hidden" name="id" value="'+info.id+'">' +
			'  <input type="hidden" name="title" value="'+info.title+'">' +
			'  <input type="hidden" name="url" value="'+location.href+'">' +
			'  <input type="hidden" name="reply" value="'+info.reply+'">' +
			'  <input type="hidden" name="reply_to" value="'+(info.reply_to || '游客')+'">' +
			'  <div class="reply-form-bar">' +captcha_code +
			'    <input type="submit" class="yzm-comment-submit" name="dosubmit" value="发布">' +
			'  </div>' +
			'</form>'
		);
		$item.append($form);

		$form.find('.yzm-comment-submit').on('click', function () {
			do_comment($form, $item);
			return false;
		});
	});
	
});

// 做最好用的开源CMS: YzmCMS ，官方QQ交流群：161208398/633258511

// 发布评论
function do_comment($form, $item = null){
	if(!$form.find('textarea').val().trim()){
	    layer.msg('你不打算说点什么吗？', {icon:2});
		return false;
	}
	$.ajax({
		type: "POST",
		url: $form.attr("action"), 
		data: $form.serialize(),
		dataType: "json", 
		success: function (msg) {
			if(msg.status == 1){
				layer.msg(msg.message, {icon:1}, function(){
					location.reload();
				});
			}else{
				$form.find('img').attr('src',$form.find('img').attr('src') + '?' + Math.random());
				layer.msg(msg.message, {icon:2});
			}
		}
	})		 
	return false;
}