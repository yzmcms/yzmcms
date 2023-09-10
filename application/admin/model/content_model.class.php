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

class content_model {
	public $modelarr;
	public $modelid;
	public $tabname;
	
	public function __construct() {
		$modelinfo = get_site_modelinfo();
		$this->modelarr = array();
		foreach($modelinfo as $val){
			$this->modelarr[$val['modelid']] = $val;
		}
		$this->modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : (isset($_POST['modelid']) ? intval($_POST['modelid']) : get_default_model('modelid'));
		if(!isset($this->modelarr[$this->modelid])) showmsg(L('model_does_not_exist'), U('sitemodel/init'));
		$this->tabname = $this->modelarr[$this->modelid]['tablename'];
	}


	/**
	 * 添加内容
	 * 
	 * @param $data
	 * @param $isimport 是否为外部接口
	 */	
 	public function content_add($data, $isimport = 0) {
		$notfilter_field = $this->get_notfilter_field();
		foreach($data as $_k=>$_v) {
			if(!in_array($_k, $notfilter_field)) {
				$data[$_k] = !is_array($data[$_k]) ? new_html_special_chars($_v) : $this->content_dispose($_v);
			}
		}

		$data['issystem']  = $isimport ? 0 : 1;
		$data['inputtime'] = isset($data['inputtime']) ? strtotime($data['inputtime']) : SYS_TIME;
		$data['updatetime'] = isset($data['updatetime']) ? strtotime($data['updatetime']) : SYS_TIME;
		$data['description'] = empty($data['description']) ? str_cut(strip_tags($data['content']),250) : $data['description'];
		$data['username'] = $_SESSION['adminname'];
		$data['userid'] = $_SESSION['adminid'];
		$data['catid'] = intval($data['catid']);
		$data['status'] = isset($data['status']) ? intval($data['status']) : 0;
		
		//远程图片本地化
		// if(isset($data['grab_img'])){
		// 	$data['content'] = down_remote_img($data['content']);
		// }
		
		//自动提取缩略图
		if(isset($data['auto_thum']) && $data['thumb'] == '') {
			$img = $data['content'] ? match_img($data['content']) : false;
			$data['thumb'] = $img ? thumb($img, get_config('thumb_width'), get_config('thumb_height')) : '';
			if(get_siteid() && $data['thumb'] && !strpos($data['thumb'], '://')) $data['thumb'] = get_config('site_url').ltrim($data['thumb'], '/');
		}

		$content_tabname = D($this->tabname);
		$id = $content_tabname->insert($data);
		
		//如果不是跳转URL，则更新URL
		if(empty($data['url'])){
			$url = get_content_url($data['catid'], $id);
			$content_tabname->update(array('url' => $url), array('id' => $id));			
		}
		
		//TAG标签处理
		if(!empty($data['tag'])){
			$this->tag_dispose($data['catid'], explode(',', $data['tag']), $id);
		}
		
		//写入全部模型表
		$data['siteid'] = get_siteid();
		$data['modelid'] = $this->modelid;
		$data['id'] = $id;
		$data['url'] = isset($url) ? $url : $data['url'];
		D('all_content')->insert($data);
		
		//记录catid
		set_cookie('catid', $data['catid']);
		set_cookie('auto_thum', isset($data['auto_thum']) ? 1 : 0);
		update_attachment($this->modelid, $id);
		
		return $id;

	} 


