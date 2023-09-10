<?php defined('IN_YZMCMS') or exit('No Define YzmCMS.'); ?>
<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
        <link rel="stylesheet" href="./css/install.css?v=yzmcms" />
    </head>
    <body>
        <div class="wrap">
            <?php require './templates/header.php'; ?>
            <section class="section">
                <div class="step">
                    <ul>
                        <li class="first current">检测环境</li>
                        <li class="current">创建数据</li>
                        <li>完成安装</li>
                    </ul>
                </div>
                <form action="index.php?step=4" method="post" onsubmit="return dosubmit(this)">
                    <input type="hidden" name="force" value="0" />
                    <div class="server">
                        <table width="100%">
                            <tr>
                                <td class="td1" width="120">数据库信息</td>
                                <td width="200">&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="tar">数据库驱动类型：</td>
                                <td>
								<select name="dbtype" id="dbtype" class="select">
									<option value="pdo" >PDO_MYSQL (推荐)</option>
									<option value="mysqli" >MYSQLI</option>
									<!-- <option value="mysql" >MYSQL</option> -->
								</select>
								</td>
                                <td><div id="J_install_tip_dbhost"><span class="gray">均支持MySql数据库，推荐使用PDO_MYSQL</span></div></td>
                            </tr>
                            <tr>
                                <td class="tar">数据库服务器：</td>
                                <td><input type="text" name="dbhost" id="dbhost" value="127.0.0.1" class="input"></td>
                                <td><div id="J_install_tip_dbhost"><span class="gray">数据库服务器地址，一般为127.0.0.1</span></div></td>
                            </tr>
                            <tr>
                                <td class="tar">数据库端口：</td>
                                <td><input type="text" name="dbport" id="dbport" value="3306" class="input"></td>
                                <td><div id="J_install_tip_dbport"><span class="gray">数据库服务器端口，一般为3306</span></div></td>
                            </tr>
                            <tr>
                                <td class="tar">数据库用户名：</td>
                                <td><input type="text" name="dbuser" id="dbuser" value="root" class="input"></td>
                                <td><div id="J_install_tip_dbuser"></div></td>
                            </tr>
                            <tr>
                                <td class="tar">数据库密码：</td>
                                <td><input type="text" name="dbpw" id="dbpw" value="" class="input" autoComplete="off"></td>
                                <td><div id="J_install_tip_dbpw"></div></td>
                            </tr>
                            <tr>
                                <td class="tar">数据库名：</td>
                                <td><input type="text" name="dbname" id="dbname" value="yzmcms" class="input"></td>
                                <td><div id="J_install_tip_dbname"></div></td>
                            </tr>
                            <tr>
                                <td class="tar">数据库表前缀：</td>
                                <td><input type="text" name="dbprefix" id="dbprefix" value="yzm_" class="input"></td>
                                <td><div id="J_install_tip_dbprefix"><span class="gray">如无特殊需要，请不要修改</span></div></td>
                            </tr>
                        </table>
                        <table width="100%">
                            <tr>
                                <td class="td1" width="120">网站配置</td>
                                <td width="200">&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="tar">网站名称：</td>
                                <td><input type="text" name="sitename" value="YzmCMS演示站" class="input"></td>
                                <td><div id="J_install_tip_sitename"></div></td>
                            </tr>
                            <tr>
                                <td class="tar">网站域名：</td>
                                <td><input type="text" name="siteurl" value="<?php echo $domain ?>" id="siteurl" class="input" autoComplete="off"></td>
                                <td><div id="J_install_tip_siteurl"><span class="gray">请以“/”结尾</span></div></td>
                            </tr>
