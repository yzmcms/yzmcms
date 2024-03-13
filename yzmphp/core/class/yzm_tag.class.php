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
		$id = isset($data['id']) ? intval($data['id']) : 0;
		$all = isset($data['all']) ? true : false;
		
		if($catid){
			if(!strpos($catid, ',')){
				$category = get_category($catid, '', $all);
				if(!$category) return false;
				$arrchildid = $category['arrchildid'];
				$catid = strpos($arrchildid, ',') ? ' AND catid IN ('.$arrchildid.')' : ' AND catid='.$arrchildid;
			}else{
				$catarr = explode(',', rtrim($catid, ','));
				$catid = $catarr[0]; 
				$category = get_category($catid, '', $all);
				if(!$category) return false;
				$catid = ' AND catid IN ('.join(',', $catarr).')';
			}
		}

		// 优先采用传入的modelid
		$modelid = $modelid ? $modelid : (isset($category['modelid']) ? $category['modelid'] : 0);

		// 支持单个ID查询
		if($id) $catid = ' AND id='.$id;
		
		if(!$this->_set_model($modelid)) return false;
		$field = isset($data['field']) ? $data['field'] : '*';
		$order = isset($data['order']) ? $data['order'] : 'listorder ASC,id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		
		if(isset($data['where']) && $data['where']){
			$where = 'status=1'.$catid.' AND '.$data['where'];
		}else{
			$thumb = isset($data['thumb']) ? ($data['thumb'] ? " AND thumb <> ''" : " AND thumb = ''") : '';
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
		return '<span class="pageinfo">共<strong>'.$this->total.'</strong>条记录</span>'.$this->page->getfull(false);
	}



	/**
	 * 全站最近更新
	 * @param $data
	 */
	public function all($data) {

		$where = isset($data['allsite']) ? array('status'=>1) : array('siteid'=>get_siteid(), 'status'=>1);
		$field = isset($data['field']) ? $data['field'] : 'modelid,catid,id,userid,username,title,inputtime,updatetime,url,thumb,issystem';
		$order = isset($data['order']) ? $data['order'] : 'allid DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		
		return D('all_content')->field($field)->where($where)->order($order)->limit($limit)->select();
	}
	
	
	
	/**
	 * 点击排行榜标签
	 * @param $data
	 */
	public function hits($data) {
		$modelid = isset($data['modelid']) ? intval($data['modelid']) : 0;
		$catid = isset($data['catid']) ? intval($data['catid']) : '';
		$all = isset($data['all']) ? true : false;
		
		if($catid){
			$category = get_category($catid, '', $all);
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
			$day = isset($data['day']) ? ' AND updatetime>'.(SYS_TIME - intval($data['day'])*86400) : '';
			$thumb = isset($data['thumb']) ? ($data['thumb'] ? " AND thumb <> ''" : " AND thumb = ''") : '';
			$where = 'status=1'.$catid.$day.$thumb;
		}
		
		return $this->db->field($field)->where($where)->order('`click` DESC')->limit($limit)->select();
	}

	
	
	/**
	 * 栏目导航标签
	 * @param $data
	 */
	public function nav($data) {
		$siteid = isset($data['siteid']) ? intval($data['siteid']) : get_siteid();
		$where = 'siteid = '.$siteid.' AND `display`=1';
		$field = isset($data['field']) ? $data['field'] : '*';
		$order = isset($data['order']) ? $data['order'] : 'listorder ASC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';

		if(isset($data['where'])) $where .= ' AND '.$data['where'];
		return D('category')->field($field)->where($where)->order($order)->limit($limit)->select();
	}	
	
	

	/**
	 * 友情链接标签
	 * @param $data
	 */
	public function link($data) {
		$siteid = isset($data['siteid']) ? intval($data['siteid']) : get_siteid();
		$field = isset($data['field']) ? $data['field'] : '*';
		$where = 'siteid = '.$siteid.' AND status = 1';
		$order = isset($data['order']) ? $data['order'] : 'listorder ASC, id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		if(isset($data['typeid'])){
			$where .= ' AND typeid = '.intval($data['typeid']);
		}
		if(isset($data['where'])){
			$where = $data['where'];
		}else{
			$where .= isset($data['thumb']) ? ($data['thumb'] ? " AND logo <> ''" : " AND logo = ''") : '';
		}
		return D('link')->field($field)->where($where)->order($order)->limit($limit)->select();
	}	
	
	
	
	/**
	 * TAG标签(所有tag列表)
	 * @param $data
	 */
	public function tag($data) {
		$siteid = isset($data['siteid']) ? intval($data['siteid']) : get_siteid();
		$field = isset($data['field']) ? $data['field'] : 'id,tag,click,total';
		$catid = isset($data['catid']) ? intval($data['catid']) : 0;
		$order = isset($data['order']) ? $data['order'] : 'id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';
		$where = $catid ? 'siteid='.$siteid.' AND catid='.$catid : 'siteid='.get_siteid();

		if(isset($data['page'])){
			yzm_base::load_sys_class('page','',0);
			$this->total = D('tag')->where($where)->total();
			$this->page = new page($this->total, $limit);
			$limit = $this->page->limit();
		}
		
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
		return  D('tag_content')->field('id,tag')->join('yzmcms_tag b ON yzmcms_tag_content.tagid=b.id')->where('yzmcms_tag_content.modelid='.$modelid.' AND yzmcms_tag_content.aid='.$id)->limit($limit)->order('id ASC')->select();
	}	
	
	
	
	/**
	 * 相关内容标签(根据tag标签获取)
	 * @param $data
	 */
	public function relation($data) {
		$modelid = isset($data['modelid']) ? intval($data['modelid']) : 1;
		$relation = isset($data['relation']) ? intval($data['relation']) : $modelid;
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
		
		$limit = $modelid==$relation ? $limit+1 : $limit;
		$res = $tag_content->field('aid')->where('modelid='.$relation.' AND tagid IN ('.implode(',', $tagid).')')->group('aid')->limit($limit)->order('aid DESC')->select();
		$ids = array();
		foreach($res as $val){
			if($modelid==$relation && $val['aid']==$id) continue;	//去除当前的内容ID
			$ids[] = $val['aid'];
		}
		if(empty($ids)) return false;

		if($modelid!=$relation && !$this->_set_model($relation)) return false;
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
		$where = isset($data['where']) ? $data['where'] : 'siteid='.get_siteid().' AND `ischeck` = 1';
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
		$where = 'siteid='.get_siteid();
		$field = isset($data['field']) ? $data['field'] : 'title,url,total,catid';
		if(isset($data['modelid'])) $where .= ' AND modelid='.intval($data['modelid']);
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
		return D('comment')->field('userid,username,userpic,inputtime,content,title,url,catid,modelid')->join('yzmcms_comment_data b ON yzmcms_comment.commentid=b.commentid')->where('yzmcms_comment.siteid='.get_siteid().' AND `status`=1')->order('id DESC')->limit($limit)->select();
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
	 * 搜索标签
	 * @param $data
	 */
	public function search($data) {
		$siteid = isset($data['siteid']) ? intval($data['siteid']) : 0;
		$catid = isset($data['catid']) ? intval($data['catid']) : 0;
		$modelid = isset($data['modelid']) ? intval($data['modelid']) : 0;
		$keyword = isset($data['keyword']) ? $data['keyword'] : '';
		$field = isset($data['field']) ? $data['field'] : '*';
		$search = isset($data['search']) ? $data['search'] : 'title';
		$order = isset($data['order']) ? $data['order'] : 'id DESC';
		$limit = isset($data['limit']) ? $data['limit'] : '20';

		$action = in_array(ROUTE_A,array('init', 'tag', 'archives')) ? ROUTE_A : 'init';
		switch($action) {
			case 'init' :
				$cat_where = '';
				if($catid){
					$category = get_category($catid);
					if(!$category) return false;
					$arrchildid = $category['arrchildid'];
					$cat_where = strpos($arrchildid, ',') ? ' AND catid IN ('.$arrchildid.')' : ' AND catid='.$arrchildid;
				}
				if($modelid){
					if(!$this->_set_model($modelid)) return false;
					$db = $this->db;
					$whereor = array();
					foreach(explode(',', $search) as $val){
					    $whereor[] = "`{$val}` LIKE '%$keyword%'";
					}
					$where = '`status` = 1'.$cat_where.' AND ('.join(' OR ', $whereor).')';
				}else{
					$where = 'siteid = '.$siteid.' AND `status` = 1'.$cat_where." AND `title` LIKE '%$keyword%'";
					$db = D('all_content');
				}

				if(isset($data['page'])){
					$this->total = $db->where($where)->total();
					$this->page = new page($this->total, $limit);
					$limit = $this->page->limit();
				}

				if($modelid) return $db->field($field)->where($where)->order($order)->limit($limit)->select();

				$data = $db->field('modelid,id')->where($where)->order('allid DESC')->limit($limit)->select();
				$search_data = array();
				foreach ($data as $value) {
					$res = D(get_model($value['modelid']))->field($field)->where(array('id'=>$value['id']))->find();
					if(!$res) continue;
					$search_data[] = $res;
				}
				return $search_data;
			case 'tag' : 
				$tagid = isset($_GET['id']) ? intval($_GET['id']) : 0;
				$where = array('siteid'=>$siteid,'tagid'=>$tagid);
				$db = D('tag_content');

				if(isset($data['page'])){
					$this->total = $db->where($where)->total();
					$this->page = new page($this->total, $limit);
					$limit = $this->page->limit();
				}

				$data = $db->field('modelid,aid')->where($where)->order('modelid ASC,aid DESC')->limit($limit)->select();
				$search_data = array();
				foreach ($data as $value) {
					$res = D(get_model($value['modelid']))->field($field)->where(array('id'=>$value['aid'],'status'=>1))->find();
					if(!$res) continue;
					$search_data[] = $res;
				}
				return $search_data;
			case 'archives' : 
				$pubtime = isset($_GET['pubtime']) ? intval($_GET['pubtime']) : 0;
				$date = date('Y-m', $pubtime);
				$starttime = strtotime($date); 
				$endtime = strtotime("$date +1 month");
				if(!$starttime || !$endtime) return false;  

				$where = '`status` = 1 AND inputtime BETWEEN '.$starttime.' AND '.$endtime;
				if(!$this->_set_model($modelid)) return false;
				$db = $this->db; 

				if(isset($data['page'])){
					$this->total = $db->where($where)->total();
					$this->page = new page($this->total, $limit);
					$limit = $this->page->limit();
				}

				return $db->field($field)->where($where)->order($order)->limit($limit)->select();
			default :
				return false;
		}
		
	}

	
	
	/**
	 * 自定义SQL标签
	 * @param $data
	 */
	public function get($data) {
		if(!isset($data['sql'])) return false;
		$sql = $data['sql'];
		$where = isset($data['where']) ? ' WHERE '.$data['where'] : '';
		$order = isset($data['order']) ? ' ORDER BY '.$data['order'] : '';
		$limit = isset($data['limit']) ? $data['limit'] : '20';

		$db = D('admin');
		$sql = $sql.$where;
		if(isset($data['page'])){
			yzm_base::load_sys_class('page','',0);
			$countsql = 'SELECT COUNT(*) AS total FROM ('.$sql.') T';
			$r = $db->query($countsql, false);
			$this->total = $r['total'];
			$this->page = new page($this->total, $limit);
			$limit = $this->page->limit();
		}
		$sql = $sql.$order.' LIMIT '.$limit;
		return $db->query($sql);
	}	
		
	
	/**
	 * 设置模型
	 * @param $modelid
	 */
	private function _set_model($modelid) {
		$model_info = yzm_array_column(get_site_modelinfo(), 'tablename', 'modelid');
		if(!isset($model_info[$modelid])) return false;
		$this->tablename = $model_info[$modelid];
		$this->db = D($this->tablename);
		return true;
	}
	
}