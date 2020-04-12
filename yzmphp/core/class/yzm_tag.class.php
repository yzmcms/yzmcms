<?php
/**
 * yzm_tag.class.php  yzmcms标签类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-01-11 
 */
 
class yzm_tag{
	
	public $tablename,$page,$total,$db;
	
	/**
	 * 内容列表标签
	 * @param $data
	 */
	public function lists($data) {
		$modelid = isset($data['modelid']) ? intval($data['modelid']) : 0;
		$catid = isset($data['catid']) ? trim($data['catid']) : '';
		
		if($catid){
			if(!strpos($catid, ',')){
				$category = get_category($catid);
				if(!$category) return false;
				$arrchildid = $category['arrchildid'];
				$catid = strpos($arrchildid, ',') ? ' AND catid IN ('.$arrchildid.')' : ' AND catid='.$arrchildid;
			}else{
				$catarr = explode(',', rtrim($catid, ','));
				$catid = $catarr[0]; 
				$category = get_category($catid);
				if(!$category) return false;
				$catid = ' AND catid IN ('.join(',', $catarr).')';
			}
		}

		// 优先采用传入的modelid
		$modelid = $modelid ? $modelid : (isset($category['modelid']) ? $category['modelid'] : 0);
		
		if(!$this->_set_model($modelid)) return false;
		$field = isset($data['field']) ? $data['field'] : '*';
		$order = isset($data['order']) ? $data['order'] : 'listorder ASC,id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		
		if(isset($data['where']) && $data['where']){
			$where = 'status=1'.$catid.' AND '.$data['where'];
		}else{
			$thumb = isset($data['thumb']) ? " AND thumb != ''" : '';
			$flag = isset($data['flag']) ? " AND FIND_IN_SET('".intval($data['flag'])."',flag)" : '';
			$where = 'status=1'.$catid.$thumb.$flag;
		}
		
		if(isset($data['page'])){
			yzm_base::load_sys_class('page','',0);
			$this->total = $this->db->where($where)->total();
			$this->page = new page($this->total, $limit);
			$limit = $this->page->limit();
		}
		return $this->db->field($field)->where($where)->order($order)->limit($limit)->select();
	}
	

	
	/**
	 * 分页显示
	 * @param $string
	 */
	public function pages() {
		if(!is_object($this->page)) return '';
		//当前页：$this->page->getpage();
		return '<span class="pageinfo">共<strong>'.$this->page->total().'</strong>页<strong>'.$this->total.'</strong>条记录</span>'.$this->page->getfull();
	}
	
	
	
	/**
	 * 点击排行榜标签
	 * @param $data
	 */
	public function hits($data) {
		$modelid = isset($data['modelid']) ? intval($data['modelid']) : 0;
		$catid = isset($data['catid']) ? intval($data['catid']) : '';
		
		if($catid){
			$category = get_category($catid);
			if(!$category) return false;
			$arrchildid = $category['arrchildid'];
			$catid = strpos($arrchildid, ',') ? ' AND catid IN ('.$arrchildid.')' : ' AND catid='.$arrchildid;		
		}	

		// 优先采用传入的modelid
		$modelid = $modelid ? $modelid : (isset($category['modelid']) ? $category['modelid'] : 0);

		if(!$this->_set_model($modelid)) return false;
		$field = isset($data['field']) ? $data['field'] : '*';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		
		if(isset($data['where']) && $data['where']){
			$where = 'status=1'.$catid.' AND '.$data['where'];
		}else{
			$thumb = isset($data['thumb']) ? " AND thumb != ''" : '';
			$where = 'status=1'.$catid.$thumb;
		}
		
		return $this->db->field($field)->where($where)->order('`click` DESC')->limit($limit)->select();
	}

	
	
	/**
	 * 栏目导航标签
	 * @param $data
	 */
	public function nav($data) {
		$field = isset($data['field']) ? $data['field'] : '*';
		$order = isset($data['order']) ? $data['order'] : 'listorder ASC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		$where = isset($data['where']) ? $data['where'].' AND ' : '';
		$where .= '`display`=1';
		return D('category')->field($field)->where($where)->order($order)->limit($limit)->select();
	}	
	
	

