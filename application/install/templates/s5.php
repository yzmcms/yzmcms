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
                <div class="success_tip cc"> 
                    <a href="http://<?php echo $domain ?>/index.php?m=admin" class="f16 b">安装完成，进入后台管理</a>
                    <p>进入后台以后，第一件事是 <span class="c-red">内容管理-批量更新URL</span>，不然有些功能不正常！<p>
                    <p>官方网站：<a href="http://www.yzmcms.com/" target="_blank">http://www.yzmcms.com</a></p>
                    <p>论坛支持：<a href="http://bbs.yzmcms.com/" target="_blank">http://bbs.yzmcms.com</a></p>
                </div>
            </section>
        </div>
        <?php require './templates/footer.php'; ?>
    </body>
</html>