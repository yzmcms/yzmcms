<?php
class content_model {
	public $modelarr;
	public $modelid;
	public $tabname;
	
	public function __construct() {
		$modelinfo = get_modelinfo();
		$this->modelarr = array();
		foreach($modelinfo as $val){
			$this->modelarr[$val['modelid']] = $val;
		}
		$this->modelid = isset($_GET['modelid']) ? intval($_GET['modelid']) : (isset($_POST['modelid']) ? intval($_POST['modelid']) : 1);
		if(!isset($this->modelarr[$this->modelid])) showmsg('模型不存在！');
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

		$data['system']  = $isimport ? 0 : 1;
		$data['inputtime'] = strtotime($data['inputtime']);
		$data['updatetime'] = SYS_TIME;
		$data['description'] = empty($data['description']) ? str_cut(strip_tags($data['content']),200) : $data['description'];
		$data['username'] = $_SESSION['adminname'];
		$data['userid'] = $_SESSION['adminid'];
		$data['catid'] = intval($data['catid']);
		$data['status'] = isset($data['status']) ? intval($data['status']) : 0;
		
		//远程图片本地化
		if(isset($data['grab_img'])){
			$data['content'] = grab_image($data['content']);
		}
		
		//自动提取缩略图
		if(isset($data['auto_thum']) && $data['thumb'] == '') {
			$img = match_img($data['content']);
			$data['thumb'] = $img ? thumb($img, get_config('thumb_width'), get_config('thumb_height')) : '';
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
		
		//记录catid
		set_cookie('catid', $data['catid']);
		
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
		$r = $content_tabname->field('`username`,`system`')->where(array('id'=>$id))->find();
		if($isimport){
			$username = safe_replace(get_cookie('_username'));
			if(!$r || $r['username']!=$username || $r['system']==1) return false;
		}
		
		unset($data['system'], $_POST['username'], $_POST['userid']);
		
		$notfilter_field = $this->get_notfilter_field();
		foreach($data as $_k=>$_v) {
			if(!in_array($_k, $notfilter_field)) {
				$data[$_k] = !is_array($data[$_k]) ? new_html_special_chars($_v) : $this->content_dispose($_v);
			}
		}
		
        $data['status'] = isset($data['status']) ? intval($data['status']) : 0;
		$data['updatetime'] = SYS_TIME;
		$data['inputtime'] = strtotime($data['inputtime']);
		$data['description'] = empty($data['description']) ? str_cut(strip_tags($data['content']),200) : $data['description'];
		$data['catid'] = intval($data['catid']);

		//如果没有设置属性标签，即清空
		if(!isset($data['flag'])) $data['flag'] = '';
		
		//远程图片本地化
		if(isset($data['grab_img'])){
			$data['content'] = grab_image($data['content']);
		}
		
		//自动提取缩略图
		if(isset($data['auto_thum']) && $data['thumb'] == '') {
			$img = match_img($data['content']);
			$data['thumb'] = $img ? thumb($img, get_config('thumb_width'), get_config('thumb_height')) : '';
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
		
		//如果是会员发布的内容，则修改会员内容表的title
		if(!$r['system']){
			$where = array('title' => $data['title']);
			$where['status'] = $data['status'] ? 1 : 0;
			D('member_content')->update($where, array('checkid' => $this->modelid.'_'.$id));
		}
		
		$affected = $content_tabname->update($data, array('id' => $id));
		return $affected;

	}  
	

	/**
	 * 删除内容
	 * @param $id 内容id
	 * @param $isimport 是否为外部接口
	 */
	public function content_delete($id, $isimport = 0) {

		$content_tabname = D($this->tabname);
		if($isimport){
			$username = safe_replace(get_cookie('_username'));
			$r = $content_tabname->field('`username`,`system`')->where(array('id'=>$id))->find();
			if(!$r || $r['username']!=$username || $r['system']==1) return false;
		}
		$affected = $content_tabname->delete(array('id'=>$id));
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
		$tag = D('tag');
		$tag_content = D('tag_content');      
        $tag_content->delete(array('modelid' => $this->modelid, 'aid' => $aid));
        $tags = array_unique($tags); 
		foreach($tags as $val){
			if(!$val) continue;
			$tagid = $tag->field('id')->where(array('tag' => $val))->one();
			if($tagid){
				$total = $tag_content->where(array('tagid' => $tagid))->total();
				$tag->update(array('total' => $total+1), array('id' => $tagid));
			}else{
				$tagid = $tag->insert(array('tag'=>$val, 'total'=>1, 'inputtime'=>SYS_TIME));
			}
			
			$tag_content->insert(array('modelid' => $this->modelid, 'catid' => $catid, 'tagid' => $tagid, 'aid' => $aid), false, false);
		}
	}   	

}