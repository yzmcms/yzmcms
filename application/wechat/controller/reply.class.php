<?php
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
		$model_db = D($wx_relation_model);
		$where = array();
		if(isset($_GET['dosubmit']) && $_GET['searinfo']){
			$where['title'] = '%'.$_GET['searinfo'].'%';
		}
		$total = $model_db->where($where)->total();
		$page = new page($total, 7);
		$data = $model_db->field('id, title, url, thumb, flag, catid, readpoint, updatetime')->where($where)->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('select_article');
	}

}