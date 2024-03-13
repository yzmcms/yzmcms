/**
 * webuploader for yzmcms
 * www.yzmcms.com
 * QQ: 214243830
 */

function album_cancel(obj){
	if($(obj).hasClass('on')){
		$(obj).removeClass("on");
		$(obj).children(".checkd").addClass("hidden");
		var length = $("a[class='on']").children(".img_src").length;
		var strs = '', tits = '', attids = '';
		for(var i=0;i<length;i++){
            attids += '|'+$("a[class='on']").children(".img_src").eq(i).attr('attid');
			strs += '|'+$("a[class='on']").children(".img_src").eq(i).attr('path');
			tits += '|'+$("a[class='on']").eq(i).attr('title');
		}
        $('#att_id').html(attids);
		$('#att_status').html(strs);
		$('#att_titles').html(tits);
		
	}else{
		var num = $('#att_status').html().split('|').length;
		var file_upload_limit = yzm_uploader_config.fileNumLimit;
		if(num > file_upload_limit) {layer.alert('不能选择超过'+file_upload_limit+'个附件'); return false;}
		$(obj).addClass("on");
		$(obj).children(".checkd").removeClass("hidden");
		$('#att_id').append('|'+$(obj).children(".img_src").attr("attid"));
        $('#att_status').append('|'+$(obj).children(".img_src").attr("path"));
		$('#att_titles').append('|'+$(obj).attr("title"));
	}
}

function isimg(url){
	var sTemp;
	var b = false;
	var opt = "png|jpg|gif|jpeg|bmp|webp|ico";
	var s=opt.toUpperCase().split("|");
	for (var i=0;i<s.length ;i++ ){
	sTemp = url.substr(url.length-s[i].length-1);
	sTemp = sTemp.toUpperCase();
	s[i] = "."+s[i];
	if (s[i]==sTemp){
		b = true;
		break;
	}
	}
	return b;
}


function sizecount(size){
    var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
    for(var i = 0; size >= 1024 && i < 5; i++) {
        size = size / 1024;
    };
    return size.toFixed(2) + units[i];
}


function yzm_att_del(url, name){
    layer.confirm('确认要删除 “ ' + name +' ” 吗？',function(index){
        $.ajax({
            type: "GET",
            url: url, 
            dataType: "json", 
            success: function (msg) {
                if(msg.status == 1){
                    layer.msg(msg.message, {icon:1,time:1000},function(){
                        if(window.location.href.indexOf('tab')<0){
                            window.location.href = window.location.href+'?tab=1';
                        }else{
                            window.location.reload();
                        }
                    });
                }else{
                    layer.msg(msg.message, {icon:2,time:2500});
                }
            }
        })
    });
}

jQuery(function() {

    uploader = WebUploader.create(yzm_uploader_config);

    // 当有文件添加进来的时候
    uploader.on( 'fileQueued', function( file ) {
        var $li = $(
                '<div id="' + file.id + '" class="yzm-file-item">' +
                    '<span class="yzm-file-name" title="' + file.name + '">' + file.name + '</span><span class="yzm-file-size">（' + sizecount(file.size) + '）</span><span class="yzm-per"></span>' +
                '</div>'
                );
        $list.append( $li );
    });

    // 文件上传过程中创建进度条实时显示
    uploader.on( 'uploadProgress', function( file, percentage ) {
        var $li = $( '#'+file.id ),
            $percent = $li.find('.yzm-progress span');

        if ( !$percent.length ) {
            $percent = $('<p class="yzm-progress"><span></span></p>')
                    .appendTo( $li )
                    .find('span');
        }

        $li.find(".yzm-per").text( ' - ' + (percentage * 100).toFixed(2) + '%' );
        $percent.css( 'width', percentage * 100 + '%' );
    });

    // 文件上传成功
    uploader.on( 'uploadSuccess', function( file, data ) {
		if(data.status == 1){
			var att_url = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'ico'].indexOf(data.filetype) !== -1 ? data.msg : (['code','css','dir','doc','docx','gif','html','jpeg','jpg','js','mp3','mp4','pdf','php','png','ppt','pptx','psd','rar','sql','swf','txt','xls','xlsx','xml','zip'].indexOf(data.filetype) !== -1 ? STATIC_URL + 'images/ext/'+data.filetype+'.png' : STATIC_URL + 'images/ext/hlp.png');
			var li='<li><a href="javascript:;" class="on" onclick="album_cancel(this)"><img src="'+att_url+'" class="img_src" path="'+data.msg+'" attid="'+data.attid+'" title="'+data.title+'"/><img src="'+ STATIC_URL +'images/checked.gif" class="checkd"></a></li>';
			$("#uploadlist ul").prepend(li);
            $('#att_id').append('|'+data.attid);
			$('#att_status').append('|'+data.msg);
			$('#att_titles').append('|'+data.title);
		}else{
			layer.alert(data.msg ? data.msg : '服务器错误!'); 
		}
        $( '#'+file.id ).hide(100);
        // $( '#'+file.id ).addClass('yzm-upload-done');
    });

    // 文件上传失败
    uploader.on( 'uploadError', function( file, code ) {
        var $li = $( '#'+file.id ),
            $error = $li.find('div.yzm-upload-error');

        if ( !$error.length ) {
            $( '#'+file.id ).find('.yzm-progress').remove();
            $error = $('<div class="yzm-upload-error"></div>').appendTo( $li );
        }

        $error.text('上传失败，错误码：' + code);
    });

    // 文件上传校验失败
    uploader.on("error", function (type) {
        if (type == "Q_EXCEED_NUM_LIMIT") {
            layer.msg("本次最多允许上传" + yzm_uploader_config.fileNumLimit + "个文件！", {icon:2});
        } else if (type == "F_EXCEED_SIZE") {
            layer.msg("单文件大小不能超过" + sizecount(yzm_uploader_config.fileSingleSizeLimit) + "！", {icon:2});
        } else if (type == "F_DUPLICATE") {
            layer.msg("不允许上传重复文件！", {icon:2});
        } else if (type == "Q_TYPE_DENIED") {
            layer.msg("不允许上传的类型！", {icon:2});
        }else {
            layer.msg("上传出错！错误代码：" + type, {icon:2});
        }
    });

    uploader.on('uploadBeforeSend', function (obj, data, headers) {
        data.open_watermark = $('#open_watermark').attr('checked') ? 1 : 0;
    });

});