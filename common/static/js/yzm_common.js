/**
 * YzmCMS 公共js文件 (yzm cms轻量级开源CMS)
 * 
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2020-05-12
 */

 
//删除一条记录
function yzm_del(url){
	var is_ajax = arguments[1] || 0;
	var symbol = url.indexOf('?')<0 ? '?' : '&';
	url += symbol+'yzm_csrf_token='+yzm_csrf_token;
	layer.confirm('确认要删除吗？', {title:"操作提示"}, function(index){
		if(!is_ajax){
			window.location.href = url;
		}else{
			layer.msg('正在操作中……', {icon:16, time:0, shade:0.3});
			$.ajax({
		        type: "GET",
		        url: url, 
			    dataType: "json", 
		        success: function (msg) {
		        	if(msg.status == 1){
		        		layer.msg(msg.message, {icon:1,time:1000},function(){
		        			window.location.reload();
		        		});
		        	}else{
		        		layer.msg(msg.message, {icon:2,time:2500});
		        	}
		        }
		    })
		}
	});
}


//删除多条记录
function yzm_dels(name){
	var is_ajax = arguments[1] || 0;
	if ($("input[name='"+name+"[]']:checked").length<1){
	   layer.alert('请选择要操作的记录！', {title:"操作提示"});
	   return false;
	}	
	layer.confirm('确认要删除吗？', {title:"操作提示"}, function(index){
		if(!is_ajax){
			$("#myform").submit();
		}else{
			layer.msg('正在操作中……', {icon:16, time:0, shade:0.3});
			$.ajax({
		        type: "POST",
		        url: $("#myform").attr("action"), 
		        data: $("#myform").serialize(),
			    dataType: "json", 
		        success: function (msg) {
		        	if(msg.status == 1){
		        		layer.msg(msg.message, {icon:1,time:1000},function(){
							window.location.reload();
						});
		        	}else{
		        		layer.msg(msg.message, {icon:2,time:2500});
		        	}
		        }
		    })
		}
	});
}


//确认窗口
function yzm_confirm(url, title, is_ajax, is_filter) {
	if (is_ajax == undefined || is_ajax == null) {
		is_ajax = false;
	}
	if (is_filter == undefined || is_filter == null) {
		is_filter = true;
	}
	var symbol = url.indexOf('?')<0 ? '?' : '&';
	url += symbol+'yzm_csrf_token='+yzm_csrf_token;
	title = is_filter ? yzm_htmlspecialchars(title) : title;
	layer.confirm(title, {title:"操作提示"}, function(index){
		if(!is_ajax){
			window.location.href = url;
		}else{
			layer.msg('正在操作中……', {icon:16, time:0, shade:0.3});
			$.ajax({
		        type: "GET",
		        url: url, 
			    dataType: "json", 
		        success: function (msg) {
		        	if(msg.status == 1){
		        		layer.msg(msg.message, {icon:1,time:1000},function(){
		        			window.location.reload();
		        		});
		        	}else{
		        		layer.msg(msg.message, {icon:2,time:2500});
		        	}
		        }
		    })
		}
	});
}


//打开页面
function yzm_open(title,url,w,h){
	if (w == undefined || w == null) {
		w = ($(window).width() * 0.8);
	}
	if (h == undefined || h == null) {
		h = ($(window).height() * 0.8);
	}
	layer.open({
		type: 2,
		area: [w+'px', h +'px'],
		fix: false, 
		// maxmin: true,
		shade:0.4,
		title: yzm_htmlspecialchars(title),
		content: url
	});
} 

 
//大窗口打开页面
function yzm_open_full(title,url){
	var index = layer.open({
		type: 2,
		title: yzm_htmlspecialchars(title),
		content: url
	});
	layer.full(index);
}


//关闭弹出层
function yzmcms_close(){
	var index = parent.layer.getFrameIndex(window.name);
	parent.layer.close(index);
}


//上传附件
function yzm_upload_att(url){
	layer.open({
      type: 2,
      title: '上传附件',
      area: ['500px', '430px'],
      content: url
    });
}


//图像裁剪
function yzm_img_cropper(cid, url){
	var str = $('#' + cid).val();
	if(str == ''){
		layer.msg('请先上传图片！', {icon:2,time:2500});
		return false;
	}
	if(url.indexOf('?') != -1) {
		url = url+'&f=' + window.btoa(unescape(encodeURIComponent(str))) + '&cid='  + cid;
	} else {
		url = url+'?f=' + window.btoa(unescape(encodeURIComponent(str))) + '&cid='  + cid;
	}
	layer.open({
      type: 2,
      title: '图像裁剪',
      area: ['770px', '510px'],
      content: url
    });
}


