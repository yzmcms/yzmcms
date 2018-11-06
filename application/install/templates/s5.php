<?php defined('IN_YZMCMS') or exit('No Define YzmCMS.'); ?>
<!doctype html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
        <link rel="stylesheet" href="./css/install.css?v=9.0" />
    </head>
    <body>
        <div class="wrap">
            <?php require './templates/header.php'; ?>
            <section class="section">
                <div class="">
                    <div class="success_tip cc"> <a href="http://<?php echo $domain ?>/index.php?m=admin" class="f16 b">安装完成，进入后台管理</a></div>
                    <div class=""> </div>
                </div>
            </section>
        </div>
        <?php require './templates/footer.php'; ?>
    </body>
</html>