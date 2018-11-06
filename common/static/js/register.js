/**
 * 
 * 作者：袁志蒙
 * 作用：会员注册验证
 * 版权：YzmCMS版权所有
 * 网址：http://www.yzmcms.com
 * 最后修改时间：2018-03-09
 * 
 */ 
 
$(function(){
   $("#username").blur(function(){
	  checkname();
   });

   $("#email").blur(function(){
	  checkemail();
   });
   		   
})



function checkpass(){
	 if($("#password").val().length < 6){
		  layer.msg('密码不能低于6位', {icon:2,time: 1000});
		  return false;				  
	 }
	 return true;
}

function checkpass2(){
	if($("#password").val() != $("#password2").val()){
		  layer.msg('两次密码不一致', {icon:2,time: 1000});
		  return false;				  
	}
	return true;
}

function checkall(){ 
	 if(!(checkname() && checkemail())){
		 return false;
	 }
     if(!(checkpass() && checkpass2())) {
		 return false;
	 }
	 if($("#code").val() == ''){
	   layer.msg('验证码不能为空', {icon:2,time: 1000});
	   return false;
	 }
	 if(!$('#agree').attr('checked')){
	   layer.msg('你必须同意注册协议', {icon:2,time: 1000});
	   return false;
	 }  
   return true;
} 
