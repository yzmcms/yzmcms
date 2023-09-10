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
yzm_base::load_controller('common', 'admin', 0);
yzm_base::load_sys_class('databack', '', 0);

class database extends common {
	
	public $config;  
	
	public function __construct(){
		parent::__construct();
		$this->config = array(
			'path'     => YZMPHP_PATH.'cache'.DIRECTORY_SEPARATOR.'databack'.DIRECTORY_SEPARATOR, //备份文件目录
			'part'     => 31457280, //30MB
			'compress' => 1,    //是否压缩
			'level'    => 4,	//压缩水平
        );
	}


	/**
	 * 数据库列表
	 */
	public function init() {
		$data = D('admin')->query('SHOW TABLE STATUS');		
		include $this->admin_tpl('database_list');
	}
	

	/**
	 * 数据库备份列表
	 */
	public function databack_list() {
		$data = array();
		$list = glob($this->config['path'].'*');
		foreach($list as $name){
			
			if(preg_match('/^[a-z]{6}-\d{8}-\d{6}-\d+\.sql(?:\.gz)?$/', basename($name))){
				$info['filesize'] = sizecount(filesize($name));
                $info['filename'] = basename($name);
				$name = sscanf($info['filename'], '%6s-%4s%2s%2s-%2s%2s%2s-%d');
                $info['backtime'] = $name[1].'-'.$name[2].'-'.$name[3].' '.$name[4].':'.$name[5].':'.$name[6];
				$info['random'] = $name[0];
                $info['part'] = $name[7];
				$info['time'] = strtotime($info['backtime']);
                $data[] = $info;

            } 
		}

		include $this->admin_tpl('databack_list');
	}
	
	
	/**
	 * 优化表
	 */
	public function public_optimize() {
		$tables = input('post.tables');
		if(!$tables) return_json(array('status'=>0, 'message'=>L('lose_parameters')));
		$tables = is_array($tables) ? implode('`,`',array_map(array($this, '_safe_replace'), $tables)) : $this->_safe_replace($tables);
		D('admin')->query('OPTIMIZE TABLE `'.$tables.'`');
		return_json(array('status'=>1, 'message'=>L('operation_success')));
	}
	
	
	/**
	 * 修复表
	 */
	public function public_repair() {
		$tables = input('post.tables');
		if(!$tables) return_json(array('status'=>0, 'message'=>L('lose_parameters')));
		$tables = is_array($tables) ? implode('`,`',array_map(array($this, '_safe_replace'), $tables)) : $this->_safe_replace($tables);
		D('admin')->query('REPAIR TABLE `'.$tables.'`');
		return_json(array('status'=>1, 'message'=>L('operation_success')));
	}
	
	
	
	/**
	 * 表结构
	 */
	public function public_datatable_structure() {
		$table = isset($_GET['table']) ? trim($_GET['table']) : '';
		if(!$table) showmsg(L('lose_parameters'), 'stop');
		$data = D('admin')->query('SHOW CREATE TABLE `'.$this->_safe_replace($table).'`', false);
		include $this->admin_tpl('datatable_structure');
	}

	

