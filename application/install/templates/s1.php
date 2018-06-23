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
            <div class="section">
                <div class="main cc">
		<div class="pact">
				  <p>安装环境：PHP5+MYSQL5以上</p>
				  <p style="color:red;margin:5px 0;">为了使你正确并合法的使用本软件，请你在使用前务必阅读清楚下面的协议条款：</p>
				  <p><strong>一、本授权协议适用于 YzmCMS内容管理系统所有版本，YzmCMS内容管理系统 官方拥有对本授权协议的最终解释权。</strong></p>
				  <br>
				  <strong>二、协议许可的权利 </strong>
				  <p>1、您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。 </p>
				  <p>2、您可以在协议规定的约束和限制范围内修改 YzmCMS内容管理系统 源代码或界面风格以适应您的网站要求。 </p>
				  <p>3、您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。 </p>
				  <br>
				  <strong>二、协议规定的约束和限制 </strong>
				  <p>1、未经官方许可，不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。</p>
				  <p>2、未经官方许可，禁止在 YzmCMS内容管理系统 的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。</p>
				  <p>3、如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。 </p>
				  <br>
				  <strong>三、有限担保和免责声明 </strong>
				  <p>1、本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。 </p>
				  <p>2、用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺对免费用户提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。 </p>
				  <p>3、电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始确认本协议并安装 YzmCMS内容管理系统，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。</p>
				  <p>4、如果本软件带有其它软件的整合api示范例子包，这些文件版权不属于本软件官方，并且这些文件是没经过授权发布的，请参考相关软件的使用许可合法的使用。</p>
				  <p>版权所有 &copy;2014-2017，袁志蒙工作室 保留所有权利。 </p>
		</div>
                </div>
                <div class="bottom tac"> <a href="./index.php?step=2" class="btn">接 受</a> </div>
            </div>
        </div>
        <?php require './templates/footer.php'; ?>
    </body>
</html>