	/**
	 * 友情链接标签
	 * @param $data
	 */
	public function link($data) {
		$field = isset($data['field']) ? $data['field'] : '*';
		$where = 'status = 1';
		$order = isset($data['order']) ? $data['order'] : 'listorder ASC, id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		if(isset($data['where'])){
			$where = $data['where'];
		}else{
			$where .= isset($data['thumb']) ? " AND logo != ''" : '';
		}
		return D('link')->field($field)->where($where)->order($order)->limit($limit)->select();
	}	
	
	
	
	/**
	 * TAG标签(所有tag列表)
	 * @param $data
	 */
	public function tag($data) {
		$order = isset($data['order']) ? $data['order'] : 'id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		$where = isset($data['where']) ? $data['where'] : '';
		$field = isset($data['field']) ? $data['field'] : 'id,tag,total,remarks';
		return D('tag')->field($field)->where($where)->order($order)->limit($limit)->select();
	}	
	
	
	
	/**
	 * 内容页TAG标签
	 * @param $data
	 */
	public function centent_tag($data) {
		$modelid = isset($data['modelid']) ? intval($data['modelid']) : 1;
		$id = isset($data['id']) ? intval($data['id']) : 0;
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		return  D('tag_content')->field('id,tag')->join('yzmcms_tag b ON yzmcms_tag_content.tagid=b.id')->order('id DESC')->where('yzmcms_tag_content.modelid='.$modelid.' AND yzmcms_tag_content.aid='.$id)->limit($limit)->order('id ASC')->select();
	}	
	
	
	
	/**
	 * 相关内容标签(根据tag标签获取)
	 * @param $data
	 */
	public function relation($data) {
		$modelid = isset($data['modelid']) ? intval($data['modelid']) : 1;
		$id = isset($data['id']) ? intval($data['id']) : 0;
		$field = isset($data['field']) ? $data['field'] : '*';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		
		if(!$this->_set_model($modelid)) return false;
		
		$tag_content = D('tag_content');
		$res = $tag_content->field('tagid')->where(array('modelid'=>$modelid, 'aid'=>$id))->limit(10)->select();
		if(empty($res))  return false;
		
		$tagid = array();
		foreach($res as $val){
			$tagid[] = $val['tagid'];
		}
		
		$res = $tag_content->field('aid')->where('modelid='.$modelid.' AND tagid IN ('.implode(',', $tagid).')')->group('aid')->limit($limit+1)->order('aid DESC')->select();
		$ids = array();
		foreach($res as $val){
			if($val['aid'] == $id) continue;	//去除当前的内容ID
			$ids[] = $val['aid'];
		}
		if(empty($ids)) return false;
		return $this->db->field($field)->where('status=1 AND id IN ('.implode(',', $ids).')')->order('id DESC')->limit($limit)->select();
	}	
	


	/**
	 * 留言板标签
	 * @param $data
	 */
	public function guestbook($data) {
		$field = isset($data['field']) ? $data['field'] : '*';
		$order = isset($data['order']) ? $data['order'] : 'id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		$where = isset($data['where']) ? $data['where'] : '`ischeck` = 1';
		$guestbook = D('guestbook');
		if(isset($data['page'])){
			yzm_base::load_sys_class('page','',0);
			$this->total = $guestbook->where($where)->total();
			$this->page = new page($this->total, $limit);
			$limit = $this->page->limit();
		}
		$data = $guestbook->field($field)->where($where)->order($order)->limit($limit)->select();
		foreach($data as $key => $val){
			//管理员回复
			$data[$key]['admin_reply'] = $guestbook->field('booktime,bookmsg')->where(array('replyid'=>$val['id']))->order('id ASC')->select(); 
		}
		return $data;
	}
	
	
	
	/**
	 * 轮播图标签
	 * @param $data
	 */
	public function banner($data) {
		$field = isset($data['field']) ? $data['field'] : '*';
		$order = 'listorder ASC,id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		$typeid = isset($data['typeid']) ? intval($data['typeid']) : 0;
		$where = $typeid ? '`status` = 1 AND typeid='.$typeid : '`status` = 1';
		return D('banner')->field($field)->where($where)->order($order)->limit($limit)->select();
	}
	
	
	
	/**
	 * 评论列表标签
	 * @param $data
	 */
	public function comment_list($data) {
		$field = isset($data['field']) ? $data['field'] : 'id,userid,username,userpic,inputtime,content';
		$order = isset($data['order']) ? $data['order'] : 'id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		$modelid = isset($data['modelid']) ? intval($data['modelid']) : 1; 
		$catid = isset($data['catid']) ? intval($data['catid']) : 0; 
		$id = isset($data['id']) ? intval($data['id']) : 0; 
		$where = '`commentid` = "'.$modelid.'_'.$catid.'_'.$id.'" AND `status` = 1';
		return D('comment')->field($field)->where($where)->order($order)->limit($limit)->select();
	}

	

	/**
	 * 评论排行榜标签
	 * @param $data
	 */
	public function comment_ranking($data) {
		$field = isset($data['field']) ? $data['field'] : 'title,url,total,catid';
		$where = isset($data['modelid']) ? 'modelid='.intval($data['modelid']) : ''; 
		$order = '`total` DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		return D('comment_data')->field($field)->where($where)->order($order)->limit($limit)->select();
	}
	
	
	
	/**
	 * 最新评论标签
	 * @param $data
	 */
	public function comment_newest($data) {
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		return D('comment')->field('userid,username,userpic,inputtime,content,title,url,catid,modelid')->join('yzmcms_comment_data b ON yzmcms_comment.commentid=b.commentid')->where('`status`=1')->order('id DESC')->limit($limit)->select();
	}

	
	
	/**
	 * 内容归档标签
	 * @param $data
	 */
	public function content_archives($data) {
		$modelid = isset($data['modelid']) ? intval($data['modelid']) : 1;
		$type = isset($data['type']) ? intval($data['type']) : 1;
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		
		if(!$this->_set_model($modelid)) return false;
		$format = $type == 1 ? '%Y-%m' : '%Y年%m月';
		
		return $this->db->field("FROM_UNIXTIME(inputtime, '$format') AS pubtime, count(*) AS total,inputtime")->where('`status`=1')->group("FROM_UNIXTIME(inputtime, '$format')")->order('pubtime DESC')->limit($limit)->select();
	}

	
	
	/**
	 * 自定义SQL标签
	 * @param $data
	 */
	public function get($data) {
		if(!isset($data['sql'])) return false;
		$sql = $data['sql'];
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		$db = D('admin');
		if(isset($data['page'])){
			yzm_base::load_sys_class('page','',0);
			$countsql = 'SELECT COUNT(*) as total FROM ('.$sql.') T';
			$r = $db->fetch_array($db->query($countsql));
			$this->total = $r['total'];
			$this->page = new page($this->total, $limit);
			$limit = $this->page->limit();
		}
		$sql = $sql.' LIMIT '.$limit;
		return $db->fetch_all($db->query($sql));
	}	
	
	
	
	/**
	 * 设置模型
	 * @param $modelid
	 */
	private function _set_model($modelid) {
		$model = get_model($modelid);
		if(!$model)  return false;
		$this->tablename = $model;
		$this->db = D($model);
		return true;
	}
	
}