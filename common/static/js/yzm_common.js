/**
 * YzmCMS 公共js文件 
 * 
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2019-04-12
 */

 
//删除一条记录
function yzm_del(url){
	layer.confirm('确认要删除吗？',function(index){
		window.location.href = url;
	});
}


//删除多条记录
function yzm_dels(name){
	if ($("input[name='"+name+"[]']:checked").length<1){
	   layer.alert('请勾选信息！');
	   return false;
	}	
	layer.confirm('确认要删除吗？',function(index){
		document.getElementById('myform').submit();
	});
}


//打开页面
function yzm_open(title,url,w,h){
	if (title == null || title == '') {
		title=false;
	};
	if (url == null || url == '') {
		url="404.html";
	};
	if (w == null || w == '') {
		w=800;
	};
	if (h == null || h == '') {
		h=($(window).height() - 50);
	};
	layer.open({
		type: 2,
		area: [w+'px', h +'px'],
		fix: false, 
		// maxmin: true,
		shade:0.4,
		title: title,
		content: url
	});
} 

 
//大窗口打开页面
function yzm_open_full(title,url){
	var index = layer.open({
		type: 2,
		title: title,
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
		layer.msg('请先上传图片！');
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


//图片预览
function yzm_img_preview(id, src){
	if(src == '') return;
	layer.tips('<img src="'+htmlspecialchars(src)+'" style="max-width:180px" >', '#'+id, {
	  tips: [1, '#fff']
	});	
}


//图片预览，可判断类型
function yzm_img_browse(obj, src){
	if(src == '') return false;
	var ext = src.substring(src.length,src.lastIndexOf('.'));
	if(ext!='.png' && ext!='.jpg' && ext!='.gif' && ext!='.jpeg') return false;
	layer.tips('<img src="'+htmlspecialchars(src)+'" height="100">', obj, {
	  tips: [1, '#fff']
	});	
}


//添加远程地址
function yzm_add_attachment(id){
	var string = '<li>文件：<input type="text" name="'+id+'[url][]" value="" onmouseover="yzm_img_browse(this, this.value)" onmouseout="layer.closeAll();" class="input-text w_300"> 描述：<input type="text" name="'+id+'[alt][]" value="" class="input-text w_200"><a href="javascript:;" onclick="remove_li(this);">删除</a></li>';
	
	$("#"+id).append(string);	
}


//删除多文件上传
function remove_li(obj){
	 $(obj).parent().remove();
}


//html实体转换
function htmlspecialchars(str)  {  
    str = str.replace(/&/g, '&amp;');
    str = str.replace(/</g, '&lt;');
    str = str.replace(/>/g, '&gt;');
    str = str.replace(/"/g, '&quot;');
    str = str.replace(/'/g, '&#039;');
    return str;
}


//新窗口打开
function yzm_win_open(url,name,w,h) {
	if(!w) w=screen.width;
	if(!h) h=screen.height;
	var winobj = window.open(url,name,"width=" + w + ",height=" + h + ",toolbar=no,menubar=no,scrollbars=yes,resizable=yes,location=no,status=no");
	var loop = setInterval(function(){
		if(winobj.closed){
			clearInterval(loop);
			location.reload();
		}
	},1000);
}