<!--                            <tr>
                                <td class="tar">关键词：</td>
                                <td><input type="text" name="sitekeywords" value="" class="input" autoComplete="off"></td>
                                <td><div id="J_install_tip_sitekeywords"></div></td>
                            </tr>
                            <tr>
                                <td class="tar">描述：</td>
                                <td><textarea class="input" name="siteinfo"></textarea></td>
                                <td><div id="J_install_tip_siteinfo"></div></td>
                            </tr>  -->
                        </table>
                        <table width="100%">
                            <tr>
                                <td class="td1" width="120">创始人信息</td>
                                <td width="200">&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td class="tar">管理员用户名：</td>
                                <td><input type="text" name="manager_adminname" class="input" value="yzmcms"></td>
                                <td><div id="J_install_tip_manager_adminname"><span class="gray">管理员用户名不能包含特殊字符</span></div></td>
                            </tr>
                            <tr>
                                <td class="tar">密码：</td>
                                <td><input type="text" name="manager_pwd" id="J_manager_pwd" class="input" value="yzmcms" autoComplete="off"></td>
                                <td><div id="J_install_tip_manager_pwd"><span class="gray">密码长度为6-20位</span></div></td>
                            </tr>
                            <tr>
                                <td class="tar">重复密码：</td>
                                <td><input type="text" name="manager_ckpwd" class="input" value="yzmcms" autoComplete="off"></td>
                                <td><div id="J_install_tip_manager_ckpwd"><span class="gray">密码长度为6-20位</span></div></td>
                            </tr>
                        </table>
                        <input type="hidden" name="webPath" value="<?php echo $rootpath?>/" />
                        <div id="J_response_tips" style="display:none;"></div>
                    </div>
                    <div class="bottom tac"> 
                        <a href="./index.php?step=2" class="btn">上一步</a>
						<input type="submit" class="btn btn_submit J_install_btn" value="创建数据">
                    </div>
                </form>
            </section>
            <div  style="width:0;height:0;overflow:hidden;"> <img src="./images/pop_loading.gif"> </div>
            <script src="../../common/static/js/jquery-1.8.2.min.js"></script>
            <script src="../../common/static/plugin/layer/layer.js"></script>
            <script>
                function TestDbPwd(){
					var db_result = false;

                    var dbType = $("#dbtype").val();;
                    var dbHost = $('#dbhost').val();
                    var dbUser = $('#dbuser').val();
                    var dbPw = $('#dbpw').val();
                    var dbName = $('#dbname').val();
                    var dbPort = $('#dbport').val();
                    data={'dbtype':dbType,'dbhost':dbHost,'dbuser':dbUser,'dbpw':dbPw,'dbname':dbName,'dbport':dbPort};
                    var url =  "./index.php?step=3&testdbpwd=1";
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: data,
                        async: false, 
                        success: function(msg){
                            if(msg==1){
								db_result = true;
                            }
                        }
                    });
                    return db_result;
                }

                function dosubmit(obj) {
                    var reg = /^http(.+)\/$/;
                    if(obj.dbhost.value == ''){
                        layer.msg('数据库服务器不能为空!', {icon:2});
                        return false;
                    }
                    if(obj.dbport.value == ''){
                        layer.msg('数据库端口不能为空!', {icon:2});
                        return false;
                    }
                    if(obj.dbuser.value == ''){
                        layer.msg('数据库用户名不能为空!', {icon:2});
                        return false;
                    }
                    if(obj.dbname.value == ''){
                        layer.msg('数据库名不能为空!', {icon:2});
                        return false;
                    }
                    if(obj.sitename.value == ''){
                        layer.msg('网站名称不能为空!', {icon:2});
                        return false;
                    }
                    if(!reg.test(obj.siteurl.value)){
                        layer.msg('网站域名格式不正确!', {icon:2});
                        return false;
                    }
                    if(obj.manager_adminname.value.length<3 || obj.manager_adminname.value.length>20){
                        layer.msg('管理员用户名长度必须为3-20位!', {icon:2});
                        return false;
                    }
                    var reg = /^[a-zA-Z]{1}([a-zA-Z0-9]|[_]){0,19}$/;   
                    if(!reg.test(obj.manager_adminname.value)) {
                        layer.msg('管理员用户名必须为英文字母开头、可以包含数字或下划线!', {icon:2});
                        return false;
                    }
                    if(obj.manager_pwd.value.length < 6 || obj.manager_pwd.value.length > 20){
                        layer.msg('管理员密码长度必须为6-20位!', {icon:2});
                        return false;
                    }
                    if(obj.manager_pwd.value !== obj.manager_ckpwd.value){
                        layer.msg('管理员两次密码值不相等!', {icon:2});
                        return false;
                    }
                    if(!TestDbPwd()){
						layer.msg('数据库连接失败，请检查配置!', {icon:2});
                        return false;
                    }
                    return true;
                }
                
            </script>
        </div>
        <?php require './templates/footer.php'; ?>
    </body>
</html>