	/**
	 * 备份文件删除
	 */
	public function databack_del() {
		if(!isset($_GET['random']) || !isset($_GET['time'])) return_json(array('status'=>0, 'message'=>'请指定要删除的文件'));

		$filename  = $_GET['random'].'-'.date('Ymd-His', intval($_GET['time'])) . '-*.sql*';
		$path  = $this->config['path'].$filename;
		array_map('unlink', glob($path));
		if(count(glob($path))){
			return_json(array('status'=>0, 'message'=>'备份文件删除失败，请检查权限！'));
		} else {
			return_json(array('status'=>1, 'message'=>'备份文件删除成功！'));
		}
	}
	
	
	/**
	 * 备份文件下载
	 */
	public function databack_down() {
		if(!isset($_GET['random']) || !isset($_GET['time']) || !isset($_GET['part'])) showmsg('请指定要下载的文件!', 'stop');

		$filename = $_GET['random'].'-'.date('Ymd-His', intval($_GET['time'])).'-'.intval($_GET['part']).'.sql';
		if($this->config['compress']) $filename .= '.gz';
		
		if(!is_file($this->config['path'].$filename)) showmsg($filename.'文件不存在!', 'stop');
		file_down($this->config['path'].$filename);
	}

	
	/**
	 * 数据库导出(此方法名不能更改export_list，因为在记录系统日志情况下会报错:1062)
	 */
	public function export_list() {
		function_exists('set_time_limit') && set_time_limit(0);
		if(!function_exists('gzopen')) return_json(array('status'=>0, 'message'=>'检测到未安装zlib扩展，请先安装!'));
        if(isset($_POST['dosubmit'])){ 
			$tables = isset($_POST['tables']) ? $_POST['tables'] : '';
			if(!$tables) return_json(array('status'=>0, 'message'=>'请指定要备份的表！'));
			
			//备份目录不存在，先创建目录
			if(!is_dir($this->config['path'])) @mkdir($this->config['path'], 0755, true);
			if(!is_file($this->config['path'].'index.html')) @file_put_contents($this->config['path'].'index.html', '');

            //检查是否有正在执行的任务，10分钟后自动解除
            $lock = $this->config['path'].'backup.lock';
            if(is_file($lock)){
            	if((SYS_TIME - filemtime($lock)) < 600){
            		return_json(array('status'=>0, 'message'=>'检测到有一个备份任务正在执行，请稍后再试！'));
            	}
            	@unlink($lock);
            }

            $len = @file_put_contents($lock, SYS_TIME); //创建锁文件
            if(!$len) return_json(array('status'=>0, 'message'=>$this->config['path'].'目录不存在或不可写，请检查！'));
			
            $backup_cache['backup_config'] = $this->config;

            //生成备份文件信息
            $file = array(
                'name' => random(6, 'abcdefghigklmzopqrstuvwxyz').'-'.date('Ymd-His'),
                'part' => 1,
            );
            $backup_cache['backup_file'] = $file;

            //缓存要备份的表
            $backup_cache['backup_tables'] = array_map(array($this, '_safe_replace'), $tables);
            setcache('backup_cache', $backup_cache);

            //创建备份文件
            $database = new databack($file, $this->config);
            if(false !== $database->create()){
                $tab = array('id' => 0, 'start' => 0);
                return_json(array('status'=>1, 'message'=>'初始化成功！', 'tab'=>$tab));
            } else {
                return_json(array('status'=>0, 'message'=>'初始化失败，备份文件创建失败！'));
            }

        } elseif (isset($_POST['id']) && isset($_POST['start'])) {

        	$backup_cache = getcache('backup_cache');
        	if(!is_array($backup_cache)) return_json(array('status'=>0, 'message'=>L('illegal_operation')));
        	
            $tables = $backup_cache['backup_tables'];
			$id = intval($_POST['id']);
			$start = intval($_POST['start']);
            $database = new databack($backup_cache['backup_file'], $backup_cache['backup_config']);
            $start  = $database->backup($tables[$id], $start);
            if(false === $start){  //出错
                return_json(array('status'=>0, 'message'=>'备份出错！'));
            } elseif (0 === $start) { //下一表
                if(isset($tables[++$id])){
                    $tab = array('id' => $id, 'start' => 0);
                    return_json(array('status'=>2, 'message'=>'表'.$tables[$id].'备份完成！', 'tab'=>$tab));
                } else {   //备份完成，清空缓存
                    @unlink($backup_cache['backup_config']['path'].'backup.lock');
                    delcache('backup_cache');
                    return_json(array('status'=>1, 'message'=>'备份全部完成！', 'url'=>U('databack_list')));
                }
            } else {
                $tab  = array('id' => $id, 'start' => $start[0]);
                $rate = round(100 * ($start[0] / $start[1]), 2);
                return_json(array('status'=>2, 'message'=>'表'.$tables[$id].'正在备份...('.$rate.'%)', 'tab'=>$tab));
            }

        } else {
            return_json(array('status'=>0, 'message'=>L('lose_parameters')));
        }
	}
	
	
	/**
	 * 数据库导入
	 */
	public function import() {
		function_exists('set_time_limit') && set_time_limit(0);
		if(!function_exists('gzopen')) return_json(array('status'=>0, 'message'=>'检测到未安装zlib扩展，请先安装!'));
		if(isset($_GET['time'])) {
            $filename  = $_GET['random'].'-'.date('Ymd-His', intval($_GET['time'])) . '-*.sql*';
			$path  = $this->config['path'].$filename;
            $files = glob($path);
            $list  = array();
            foreach($files as $name){
                $basename = basename($name);
                $match    = sscanf($basename, '%6s-%4s%2s%2s-%2s%2s%2s-%d');
                $gz       = preg_match('/^[a-z]{6}-\d{8}-\d{6}-\d+\.sql.gz$/', $basename);
                $list[$match[7]] = array($match[7], $name, $gz);
            }

            ksort($list);
			
            //检测文件正确性
            $last = end($list);
            if(count($list) === $last[0]){ 
                //缓存备份列表
				setcache('backup_list', $list);
                $data = array('part' => 1, 'start' => 0);
                return_json(array('status'=>1, 'message'=>'初始化成功！', 'data'=>$data));
            } else {
                return_json(array('status'=>0, 'message'=>'备份文件可能已经损坏，请检查！'));
            }
		} elseif(isset($_POST['part']) && isset($_POST['start'])) {
			$part = intval($_POST['part']);
			$start = intval($_POST['start']);
            $list  = getcache('backup_list');
			if(!is_array($list)) return_json(array('status'=>0, 'message'=>L('illegal_operation')));
				
            $databack = new databack($list[$part], array('path' => $this->config['path'],'compress' => $list[$part][2]));

            $start = $databack->import($start);

            if(false === $start){
                return_json(array('status'=>0, 'message'=>'还原数据出错！'));
            } elseif(0 === $start) { //下一卷
                if(isset($list[++$part])){
                    $data = array('part' => $part, 'start' => 0);
                    return_json(array('status'=>2, 'message'=>'正在还原：卷'.$part.'...', 'data'=>$data));
                } else {
                    delcache('backup_list');
                    return_json(array('status'=>1, 'message'=>'数据还原完成！'));
                }
            } else {
                $data = array('part' => $part, 'start' => $start[0]);
                if($start[1]){
                    $rate = round(100 * ($start[0] / $start[1]), 2);
					return_json(array('status'=>2, 'message'=>'正在还原：卷'.$part.'...('.$rate.'%)', 'data'=>$data));
                } else {
					return_json(array('status'=>2, 'message'=>'正在还原：卷'.$part.'...', 'data'=>$data));
                }
            }

        } else {
            return_json(array('status'=>0, 'message'=>L('lose_parameters')));
        }
	}


	private function _safe_replace($string) {
		if(!is_string($string)) return '';
		return str_replace(array('`',';','%','{','}',"\\",'&',' ',"'",'"','/','*','<','>',"\r","\t","\n",'#'), '', $string);
	}
}