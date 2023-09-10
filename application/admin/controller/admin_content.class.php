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

class admin_content extends common {

	/**
	 * 投稿管理
	 */
	public function init() {
		$of = input('get.of');
		$or = input('get.or');
		$of = in_array($of, array('id','catid','username','updatetime','status','userid')) ? $of : 'updatetime';
		$or = in_array($or, array('ASC','DESC')) ? $or : 'DESC';
		yzm_base::load_sys_class('page','',0);

		$modelinfo = get_site_modelinfo();
		$modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : 0;
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$where = 'siteid='.self::$siteid.' AND issystem=0';
		if(isset($_GET['dosubmit'])){	
		
			$searinfo = isset($_GET['searinfo']) ? safe_replace($_GET['searinfo']) : '';
			$type = isset($_GET["type"]) ? $_GET["type"] : 1;

			if(isset($_GET["status"]) && $_GET["status"] != '99'){
				$where .= ' AND status = '.intval($_GET["status"]);
			}	

			if($modelid){
				$where .= ' AND modelid='.$modelid;
			}

			if($catid){
				$where .= ' AND catid='.$catid;
			}

			if(isset($_GET["start"]) && $_GET["start"] != '' && $_GET["end"]){		
				$where .= ' AND updatetime BETWEEN '.strtotime($_GET["start"]).' AND '.strtotime($_GET["end"]);
			}

			if($searinfo){
				if($type == '1')
					$where .= ' AND title LIKE \'%'.$searinfo.'%\'';
				elseif($type == '2')
					$where .= ' AND allid = '.intval($searinfo);
				else
					$where .= ' AND username LIKE \'%'.$searinfo.'%\'';
			}
			
		}

		$all_content = D('all_content');
		$total = $all_content->where($where)->total();
		$page = new page($total, 15);
		$data = $all_content->where($where)->order("$of $or")->limit($page->limit())->select();		
		include $this->admin_tpl('member_publish_list');
	}


