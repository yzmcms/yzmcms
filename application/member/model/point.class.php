<?php
/**
 * 会员余额/积分处理类   
 * 
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2017-01-16
 */
class point {
	
	//类型
	public $pay_type = array(
		0 => '每日登录',
		1 => '评论奖励',
		2 => '投稿奖励',
		3 => '会员充值',
		4 => '后台充值',
		5 => '积分兑换',
		6 => '积分购买',
		7 => '阅读收费',
		8 => '下载收费',
		9 => '发表帖子',
		10 => '每日签到',
		11 => '投稿收费',
		12 => '其他'
	);
	
	
	/**
	 * 添加积分/金钱记录
	 * @param integer $type           添加类型 1积分,2金钱
	 * @param integer $value          增加金额
	 * @param integer $pay_type       类型代号
	 * @param integer $userid         用户ID
	 * @param integer $experience     当前经验
	 * @param string $username        用户名
	 * @param string $remarks         说明
	 * @param string $adminnote     操作人用户名
	 * @param string $mod_experience  是否修改经验【后台操作无需修改经验】
	 */
	public function point_add($type, $value, $pay_type, $userid, $username, $experience = 0, $remarks = '', $adminnote = '', $mod_experience = true) {

		if($type == '1') $value = intval($value);
		if($value == 0) return false;
		
		$data = array();
		$data['trade_sn'] = create_tradenum();
		$data['money'] = $value;
		$data['userid'] = $userid;
		$data['username'] = $username;
		$data['type'] = $type;
		$data['msg'] =  $this->pay_type[$pay_type];
		$data['remarks'] = htmlspecialchars($remarks);
		$data['creat_time'] = SYS_TIME;
		$data['ip'] = getip();
		$data['adminnote'] = $adminnote;
		$data['status'] = 1;
		
		//自增积分/金钱或经验
		$update = $type == '1' ? '`point`=`point`+'.$value : '`amount`=`amount`+'.$value;
		
		//若有修改经验
		if($mod_experience){
			//增加经验数量
			$update .= ',`experience`=`experience`+'.$value;
			
			if(!$experience) $experience = D('member')->field('experience')->where(array('userid' => $userid))->one();
			//检查并更新会员组
			$this->_check_update_group($value, $experience, $userid);
		} 
		
		D('member')->update($update, array('userid'=>$userid)); 
		D('pay')->insert($data);
	}
	
	
	/**
	 * 消费积分/金钱记录
	 * @param integer $type           扣除类型 1积分,2金钱
	 * @param integer $value          扣除金额
	 * @param integer $pay_type       类型代号
	 * @param integer $userid         用户ID
	 * @param string $username        用户名
	 * @param string $remarks         说明
	 * @return bool
	 */
	public function point_spend($type, $value, $pay_type, $userid = '', $username = '', $remarks = '') {

		if($value == 0) return false;
		
		if(!$userid || !$username || $value<0) return_message(L('illegal_parameters'), 0);
		$data = D('member')->field('point,amount')->where(array('userid'=>$userid))->find();
		if($type == '1'){
			if(($data['point']-$value)<0) return_message('积分不足本次交易！', 0);
			$update = '`point`=`point`-'.$value;
		}else{
			if(($data['amount']-$value)<0) return_message('账户余额不足本次交易！', 0);
			$update = '`amount`=`amount`-'.$value;
		}
		
		$data = array();
		$data['trade_sn'] = create_tradenum();
		$data['money'] = $value;
		$data['userid'] = $userid;
		$data['username'] = $username;
		$data['type'] = $type;
		$data['msg'] =  $this->pay_type[$pay_type];
		$data['remarks'] = htmlspecialchars($remarks);
		$data['creat_time'] = SYS_TIME;
		$data['ip'] = getip();
		
		$affected = D('member')->update($update, array('userid'=>$userid));  //自减积分/金钱，不减经验
		if(!$affected) return false;
		return D('pay_spend')->insert($data);
	}
	
	
	/**
	 * 根据用户经验检查并更新用户组
	 * @param integer $add           自增数量
	 * @param integer $experience    当前经验
	 * @param integer $userid        用户ID
	 */
	protected function _check_update_group($add, $experience, $userid) {
		$new_groupid = 1;
		$groupid = intval(get_cookie('_groupid'));
		
		$data = get_groupinfo();
		if(!$data) return false;
		$exp = $experience+$add;
		foreach ($data as $k=>$v) {
			$experience_list[$v['groupid']] = $v['experience'];
		}
		arsort($experience_list);
		
		//如果超出用户组积分设置则为积分最高的用户组
		if($exp >= max($experience_list)) {
            $new_groupid = key($experience_list);
        } else {
            foreach ($experience_list as $k=>$v) {
                if($exp >= $v) {
                    $new_groupid = $k;
                    break;
                }
            }
        }

		if($new_groupid != $groupid) {
			set_cookie('_groupid', $new_groupid, 0, true);
			D('member')->update(array('groupid'=>$new_groupid), array('userid' => $userid));
		}
	}
}