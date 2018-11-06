<?php
defined('IN_YZMPHP') or exit('Access Denied');
defined('INSTALL') or exit('Access Denied');

$parentid = $menu->insert(array('name'=>'友情链接管理', 'parentid'=>3, 'm'=>'link', 'c'=>'link', 'a'=>'init', 'data'=>'', 'listorder'=>1, 'display'=>'1'));
$menu->insert(array('name'=>'添加友情链接', 'parentid'=>$parentid, 'm'=>'link', 'c'=>'link', 'a'=>'add', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'修改友情链接', 'parentid'=>$parentid, 'm'=>'link', 'c'=>'link', 'a'=>'edit', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'删除单个友情链接', 'parentid'=>$parentid, 'm'=>'link', 'c'=>'link', 'a'=>'del_one', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'删除多个友情链接', 'parentid'=>$parentid, 'm'=>'link', 'c'=>'link', 'a'=>'del', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'友情链接排序', 'parentid'=>$parentid, 'm'=>'link', 'c'=>'link', 'a'=>'order', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'审核友情链接', 'parentid'=>$parentid, 'm'=>'link', 'c'=>'link', 'a'=>'adopt', 'data'=>'', 'listorder'=>0, 'display'=>'0'));


//以下代码可选，预装数据
$link = D('link');
$link->insert(array('name'=>'YzmCMS官方网站','url'=>'http://www.yzmcms.com','username'=>'袁志蒙','email'=>'214243830@qq.com','status'=>'1','addtime'=>SYS_TIME));
$link->insert(array('name'=>'YzmCMS官方博客','url'=>'http://blog.yzmcms.com/','username'=>'袁志蒙','email'=>'214243830@qq.com','status'=>'1','addtime'=>SYS_TIME));

