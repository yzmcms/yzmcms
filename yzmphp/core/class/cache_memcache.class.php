<?php 
/**
 * cache_memcache.class.php    缓存memcache类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-05-05
 */
 
class cache_memcache {

    protected $link = null;
    protected $config  = array(
		'host'       => '127.0.0.1',	// memcache主机
        'port'       => 11211,			// memcache端口
        'timeout'    => 0,				// 超时时间(秒)
        'expire'     => 0,				// 有效期(秒)
        'persistent' => false,			// 是否长连接
        'prefix'     => '',				// 前缀
	);	

    /**
     * 构造函数
     * @param array $config 
     * @access public
     */
    public function __construct($config = array()){
		if (!extension_loaded('memcache')) {
            showmsg('not support: memcache', 'stop');
        }
		if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
		$this->link = new Memcache();
		$this->link->addServer($this->config['host'], $this->config['port'], $this->config['persistent']);
	}


	
	/**
	 * 设置数据
	 * @param	string   $name
	 * @param	void 	 $value
	 * @param	string   $expire
	 * @return  void
	 */
	public function set($name, $value, $expire = null) {
		$name = $this->config['prefix'].$name;
		$expire = $expire ? $expire : $this->config['expire'];
		if(is_array($value)){
            $value = json_encode($value);
        }
		return $this->link->set($name, $value, false, $expire);
	}
	

	/**
	 * 获取数据
	 * @param	string   $name
	 * @return  string
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
		return $this->link->delete($name);
	}

	/**
	 * 清空数据
	 * @return  void
	 */
	public function flush() {
		return $this->link->flush();
	}
}