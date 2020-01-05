<?php
/**
 * cache_redis.class.php   Redis缓存类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-05-05
 */
 
class cache_redis{
	
    protected $link = null;
    protected $config  = array(
		'host'       => '127.0.0.1',	// redis主机
        'port'       => 6379,			// redis端口
        'password'   => '',				// 密码
        'select'     => 0,				// 操作库
        'timeout'    => 0,				// 超时时间(秒)
        'expire'     => 3600,			// 有效期(秒)
        'persistent' => false,			// 是否长连接
        'prefix'     => '',				// 前缀
	);	
	
	
    /**
     * 构造函数
     * @param array $config 
     * @access public
     */
    public function __construct($config = array()){
        if (!extension_loaded('redis')) {
            showmsg('not support: redis', 'stop');
        }
        if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
        $this->link = new Redis();
        if ($this->config['persistent']) {
            $this->link->pconnect($this->config['host'], $this->config['port'], $this->config['timeout'], 'persistent_id_' . $this->config['select']);
        } else {
            $this->link->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        }

        if ('' != $this->config['password']) {
            $this->link->auth($this->config['password']);
        }

        if (0 != $this->config['select']) {
            $this->link->select($this->config['select']);
        }
    }

	/**
	 * 设置数据
	 * @param	string   $name
	 * @param	void   $value 除对象类型外的所有类型数据
	 * @param	int   $expire  有效时间（秒）
	 * @return  void
	 */
	public function set($name, $value, $expire = null) {
		$name = $this->config['prefix'].$name;
		$expire = $expire ? $expire : $this->config['expire'];
		if(is_array($value)){
            $value = json_encode($value);
        }
		if ($expire == 0) {
            $ret = $this->link->set($name, $value);
        } else {
            $ret = $this->link->setex($name, $expire, $value);
        }
		return $ret;
	}

	/**
	 * 获取数据
	 * @param	string   $name
	 * @return  void
	 */
	public function get($name) {
		$name = $this->config['prefix'].$name;
		$value = $this->link->get($name);
		$value_serl = json_decode($value, true);
        if(is_array($value_serl)){
            return $value_serl;
        }
        return $value;
	}

	/**
	 * 删除数据
	 * @param	string   $name
	 * @return  void
	 */
	public function delete($name) {
		$name = $this->config['prefix'].$name;
		return $this->link->del($name);
	}

	/**
	 * 清空数据
	 * @return  void
	 */
	public function flush() {
		return $this->link->flushall();
	}


}