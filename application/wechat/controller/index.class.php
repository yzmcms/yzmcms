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

class index{
	
	//token签名
    private $token;

	//消息对象
    private $msgobj;
	
    //消息类型
    private $msgtype;

	
	public function __construct(){
		$this->token = get_config('wx_token');
	}

	
	/**
     *  执行程序入口
     */
    public function init(){
		if (!isset($_GET['echostr'])) {		
			$this->_receivemsg();   //接收消息
		}else{ 
			$this->valid();   		//校验签名
		}
    }
	
	
    /**
     *  初次校验
     */	
	public function valid(){
        $echostr = $_GET['echostr'];
        if($this->_check_signature()){
        	echo $echostr; exit;
        }
    }
	
	
	/**
     *  获取消息
     */
    private function _receivemsg(){
        //验证消息的真实性
        if(!$this->_check_signature()) showmsg(L('illegal_operation'), 'stop');
		
        //接收消息
        $poststr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents('php://input');
        if(!empty($poststr)){
        	if (PHP_VERSION_ID < 80000) {
        	    libxml_disable_entity_loader(true); //禁止引用外部xml实体
        	}
            $this->msgobj = simplexml_load_string($poststr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->msgtype = strtolower($this->msgobj->MsgType);
			switch ($this->msgtype) {
				case "text":
					$result = $this->_receivetext();     //接收文本消息
					break;
				case "image":
					$result = $this->_receiveimage();   //接收图片消息
					break;
				case "location":
					$result = $this->_receivelocation();  //接收位置消息
					break;
				case "voice":
					$result = $this->_receivevoice();   //接收语音消息
					break;
				case "event":
					$result = $this->_receiveevent();  //接收事件消息
					break;
				default:
					$result = "unknown msg type: ".$this->msgtype;   //未知的消息类型
					break;
			}
			
			if($result){
				// M('wechat')->logger($result);  //调试程序
				echo $result;				
			}
        }else{
            $this->msgobj = null;
        }
    }
	
	
	
    /**
     *  接收文本消息
     */
    private function _receivetext(){

        $content = trim($this->msgobj->Content);
		
		$arr['openid'] = $this->msgobj->FromUserName;
		$arr['content'] = $content;
		$arr['inputtime'] = SYS_TIME;
		$arr['msgtype'] = $this->msgtype;
		D('wechat_message')->insert($arr);
		$result = M('wechat')->text_action($content);	//处理文本消息
		if($result){
			if($result['type'] == 'text'){
				return $this->_replytext($result['content']);
			}else{
				return $this->_replynews($result['content']);
			}
		}else{
			return false;
		}
    }
	

	
    /**
     *  接收图片消息
     */
    private function _receiveimage(){
		$content = array("MediaId" => $this->msgobj->MediaId);
		return $this->_replyimage($content);
    }
	

    /**
     *  接收位置消息
     */
    private function _receivelocation(){
        $content = "位置消息，纬度为：".$this->msgobj->Location_X."；经度为：".$this->msgobj->Location_Y."；缩放级别为：".$this->msgobj->Scale."；位置为：".$this->msgobj->Label;
		$arr['openid'] = $this->msgobj->FromUserName;
		$arr['content'] = $content;
		$arr['inputtime'] = SYS_TIME;
		$arr['msgtype'] = $this->msgtype;
		D('wechat_message')->insert($arr);
        return $this->_replytext($content);
    }

	
    /**
     *  接收语音消息
     */
    private function _receivevoice(){
		//只有开启了“接收语音识别结果”，才会有这个属性
        if (isset($this->msgobj->Recognition) && !empty($this->msgobj->Recognition)){
            $content = "你刚才说的是：".$this->msgobj->Recognition;
            return $this->_replytext($content);
        }else{
            $content = array("MediaId" => $this->msgobj->MediaId);
            return $this->_replyvoice($content);
        }
    }


	
    /**
     *  接收事件消息
     */
    private function _receiveevent(){
		$content = false;
		$event = strtolower($this->msgobj->Event);
		switch ($event) {
			case "subscribe":
				$content = getcache('wechat_reply_3');
				if(!empty($this->msgobj->EventKey)){
					$scan = str_replace('qrscene_', '', $this->msgobj->EventKey); 
					M('wechat')->add_wechat_user($this->msgobj->FromUserName, $scan);  //扫描带参数的二维码
				}else{
					M('wechat')->add_wechat_user($this->msgobj->FromUserName);
				}
				break;
				
			case "unsubscribe":
				M('wechat')->del_wechat_user($this->msgobj->FromUserName);
				break;
				
			case "click":
				$result = M('wechat')->text_action($this->msgobj->EventKey);	//click事件，处理文本消息
				if($result){
					if($result['type'] == 'text'){
						return $this->_replytext($result['content']);
					}else{
						return $this->_replynews($result['content']);
					}
				}else{
					return false;
				}
				break;
				
			case "scan":
				M('wechat')->scan_action($this->msgobj->FromUserName, $this->msgobj->EventKey);  //处理扫描带参数的二维码事件
				break;
		}
		
		return $content ? $this->_replytext($content) : false;
    }

	
    /**
     *  回复文本消息
     */	
    private function _replytext($content){
        $xmltpl = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[text]]></MsgType>
		<Content><![CDATA[%s]]></Content>
		</xml>';
		return sprintf($xmltpl, $this->msgobj->FromUserName, $this->msgobj->ToUserName, SYS_TIME, $content);
    }
	

	
    /**
     *  回复图片消息
     */	
    private function _replyimage($imagearray){
		$xmltpl = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[image]]></MsgType>
		<Image>
		<MediaId><![CDATA[%s]]></MediaId>
		</Image>
		</xml>';
		return sprintf($xmltpl, $this->msgobj->FromUserName, $this->msgobj->ToUserName, SYS_TIME, $imagearray['MediaId']);
    }


	
    /**
     *  回复语音消息
     */	
    private function _replyvoice($voicearray){
		$xmltpl = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[voice]]></MsgType>
		<Voice>
		<MediaId><![CDATA[%s]]></MediaId>
		</Voice>
		</xml>';
		return sprintf($xmltpl, $this->msgobj->FromUserName, $this->msgobj->ToUserName, SYS_TIME, $voicearray['MediaId']);
    }

	
    /**
     *  回复视频消息
     */	
    private function _replyvideo($videoarray){
		
		$xmltpl = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[video]]></MsgType>
		<Video>
		<MediaId><![CDATA[%s]]></MediaId>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		</Video> 
		</xml>';

        return sprintf($xmltpl, $this->msgobj->FromUserName, $this->msgobj->ToUserName, SYS_TIME, $videoarray['mediaid'], $videoarray['title'], $videoarray['description']);
    }

	
    /**
     *  回复图文消息
	 *  @param array $newsarray title,description,picurl,url--标题，描述，图片URL，内容URL
     */	
    private function _replynews($newsarray){
        if(!is_array($newsarray)) return false;
		
        $itemtpl = '<item>
						<Title><![CDATA[%s]]></Title>
						<Description><![CDATA[%s]]></Description>
						<PicUrl><![CDATA[%s]]></PicUrl>
						<Url><![CDATA[%s]]></Url>
					</item>';
        $item_str = '';
        foreach ($newsarray as $item){
            $item_str .= sprintf($itemtpl, $item['title'], $item['description'], $item['picurl'], $item['url']);
        }
		
        $xmltpl = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[news]]></MsgType>
		<ArticleCount>%s</ArticleCount>
		<Articles>'.$item_str.'</Articles>
		</xml>';

        return sprintf($xmltpl, $this->msgobj->FromUserName, $this->msgobj->ToUserName, SYS_TIME, count($newsarray));
    }


    /**
     *  回复音乐消息
	 *  @param array $musicarray title,description,url,hqurl--标题，描述，音乐URL，高质量音乐URL
     */	
    private function _replymusic($musicarray){
		$xmltpl = '<xml>
		<ToUserName><![CDATA[%s]]></ToUserName>
		<FromUserName><![CDATA[%s]]></FromUserName>
		<CreateTime>%s</CreateTime>
		<MsgType><![CDATA[music]]></MsgType>
		<Music>
		<Title><![CDATA[%s]]></Title>
		<Description><![CDATA[%s]]></Description>
		<MusicUrl><![CDATA[%s]]></MusicUrl>
		<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
		</Music>
		</xml>';

        return sprintf($xmltpl, $this->msgobj->FromUserName, $this->msgobj->ToUserName, SYS_TIME, $musicarray['title'], $musicarray['description'], $musicarray['url'], $musicarray['hqurl']);
    }

	
    /**
     *  校验签名
     */
    private function _check_signature(){	
		if($this->token == '') return false;
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];         
        $token = $this->token;
        $tmparr = array($token, $timestamp, $nonce);
        sort($tmparr, SORT_STRING);
        $tmpstr = implode($tmparr);
        $tmpstr = sha1($tmpstr);
        return ($tmpstr == $signature) ? true : false;
    }
}