	/**
	 * 修改内容
	 * 
	 * @param $data
	 * @param $id
	 * @param $isimport 是否为外部接口
	 */	
 	public function content_edit($data, $id, $isimport = 0) {
		$content_tabname = D($this->tabname);
		$original_data = $content_tabname->field('`userid`,`username`,`issystem`,`status`')->where(array('id'=>$id))->find();
		if($isimport){
			$username = safe_replace(get_cookie('_username'));
			if(!$original_data || $original_data['username']!=$username || $original_data['issystem']==1) return false;
		}
		
		unset($data['issystem'], $data['username'], $data['userid']);
		
		$notfilter_field = $this->get_notfilter_field();
		foreach($data as $_k=>$_v) {
			if(!in_array($_k, $notfilter_field)) {
				$data[$_k] = !is_array($data[$_k]) ? new_html_special_chars($_v) : $this->content_dispose($_v);
			}
		}
		
        $data['status'] = isset($data['status']) ? intval($data['status']) : 0;
		$data['inputtime'] = isset($data['inputtime']) ? strtotime($data['inputtime']) : SYS_TIME;
		$data['updatetime'] = isset($data['updatetime']) ? strtotime($data['updatetime']) : SYS_TIME;
		$data['description'] = empty($data['description']) ? str_cut(strip_tags($data['content']),250) : $data['description'];
		$data['catid'] = intval($data['catid']);

		//如果没有设置属性标签，即清空
		if(!isset($data['flag'])) $data['flag'] = '';
		
		//远程图片本地化
		// if(isset($data['grab_img'])){
		// 	$data['content'] = down_remote_img($data['content']);
		// }
		
		//自动提取缩略图
		if(isset($data['auto_thum']) && $data['thumb'] == '') {
			$img = $data['content'] ? match_img($data['content']) : false;
			$data['thumb'] = $img ? thumb($img, get_config('thumb_width'), get_config('thumb_height')) : '';
			if(get_siteid() && $data['thumb'] && !strpos($data['thumb'], '://')) $data['thumb'] = get_config('site_url').ltrim($data['thumb'], '/');
		}
		
		//TAG标签处理
		if(!empty($data['tag'])){
			$this->tag_dispose($data['catid'], explode(',', $data['tag']), $id);
		}else{
			$this->tag_dispose($data['catid'], array(), $id);
		}
		
		//如果不是跳转URL，则更新URL
		if(strpos($data['flag'], '7') === false){
			$data['url'] = get_content_url($data['catid'], $id);
		}
		
		//修改全部模型表
		D('all_content')->update($data, array('modelid' => $this->modelid, 'id' => $id));
		
		$affected = $content_tabname->update($data, array('id' => $id));
		set_cookie('auto_thum', isset($data['auto_thum']) ? 1 : 0);
		update_attachment($this->modelid, $id);

		//如果是会员投稿，投稿奖励积分和经验
		$publish_point = get_config('publish_point');
		if($data['status']==1 && $original_data['status']!=1 && !$original_data['issystem'] && $publish_point > 0){
			D('member')->update('`point`=`point`+'.$publish_point.',`experience`=`experience`+'.$publish_point, array('userid' => $original_data['userid']));  
			D('pay')->insert(array('trade_sn'=>create_tradenum(), 'userid'=>$original_data['userid'], 'username'=>$original_data['username'], 'money'=>$publish_point, 'creat_time'=>SYS_TIME, 'msg'=>'投稿奖励','remarks'=>$data['catid'].'_'.$id, 'type'=>'1', 'status'=>'1', 'ip'=>getip()));		
		}
		return $affected;

	}  
	

	/**
	 * 删除内容
	 * @param $id 内容id
	 * @param $isimport 是否为外部接口
	 */
	public function content_delete($id, $isimport = 0) {

		$content_tabname = D($this->tabname);
		$res = $content_tabname->field('`catid`,`username`,`issystem`')->where(array('id'=>$id))->find();
		if(!$res) return false;
		if($isimport){
			$username = safe_replace(get_cookie('_username'));
			if($res['username']!=$username || $res['issystem']) return false;
		}
		$affected = $content_tabname->delete(array('id'=>$id)); //删除内容
		D('all_content')->delete(array('modelid' => $this->modelid, 'id'=>$id));  //删除所有模型内容表
		D('tag_content')->delete(array('modelid' => $this->modelid, 'aid'=>$id)); //删除TAG表	
		$commentid = $this->modelid.'_'.$res['catid'].'_'.$id;	
		D('comment')->delete(array('commentid' => $commentid));  //删除评论表
		D('comment_data')->delete(array('commentid' => $commentid));  //删除评论表
		delete_attachment($this->modelid, $id); //删除关联附件
		return $affected;
	}
	


	/**
	 * 内容处理
	 * @param $content 
	 */	
	private function content_dispose($content) {
		$is_array = false;
		foreach($content as $val){
			if(is_array($val)) $is_array = true;
			break;
		}
		if(!$is_array) return implode(',', $content);
		
		//这里认为是多文件上传
		$arr = array();
		foreach($content['url'] as $key => $val){
			$arr[$key]['url'] = $val;
			$arr[$key]['alt'] = $content['alt'][$key];
		}		
		return array2string($arr);
	}


	/**
	 * 获取模型非过滤字段
	 */	
	private function get_notfilter_field() {
		$arr = array('content');
		$data = D('model_field')->field('field,fieldtype')->where(array('modelid' => $this->modelid))->select();
		foreach($data as $val){
			if($val['fieldtype'] == 'editor' || $val['fieldtype'] == 'editor_mini') $arr[] = $val['field'];
		}

		return $arr;
	} 

	
	/**
	 * TAG标签处理
	 * @param $catid 
	 * @param $tags 
	 * @param $aid 
	 */	
	private function tag_dispose($catid, $tags, $aid) {
		$siteid = get_siteid();
		$tag = D('tag');
		$tag_content = D('tag_content');      
        $tag_content->delete(array('modelid' => $this->modelid, 'aid' => $aid));
        $tags = array_unique($tags); 
		foreach($tags as $val){
			if(!$val) continue;
			$tagid = $tag->field('id')->where(array('siteid'=>$siteid, 'tag'=>$val))->one();
			if($tagid){
				$total = $tag_content->where(array('siteid'=>$siteid,'tagid'=>$tagid))->total();
				$tag->update(array('total'=>$total+1), array('id'=>$tagid));
			}else{
				$tagid = $tag->insert(array('siteid'=>$siteid, 'catid'=>$catid, 'tag'=>$val, 'total'=>1, 'inputtime'=>SYS_TIME));
			}
			
			$tag_content->insert(array('siteid'=>$siteid, 'modelid'=>$this->modelid, 'catid'=>$catid, 'tagid'=>$tagid, 'aid'=>$aid), false, false);
		}
	}   	

}