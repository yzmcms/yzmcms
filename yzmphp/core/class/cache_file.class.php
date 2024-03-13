<?php 
/**
 * cache_file.class.php    缓存文件类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2018-05-05
 */

class cache_file{ 
 
    protected $config = array();
     
	 
    /**
     * 构造函数
     */
    public function __construct($config = array()){
		//缓存默认配置
        $this->config = array(
			'cache_dir'   => YZMPHP_PATH.'cache/cache_file/',    //缓存文件目录
			'suffix'      => '.cache.php',  //缓存文件后缀
			'mode'        => '1',           //缓存格式：mode 1 为serialize序列化, mode 2 为保存为可执行文件array
		);
		
		if (!empty($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }
 
 
    /**
     * 得到缓存信息
     * 
     * @param string $id
     * @return boolean|array
     */
    public function get($id){

        //缓存文件不存在
        if(!$this->has($id)){
            return false;
        }
         
        $file = $this->_file($id);
         
        $data = $this->_filegetcontents($file);
         
        if($data['expire'] == 0 || SYS_TIME < $data['expire']){
            return $data['contents'];
        }
        return false;
    }
     
    /**
     * 设置一个缓存
     * 
     * @param string $id   缓存id
     * @param void   $data 除对象类型外的所有类型数据
     * @param int    $cachelife 缓存生命 默认为0无限生命
     */
    public function set($id, $data, $cachelife = 0){
        $cache  = array();
        $cache['contents'] = $data;
        $cache['expire']   = $cachelife === 0 ? 0 : SYS_TIME + $cachelife;
        $cache['mtime']    = SYS_TIME;
        
		if(!is_dir($this->config['cache_dir'])) {
			@mkdir($this->config['cache_dir'], 0777, true);
	    }
		
        $file = $this->_file($id);
         
        return $this->_fileputcontents($file, $cache);
    }
     
    /**
     * 清除一条缓存
     * 
     * @param string cache id    
     * @return void
     */  
    public function delete($id){
        if(!$this->has($id)){
            return false;
        }
        $file = $this->_file($id);
        //删除该缓存
        return unlink($file);
    }
   
     
    /**
     * 删除所有缓存
     * @return boolean
     */
    public function flush(){
        
        $glob = glob($this->config['cache_dir'] . '*' . $this->config['suffix']);
         
        if(empty($glob)){
            return false;
        }
         
        foreach ($glob as $v){
			$id =  $this->_filenametoid(basename($v));
            $this->delete($id);
        }
        return true;
    }
	
	
    /**
     * 判断缓存是否存在
     * 
     * @param string $id cache_id
     * @return boolean true 缓存存在 false 缓存不存在
     */
    public function has($id){
        
        $file  = $this->_file($id);
         
        if(!is_file($file)){
            return false;
        }
        return true;
    }
     

    /**
     * 通过缓存id得到缓存信息路径
     * @param string $id
     * @return string 缓存文件路径
     */
    protected function _file($id){
        
        $filenmae  = $this->_idtofilename($id);
        return $this->config['cache_dir'] . $filenmae;
    }   
     

    /**
     * 通过id得到缓存信息存储文件名
     * 
     * @param  $id
     * @return string 缓存文件名
     */
    protected function _idtofilename($id){
        
        return $id . $this->config['suffix'];
    }
     
  
    /**
     * 通过filename得到缓存id
     * 
     * @param  $id
     * @return string 缓存id
     */
    protected function _filenametoid($filename){
        
        return str_replace($this->config['suffix'], '', $filename);
    }

	
    /**
     * 把数据写入文件
     * 
     * @param string $file 文件名称
     * @param array  $contents 数据内容
     * @return int | false 
     */
    protected function _fileputcontents($file, $contents){
        if($this->config['mode'] == 1){
            $contents = "<?php exit('NO.'); ?>\n".serialize($contents);
        }else{
            $contents = "<?php\nreturn ".var_export($contents, true).";\n?>";
        }
		
		$filesize = file_put_contents($file, $contents, LOCK_EX);
        return $filesize ? $filesize : false;
    }
     

    /**
     * 从文件得到数据
     * 
     * @param  string $file
     * @return boolean|array
     */
    protected function _filegetcontents($file){
        if(!file_exists($file)){
            return false;
        }
         
        if($this->config['mode'] == 1){
            $handle = @fopen($file, 'r');
            fgets($handle);
            return unserialize(fgets($handle));
        }else{
            return @require $file;
        }
    }
}