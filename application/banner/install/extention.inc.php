<?php
defined('IN_YZMPHP') or exit('Access Denied');
defined('INSTALL') or exit('Access Denied');

$parentid = $menu->insert(array('name'=>'轮播图管理', 'parentid'=>3, 'm'=>'banner', 'c'=>'banner', 'a'=>'init', 'data'=>'', 'listorder'=>1, 'display'=>'1'));
$menu->insert(array('name'=>'添加轮播', 'parentid'=>$parentid, 'm'=>'banner', 'c'=>'banner', 'a'=>'add', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'修改轮播', 'parentid'=>$parentid, 'm'=>'banner', 'c'=>'banner', 'a'=>'edit', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'删除轮播', 'parentid'=>$parentid, 'm'=>'banner', 'c'=>'banner', 'a'=>'del', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'轮播排序', 'parentid'=>$parentid, 'm'=>'banner', 'c'=>'banner', 'a'=>'order', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'添加轮播分类', 'parentid'=>$parentid, 'm'=>'banner', 'c'=>'banner', 'a'=>'cat_add', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
$menu->insert(array('name'=>'管理轮播分类', 'parentid'=>$parentid, 'm'=>'banner', 'c'=>'banner', 'a'=>'cat_manage', 'data'=>'', 'listorder'=>0, 'display'=>'0'));
