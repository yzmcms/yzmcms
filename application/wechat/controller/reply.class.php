<?php
// +----------------------------------------------------------------------
// | Site:  [ http://www.yzmcms.com]
// +----------------------------------------------------------------------
// | Copyright: 袁志蒙工作室，并保留所有权利
// +----------------------------------------------------------------------
// | Author: YuanZhiMeng <214243830@qq.com>
// +---------------------------------------------------------------------- 
// | Explain: 这不是一个自由软件,您只能在不用于商业目的的前提下对程序代码进行修改和使用，不允许对程序代码以任何形式任何目的的再发布！
// +----------------------------------------------------------------------

defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class reply extends common{
	
	
    /**
     *  关键字回复列表
     */	
	public function init(){
		
		$wechat_auto_reply = D('wechat_auto_reply');
        $total = $wechat_auto_reply->where(array('type' => 1))->total();
		$page = new page($total, 15);
		$data = $wechat_auto_reply->where(array('type' => 1))->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('keyword_reply_list');
    }
	
	
	
    /**
     *  自动回复/关注回复
     */	
	public function reply_list(){
 		if(isset($_POST['dosubmit'])) {
			$type = intval($_POST['type']);
			setcache('wechat_reply_'.$type, htmlspecialchars($_POST['content']));
			showmsg(L('operation_success'), '', 1);
		}
		$type = isset($_GET['type']) ? intval($_GET['type']) : 2;
		if(!$data = getcache('wechat_reply_'.$type)){
			$data = '';
		}
		if($type == 2){
			$nav = '自动回复';
			$mess = '自动回复是未查询到关键字结果时返回的内容。';
		}else{
			$nav = '关注回复';
			$mess = '关注回复是用户关注您时，自动回复的内容。';
		}
		include $this->admin_tpl('reply_list');
    }


	
	/**
	 * 添加关键字回复
	 */	
	public function add(){ 
 		if(isset($_POST['dosubmit'])) {
			$_POST['type'] = 1;
			D('wechat_auto_reply')->insert($_POST, true);
			showmsg(L('operation_success'), U('init'), 1);
		}
		include $this->admin_tpl('keyword_reply_add');
	}

	

	
	/**
	 * 修改关键字回复
	 */	
	public function edit(){ 
 		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$_POST['type'] = 1;
			if(D('wechat_auto_reply')->update($_POST, array('id' => $id), true)){
				showmsg(L('operation_success'), U('init'), 1);
			}else{
				showmsg(L('operation_failure'));
			}
		}
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		$data = D('wechat_auto_reply')->where(array('id' => $id))->find();
		include $this->admin_tpl('keyword_reply_edit');
	}
	
	
	/**
	 * 删除关键字回复
	 */	
	public function del(){ 
		if($_POST && is_array($_POST['ids'])){
			$wechat_auto_reply = D('wechat_auto_reply');
			foreach($_POST['ids'] as $val){
				$wechat_auto_reply->delete(array('id'=>$val));
			}
			showmsg(L('operation_success'),'',1);
		}
	}
	
	
	/**
	 * 选择文章
	 */	
	public function select_article(){
		$wx_relation_model = get_config('wx_relation_model');
		if(!$wx_relation_model) showmsg('请选择微信关联模型！', 'stop');
		$model_db = get_model($wx_relation_model) ? D(get_model($wx_relation_model)) : showmsg('关联模型不存在！', 'stop');
		$where = array();
		if(isset($_GET['dosubmit'])){
			if($_GET['catid']) $where['catid'] = intval($_GET['catid']);
			if(isset($_GET['start']) && $_GET['start'] && $_GET['end']){		
				$where['updatetime>'] = strtotime($_GET['start'].' 00:00:00');
				$where['updatetime<'] = strtotime($_GET['end'].' 23:59:59');
			}
			if($_GET['searinfo']) $where['title'] = '%'.safe_replace($_GET['searinfo']).'%';
		}
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$total = $model_db->where($where)->total();
		$page = new page($total, 7);
		$data = $model_db->field('id, title, url, thumb, flag, catid, readpoint, inputtime, updatetime')->where($where)->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('select_article');
	}

}