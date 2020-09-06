<?php
defined('IN_YZMPHP') or exit('Access Denied'); 
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('page','',0);

class tag extends common {

	/**
	 * TAG列表
	 */
	public function init() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','catid','total','inputtime')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$tag = D('tag');
		$where = array();
		$catid = 0;
		if(isset($_GET['dosubmit'])){
			$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
			$type = isset($_GET['type']) ? intval($_GET['type']) : 1;
			$searinfo = isset($_GET['searinfo']) ? safe_replace(trim($_GET['searinfo'])) : '';
			if($searinfo != ''){
				if($type == 1)
					$where = array('tag' => '%'.$searinfo.'%');
				else
					$where = array('remarks' => '%'.$searinfo.'%');
			}
			if($catid)	$where['catid'] = $catid;		
		}		
		$_GET = array_map('htmlspecialchars', $_GET);
		$total = $tag->where($where)->total();
		$page = new page($total, 15);
		$data = $tag->where($where)->order("$of $or")->limit($page->limit())->select();		
		include $this->admin_tpl('tag_list');
	}


	/**
	 * 添加TAG
	 */
	public function add() {
 		if(isset($_POST['dosubmit'])) {
			$tag = D('tag');
			$catid = isset($_POST['catid']) ? intval($_POST['catid']) : 0;
			$_POST['tags'] = str_replace('，', ',', strip_tags($_POST['tags']));
			$arr = array_unique(explode(',', $_POST['tags']));
			foreach($arr as $val){
				$val = trim($val);
				$tagid = $tag->where(array('tag' => $val))->find();
				if(!$tagid){
					$tag->insert(array('catid'=>$catid, 'tag'=>$val, 'total'=>0, 'inputtime'=>SYS_TIME), true);
				}
			}
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}else{
			include $this->admin_tpl('tag_add');
		}
	}
	
	
	/**
	 * 编辑TAG
	 */
 	public function edit() {
		$tag = D('tag');
		if(isset($_POST['dosubmit'])) {
			$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
			$catid = isset($_POST['catid']) ? intval($_POST['catid']) : 0;
			// $total = isset($_POST['total']) ? intval($_POST['total']) : 0;
			$tagv = trim($_POST['tag']);
			$remarks = trim($_POST['remarks']);
			$data = $tag->where(array('id!='=>$id, 'tag'=>$tagv))->find();
			if($data) return_json(array('status'=>0,'message'=>'TAG标签重复，请修改名称！'));
			if($tag->update(array('catid'=>$catid, 'tag'=>$tagv, 'remarks'=>$remarks), array('id'=>$id), true)){
				return_json(array('status'=>1,'message'=>L('operation_success')));
			}else{
				return_json();
			}
			
		}else{
			$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
			$data = $tag->where(array('id' => $id))->find();
			include $this->admin_tpl('tag_edit');
		}

	}

	
	/**
	 * 删除多个TAG
	 */
	public function del() {
		if($_POST && is_array($_POST['id'])){
			$tag = D('tag'); 
			$tag_content = D('tag_content'); 
			foreach($_POST['id'] as $id){
				$tag->delete(array('id' => $id)); 
				$tag_content->delete(array('tagid'=>$id));
			}
		}
		showmsg(L('operation_success'),'',1);
	}


	/**
	 * 管理内容
	 */
	public function content() {
		$id = input('get.id', 0, 'intval');
		$modelinfo = get_modelinfo();
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 1;
		$tabname = get_model($modelid);
        $catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
        $content = D($tabname);
        $where = '1=1';
        if (isset($_GET['dosubmit'])) {
            $searinfo = isset($_GET["searinfo"]) ? safe_replace($_GET["searinfo"]) : '';
            $type = isset($_GET["type"]) ? $_GET["type"] : 1;

            if ($searinfo != '') {
                if ($type == '1') {
                    $where .= ' AND `title` LIKE \'%'.$searinfo.'%\'';
                } elseif($type == '2') {
                    $where .= ' AND `keywords` LIKE \'%'.$searinfo.'%\'';
                } elseif($type == '3') {
                    $where .= ' AND `description` LIKE \'%'.$searinfo.'%\'';
                }else {
                    $where .= ' AND id = '.intval($searinfo);
                }
            }

            if ($catid) {
                $where .= ' AND catid='.$catid;
            }
        }
        $_GET = array_map('htmlspecialchars', $_GET);
        $total = $content->where($where)->total();
        $page = new page($total, 8);
        $data = $content->where($where)->order('id DESC')->limit($page->limit())->select();
		include $this->admin_tpl('tag_content');
		
	}


	/**
	 * TAG内容操作
	 */
	public function content_oper(){
		if(is_post()){
			if(!isset($_POST['ids'])) return_json(array('status'=>0,'message'=>'请勾选内容！'));
			$modelid = input('post.modelid', 0, 'intval');
			$tagid = input('post.tagid', 0, 'intval');
			$operation = input('post.operation', 1, 'intval');
			$id = D('tag')->field('id')->where(array('id'=>$tagid))->one();
			if(!$id) return_json(array('status'=>0,'message'=>L('operation_failure')));

			$tag_content = D('tag_content');
			foreach ($_POST['ids'] as $id => $catid) {
				$is_exist = $tag_content->field('tagid')->where(array('modelid'=>$modelid,'aid'=>$id,'tagid'=>$tagid))->one();
				if($operation){
					if($is_exist) continue;
					$tag_content->insert(array('modelid'=>$modelid,'catid'=>$catid,'aid'=>$id,'tagid'=>$tagid), false, false);
				}else{
					if(!$is_exist) continue;
					$tag_content->delete(array('modelid'=>$modelid,'aid'=>$id,'tagid'=>$tagid));
				}
			}

			$total = $tag_content->where(array('tagid' => $tagid))->total();
			D('tag')->update(array('total' => $total), array('id' => $tagid));
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}
	}



	/**
	 * TAG标签选择
	 */
	public function public_select() {
		$catid = isset($_POST['catid']) ? intval($_POST['catid']) : 0;
		$searinfo = isset($_POST['searinfo']) ? htmlspecialchars($_POST['searinfo']) : '';
		$where = array();
		if(isset($_POST['dosubmit'])){
			if($catid)  $where['catid'] = $catid;
			if($searinfo)  $where['tag'] = '%'.$searinfo.'%';
			$data = D('tag')->field('id,catid,tag,total')->where($where)->order('id DESC')->limit('100')->select();
			return_json(array('status'=>1,'message'=>L('operation_success'),'data'=>$data));
		}
		$data = D('tag')->field('id,catid,tag,total')->where($where)->order('id DESC')->limit('100')->select();
		include $this->admin_tpl('tag_select');
	}
	
}