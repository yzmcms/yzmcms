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

class tag extends common {

	/**
	 * TAG列表
	 */
	public function init() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','catid','total','click','inputtime')) ? $of : 'id';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		$tag = D('tag');
		$where = array('siteid'=>self::$siteid);
		$catid = 0;
		$site_url = self::$siteid ? rtrim(get_site_url(), '/') : '';
		if(isset($_GET['dosubmit'])){
			$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
			$type = isset($_GET['type']) ? intval($_GET['type']) : 1;
			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';

			if($catid)	$where['catid'] = $catid;		
			if(isset($_GET['start']) && isset($_GET['end']) && $_GET['start']) {
				$where['inputtime>='] = strtotime($_GET['start']);
				$where['inputtime<='] = strtotime($_GET['end']);
			}
			if($searinfo){
				if($type == 1){
					$where['tag'] = '%'.$searinfo.'%';
				}elseif($type == 2){
					$where['id'] = intval($searinfo);
				}elseif($type == 3){
					$where['seo_title'] = '%'.$searinfo.'%';
				}elseif($type == 4){
					$where['seo_keywords'] = '%'.$searinfo.'%';
				}else{
					$where['seo_description'] = '%'.$searinfo.'%';
				}
			}
		}		

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
				$tagid = $tag->field('id')->where(array('siteid'=>self::$siteid, 'tag'=>$val))->one();
				if(!$tagid){
					$tag->insert(array('siteid'=>self::$siteid, 'catid'=>$catid, 'tag'=>$val, 'total'=>0, 'inputtime'=>SYS_TIME), true);
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
			$seo_title = trim($_POST['seo_title']);
			$seo_keywords = trim($_POST['seo_keywords']);
			$seo_description = trim($_POST['seo_description']);
			$tagid = $tag->field('id')->where(array('siteid'=>self::$siteid, 'tag'=>$tagv))->one();
			if($tagid && $tagid!=$id) return_json(array('status'=>0,'message'=>'TAG标签重复，请修改名称！'));
			if($tag->update(array(
				'catid'=>$catid,
				'tag'=>$tagv,
				'seo_title'=>$seo_title,
				'seo_keywords'=>$seo_keywords,
				'seo_description'=>$seo_description
				), array('id'=>$id), true)
			){
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
		return_json(array('status'=>1,'message'=>L('operation_success')));
	}


	/**
	 * 关联内容
	 */
	public function content() {
		$id = input('get.id', 0, 'intval');
		$modelinfo = get_site_modelinfo();
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : get_default_model('modelid');
		$tabname = $modelid ? get_model($modelid) : showmsg('模型为空或不存在！', 'stop');
        $catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
        $content = D($tabname);
        $where = '1=1';
        if (isset($_GET['dosubmit'])) {
            $searinfo = isset($_GET["searinfo"]) ? safe_replace($_GET["searinfo"]) : '';
            $type = isset($_GET["type"]) ? $_GET["type"] : 1;

            if ($catid) {
                $where .= ' AND catid='.$catid;
            }

            if(isset($_GET['start']) && $_GET['start'] && $_GET['end']){		
            	$where .= ' AND updatetime BETWEEN '.strtotime($_GET['start'].' 00:00:00').' AND '.strtotime($_GET['end'].' 23:59:59');
            }

            if($searinfo){
                if ($type == '1') {
                    $where .= ' AND `title` LIKE \'%'.$searinfo.'%\'';
                } elseif($type == '2') {
                    $where .= ' AND `username` LIKE \'%'.$searinfo.'%\'';
                } elseif($type == '3') {
                    $where .= ' AND `keywords` LIKE \'%'.$searinfo.'%\'';
                } elseif($type == '4') {
                    $where .= ' AND `description` LIKE \'%'.$searinfo.'%\'';
                }else {
                    $where .= ' AND id = '.intval($searinfo);
                }
            }

        }
        $_GET = array_map('htmlspecialchars', $_GET);
        $total = $content->where($where)->total();
        $page = new page($total, 0);
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
					$tag_content->insert(array('siteid'=>self::$siteid,'modelid'=>$modelid,'catid'=>$catid,'aid'=>$id,'tagid'=>$tagid), false, false);
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
		$where = array('siteid'=>self::$siteid);
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