//图片预览(根据id)
function yzm_img_preview(id, src){
	if(src == '') return;
	var ext = src.substr(src.lastIndexOf(".")+1);
	if(['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'ico'].indexOf(ext.toLowerCase()) === -1) return;
	layer.tips('<img src="'+yzm_htmlspecialchars(src)+'" style="max-width:180px;max-height:250px" >', '#'+id, {
	  tips: [1, '#fff']
	});	
}


//图片预览(根据对象)
function yzm_img_browse(obj, src){
	if(src == '') return;
	var ext = src.substr(src.lastIndexOf(".")+1);
	if(['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'ico'].indexOf(ext.toLowerCase()) === -1) return;
	layer.tips('<img src="'+yzm_htmlspecialchars(src)+'" style="max-width:180px;max-height:250px">', obj, {
	  tips: [1, '#fff']
	});	
}


//添加远程地址
function yzm_add_attachment(id){
	var string = '<li>文件：<input type="text" name="'+id+'[url][]" value="" onmouseover="yzm_img_browse(this, this.value)" onmouseout="layer.closeAll(\'tips\')" class="input-text yzm-input-url"> 描述：<input type="text" name="'+id+'[alt][]" value="" class="input-text yzm-input-alt"><a href="javascript:;" class="secondary" onclick="yzm_move_li(this, 1);">上移</a> <a href="javascript:;" class="secondary" onclick="yzm_move_li(this, 0);">下移</a> <a href="javascript:;" class="danger" onclick="yzm_delete_li(this);">删除</a></li>';
	
	$("#"+id).append(string);	
}


//删除多文件
function yzm_delete_li(obj){
	 $(obj).parent().remove();
}


//多文件上下移动
function yzm_move_li(obj, is_up) {
	if(is_up){
		var prevLi = $(obj).parent("li").prev();
		if(prevLi.length){
		    prevLi.before($(obj).parent("li"));
		}
	}else{
	    var nextLi = $(obj).parent("li").next();
	    if(nextLi.length){
	        nextLi.after($(obj).parent("li"));
	    }
	}
}


//html实体转换
function yzm_htmlspecialchars(str)  {  
    str = str.replace(/&/g, '&amp;');
    str = str.replace(/</g, '&lt;');
    str = str.replace(/>/g, '&gt;');
    str = str.replace(/"/g, '&quot;');
    str = str.replace(/'/g, '&#039;');
    return str;
}


//新窗口打开
function yzm_win_open(url,name,w,h) {
	if (w == undefined || w == null) {
		w = screen.width;
	}
	if (h == undefined || h == null) {
		h = screen.height;
	}
	var winobj = window.open(url,name,"width=" + w + ",height=" + h + ",toolbar=no,menubar=no,scrollbars=yes,resizable=yes,location=no,status=no");
	var loop = setInterval(function(){
		if(winobj.closed){
			clearInterval(loop);
			location.reload();
		}
	},1000);
}


//设置每页展示条数
function yzm_page_size(obj) {
	var url = $(obj).data('url').replace("PAGE", 1);
	if(url.indexOf('page_size')<0){
		var page_size = url.indexOf('page_size')<0 ? (url.indexOf('?')<0 ? '?' : '&')+'page_size='+obj.value : '';
		url = url+page_size;
	}else{
		url = url.replace(/\/page_size\/[0-9]*/i, '/page_size/'+obj.value);
		url = url.replace(/page_size=[0-9]*/i, 'page_size='+obj.value);
	}
	window.location.href = url;
}


//跳转到指定页
function yzm_page_jump(obj) {
	var theEvent = window.event;
	if (theEvent.keyCode != 13)  return true;
	var url = $(obj).data('url');
	var page = $(obj).val();
	if(page == '' || isNaN(page)){
		alert('请输入正确的页码！');
		return false;
	}
	url = url.replace("PAGE", page);
	window.location.href = url;
	return false;
}


//自动提示
function yzm_auto_tips(){
    var html = '<a class="yzm-tips" href="javascript:void(0);" onmouseover="yzm_tips = layer.tips($(this).parent().find(\'span.yzm-explain\').html(), this, {time:100000});" onmouseout="layer.close(yzm_tips);"><i class="yzm-iconfont yzm-iconbangzhu yzm-tips-icon"></i></a>';
    $.each($('.yzm-explain-box > span.yzm-explain'), function(index, item){
        if ($(item).html() != '') {
            $(item).before(html);
        }
    });
}


//状态切换
function yzm_change_status(obj, url) {
	var id = $(obj).data('id');
	var field = $(obj).data('field');
	var value = $(obj).hasClass('yzm-status-disable') ? 1 : 0;

	$.ajax({
        type: "POST",
        url: url, 
        data: {"id":id,"field":field,"value":value,"yzm_csrf_token":yzm_csrf_token},
	    dataType: "json", 
        success: function (msg) {
        	if(msg.status == 1){
        		layer.msg(msg.message, {icon:1,time:800});
        		if(value){
        			$(obj).removeClass('yzm-status-disable').addClass('yzm-status-enable');
        			$(obj).html('<i class="yzm-iconfont">&#xe81f;</i>是');
        		}else{
        			$(obj).removeClass('yzm-status-enable').addClass('yzm-status-disable');
        			$(obj).html('<i class="yzm-iconfont">&#xe601;</i>否');
        		}
        	}else{
        		layer.msg(msg.message, {icon:2,time:2500});
        	}
        }
    })
}


// 查看大图
function yzm_show_img(src, max_width, max_height) {
	var img = '<img src="' + src + '">';
	$(img).load(function() {
		width  = this.width;
		height = this.height;
		if (this.width > max_width) {
			width = max_width + 'px';
			height = 'auto';
		}
		if (this.height > max_height) {
			width = 'auto';
			height = max_height + 'px';
		}
		var string = '<style type="text/css">.layui-layer-page .layui-layer-content{overflow-y:hidden;}</style><img src="'+src+'" style="width:'+width+';height:'+height+';">';
		layer.open({
			type: 1,
			title: false,
			area: [width, height],
			content: string,
			shadeClose: true
		});
	})
}