	/**
	 * 稿件浏览
	 */
	public function public_preview() {
		yzm_base::load_model('content', 'index', 0);
		$catid = isset($_GET['catid']) ? intval($_GET['catid']) : 0;
		$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		if(!$catid || !$id) showmsg(L('lose_parameters'),'stop');
		
		$category = get_category($catid);
		if(!$category) showmsg('栏目不存在！','stop');
		$siteid = $category['siteid'];
		$modelid = $category['modelid'];
		$template = $category['show_template'];

		if($siteid){
			set_module_theme(get_site($siteid, 'site_theme'));
		}
		
		$tablename = get_model($modelid);
		if(!$tablename)  showmsg(L('model_not_existent'),'stop');
		$db = D($tablename);
		$data = $db->where(array('id'=>$id))->find();
		extract($data);

		//跳转链接检测
		$flag==7 && redirect($url);
		
		//阅读收费检测
		$allow_read = true;
		if($readpoint){
			$allow_read = content::check_readpoint($data);
			$par[] = $catid.'_'.$id;
			$par[] = $readpoint;
			$par[] = $paytype;
			$par[] = $issystem ? 0 : $userid;
			$pay_url = U('member/member_pay/spend_point', 'par='.string_auth(join('|',$par))).'?referer='.urlencode($url);
		} 

		//内容分页
		$page_section = '';
		if(strpos($content, '_yzm_content_page_') !== false){
			$content = content::content_page($content, (isset($_GET['page']) ? intval($_GET['page']) : 0), $page_section);
		}	
		
		//内容关键字
		if(get_config('keyword_link')){
			$content = content::keyword_content($content);
		}		
		
		//获取相同分类的上一篇/下一篇内容	
		$pre = $db->field('title,url')->where(array('id<'=>$id , 'status'=>'1' , 'catid'=>$catid))->order('id DESC')->find();
		$next = $db->field('title,url')->where(array('id>'=>$id , 'status'=>'1', 'catid'=>$catid))->order('id ASC')->find();
		$pre = $pre ? '<a href="'.$pre['url'].'">'.$pre['title'].'</a>' : '已经是第一篇';
		$next = $next ? '<a href="'.$next['url'].'">'.$next['title'].'</a>' : '已经是最后一篇';

		//SEO相关设置
		$site = get_config();
		$seo_title = $title.$page_section.get_seo_suffix();
		
		include template('index', $template);
	}
	
	
	/**
	 * 稿件删除
	 */
	public function del() {
		if($_POST && is_array($_POST['ids'])){	
			$all_content = D('all_content');
			yzm_base::load_model('content_model', 'admin', 0);
			foreach($_POST['ids'] as $val){
				$res = $all_content->field('modelid,id')->where(array('allid' => $val))->find(); 
				if(!$res) continue;
				$_POST['modelid'] = $res['modelid'];
				$content_model = new content_model();
				$content_model->content_delete($res['id']);
			}
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}
	}
	
	
	/**
	 * 通过审核
	 */
	public function adopt() {
		if($_POST && is_array($_POST['ids'])){	
			$all_content = D('all_content');
			$member = D('member');
			$pay = D('pay');
			$ip = getip();
			$publish_point = get_config('publish_point');
			
			foreach($_POST['ids'] as $val){
				$data = $all_content->field('modelid,catid,id,userid,username,status')->where(array('allid' => $val))->find();
				if($data['status'] == 1) continue;

				$modelid = $data['modelid'];
				$catid = $data['catid'];
				$id = $data['id'];
				
				$updatearr['status'] = '1';
				$updatearr['url'] = get_content_url($catid, $id);
				$content_tabname = D(get_model($modelid));
				$content_tabname->update($updatearr, array('id' => $id));
				$all_content->update($updatearr, array('allid' => $val));
				
				//投稿奖励积分和经验
				if($publish_point > 0){
					$member->update('`point`=`point`+'.$publish_point.',`experience`=`experience`+'.$publish_point, array('userid' => $data['userid']));  
					$pay->insert(array('trade_sn'=>create_tradenum(), 'userid'=>$data['userid'], 'username'=>$data['username'], 'money'=>$publish_point, 'creat_time'=>SYS_TIME, 'msg'=>'投稿奖励','remarks'=>$catid.'_'.$id, 'type'=>'1', 'status'=>'1', 'ip'=>$ip));		
				}
			}
			return_json(array('status'=>1,'message'=>L('operation_success')));
		}
	}
	
	
	/**
	 * 退稿
	 */
	public function rejection() {
		if($_POST && is_array($_POST['ids'])){
			
			$data = array();
			$data['send_from'] = '系统';
			$data['issystem'] = '1';
			$data['message_time'] = SYS_TIME;
			$data['subject'] = '您的稿件被退回，请修改后重新提交';
			$data['content_c'] = isset($_POST['content_c']) ? htmlspecialchars($_POST['content_c']) : '';

			$message = D('message');
			$all_content = D('all_content');
			foreach($_POST['ids'] as $val){
				$r = $all_content->field('modelid,catid,id,title,username')->where(array('allid' => $val))->find();
				if(!$r) return_json(array('status'=>0,'message'=>'该内容不存在，请检查！'));
				$tablename = get_model($r['modelid']);
				if($tablename) D($tablename)->update(array('status' => '2'), array('id' => $r['id'])); 
				$all_content->update(array('status' => '2'), array('allid' => $val)); 
				$data['send_to'] = $r['username'];  //收件人
				$data['content'] = '您提交的稿件不满足我们的要求，请重新编辑稿件！<br>具体原因：'.$data['content_c'].'<br><br>原文标题：'.$r['title'].'<br><a href="'.U('member/member_content/edit_through',array('catid'=>$r['catid'], 'id'=>$r['id']), false).'" style="color:#2438cf">点击这里修改</a>';
				$message->insert($data);
			}
			return_json(array('status'=>1,'message'=>L('operation_success')));			
		}
	}
	
}