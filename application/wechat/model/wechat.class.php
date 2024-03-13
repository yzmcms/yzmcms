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

	 
	 
class wechat {

	public $appid,$secret;
	
    /**
     *  添加微信用户
     */	
	public function add_wechat_user($openid, $scan){
		$info = $this->get_userinfo($openid);
		if(!$info) return false;
		
		$wechat_user = D('wechat_user');
		$update = array('subscribe' => 1, 'subscribe_time' => $info['subscribe_time']);
		if($scan){
			$info['scan'] = $scan;
			$update['scan'] = $scan;
		} 
		if($wechat_user->where(array('openid' => $openid))->total()){
			$wechat_user->update($update, array('openid' => $openid));
		}else{
			$wechat_user->insert($info);
		}
    }	
	
	
    /**
     *  删除微信用户
     */	
	public function del_wechat_user($openid){
		
		D('wechat_user')->update(array('subscribe' => 0), array('openid' => $openid));
    }
	

    /**
     *  处理文本消息
     */	
	public function text_action($keyword){
		$wechat_auto_reply = D('wechat_auto_reply');
		
		//处理完全匹配关键字
		$data = $wechat_auto_reply->field('keyword,keyword_type,relation_id,content')->where(array('type' => 1, 'keyword' => $keyword))->find();
		if($data){
			//如果是图文回复
			if($data['relation_id']){
				$wx_relation_model = get_config('wx_relation_model');
				if(!$wx_relation_model) return false;
				$model_db = get_model($wx_relation_model);
				if(!$model_db) return false;
				$model_db = D($wx_relation_model);
				$where = strpos($data['relation_id'], ',') ? 'id IN ('.$data['relation_id'].')' : 'id = '.$data['relation_id'];
				$news = $model_db->field('title, description, thumb AS picurl, url')->where($where)->order('id DESC')->select();
				return array('type' => 'news', 'content' => $news);
			}
			
			//文本回复
			return array('type' => 'text', 'content' => $data['content']);
		}
		
		
		//处理模糊搜索的关键字
		$data = $wechat_auto_reply->field('keyword,keyword_type,relation_id,content')->where(array('type' => 1, 'keyword_type' => 0))->select();
		foreach($data as $val){
			if(strpos($keyword, $val['keyword']) !== false){
				//如果是图文回复
				if($data['relation_id']){
					$wx_relation_model = get_config('wx_relation_model');
					if(!$wx_relation_model) return false;
					$model_db = get_model($wx_relation_model);
					if(!$model_db) return false;
					$model_db = D($wx_relation_model);
					$where = strpos($data['relation_id'], ',') ? 'id IN ('.$data['relation_id'].')' : 'id = '.$data['relation_id'];
					$news = $model_db->field('title, description, thumb AS picurl, url')->where($where)->order('id DESC')->select();
					return array('type' => 'news', 'content' => $news);
				}
				
				//文本回复
				return array('type' => 'text', 'content' => $val['content']);
			}			
		}
		
		//没有匹配到任何关键字，则获取自动回复
		$content = getcache('wechat_reply_2');
		return $content ? array('type' => 'text', 'content' => $content) : false;
    }
	
	
    /**
     *  处理扫描带参数的二维码事件
     */	
	public function scan_action($openid, $scan){
		

    }
	

    /**
     *  获取用户基本信息
     */	
	private function get_userinfo($openid){
		
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->get_access_token().'&openid='.$openid.'&lang=zh_CN';
        $json_arr = https_request($url);

		if(isset($json_arr['errcode'])){
			return false;
		}
		
		return $json_arr;
    }
	
	
    /**
     *  记录日志
     */
    public function logger($content){
	    $max_size = 100000;  
	    $log_filename = YZMPHP_PATH.'wechat_log.html';  
	    if(file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size)){
		    unlink($log_filename);
	    }
	    file_put_contents($log_filename, date('H:i:s')." --- ".$content."\n", FILE_APPEND);  
    }

	
    /**
     *  获取access_token
     */	
	protected function get_access_token(){
		
		if(!$access_token = getcache('wechat_access_token')){
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->secret;

			$access_token = https_request($url);
			
			if(isset($access_token['errcode'])) return false;
			
			setcache('wechat_access_token', $access_token, 7000);
		}
        	
		return $access_token['access_token'];
    }	
	
	
    /**
     *  不转义中文的json_encode
     */	
	protected function my_json_encode($array){
		if(version_compare(PHP_VERSION,'5.4.0','<')) {
			foreach($array as $key => $value){  
				if(!is_array($value)){
					$jsonstr[$key] = urlencode($value);
				}else{
					$jsonstr[$key] = urlencode($this->my_json_encode($value));
				}
			}  
			$jsonstr = urldecode(json_encode($jsonstr)); 
			$jsonstr = str_replace(']"', ']', str_replace('"[', '[', $jsonstr)); 
		}else{
			$jsonstr = json_encode($array, JSON_UNESCAPED_UNICODE);  //必须PHP5.4+  
		}	
		return $jsonstr;
	}	

}