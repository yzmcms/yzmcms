<?php
/**
 * page.class.php	 数据分页类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2019-11-15
 */

class page{
	
	private $url;         		//当前URL
	private $total_rows;  		//一共多少条数据
	private $list_rows;   		//每页显示记录数
	private $total_page;  		//总的分页数
	private $now_page; 	  		//当前页
	private $parameter;   		//分页跳转的参数
	private $url_rule;    		//URL规则
	private $page_prefix; 		//URL分页前缀,默认为list_

	
    /**
     * 构造函数
     * @param int $total_rows  一共多少条数据
     * @param int $list_rows   每页显示记录数
     * @param array $parameter  分页跳转的参数
     */
	public function __construct($total_rows, $list_rows = 10, $parameter = array()){
		$this->total_rows = intval($total_rows);	
		$this->list_rows = $list_rows ? intval($list_rows) : $this->_get_page_size();
		$this->total_page = ceil($this->total_rows/$this->list_rows); 
		$this->now_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$this->now_page = $this->now_page>0 ? $this->now_page : 1;
        $this->parameter  = empty($parameter) ? $_GET : $parameter;	
        $this->url_rule = defined('LIST_URL') && LIST_URL ? true : false;
        $this->page_prefix = defined('PAGE_PREFIX') ? PAGE_PREFIX : 'list_';
        $this->url = $this->geturl();		
	}

	
	/**
	 * 获得当前地址
	 */
	protected function geturl(){
        unset($this->parameter['m'],$this->parameter['c'],$this->parameter['a']);
		$this->parameter['page'] = 'PAGE';
		
		if($this->url_rule) return $this->_list_url();
		return U(ROUTE_A, $this->parameter);
	}
	
	
	/**
	 * 生成链接URL
	 */
    private function make_url($page){
    	// 兼容PHP5.2写法，已不推荐
    	// if($page == 1 && $this->url_rule && !strpos($this->url, '?')) return substr($this->url, 0, strpos($this->url, $this->page_prefix.'PAGE'));

    	if($page == 1 && $this->url_rule && !strpos($this->url, '?')) return strstr($this->url, $this->page_prefix.'PAGE', true);
        return str_replace('PAGE', $page, $this->url);
    }

	
	/**
	 * 总页数
	 */
	public function total(){
		return $this->total_page;
	}

	
	/**
	 * 获得当前页
	 */
	public function getpage(){
		return $this->now_page;
	}

	
	/**
	 * 获得首页
	 */
	public function gethome(){	
		return '<a href="'.$this->make_url(1).'" class="homepage">'.L('home_page').'</a>';
	}

	
	/**
	 * 获得尾页
	 */
	public function getend(){	
		return '<a href="'.$this->make_url($this->total_page).'" class="endpage">'.L('end_page').'</a>';
	}

	
	/**
	 * 获得上页
	 */
	public function getpre(){
		if($this->now_page<=1){
			return '<a href="'.$this->make_url(1).'" class="nopage">'.L('pre_page').'</a>';
		}
		return '<a href="'.$this->make_url($this->now_page-1).'" class="prepage">'.L('pre_page').'</a>';
	}

	
	/**
	 * 获得下页
	 */
	public function getnext(){
		if($this->now_page>=$this->total_page){
			return '<a href="'.$this->make_url($this->now_page).'" class="nopage">'.L('next_page').'</a>';	
		}
		return '<a href="'.$this->make_url($this->now_page+1).'" class="nextpage">'.L('next_page').'</a>';
	}
	
	
	/**
	 * 获取开始数列
	 */
	public function start_rows(){ 
		if($this->total_page && $this->now_page > $this->total_page) $this->now_page = $this->total_page;
		return ($this->now_page-1)*($this->list_rows);
	}
	

	/**
	 * 每页显示的条数
	 */
	public function list_rows(){
		return $this->list_rows;
	}	
	
	
	/**
	 * 供外部分页使用
	 */
	public function limit(){
		return $this->start_rows().','.$this->list_rows();
	}


	/**
	 * 设置每页展示条数
	 */
	public function page_size($sizes = array(10, 20, 30, 40, 50, 100)){
		if(!is_array($sizes)) return '';
		$string = '<select name="page_size" class="select" data-url="'.$this->url.'" onchange="yzm_page_size(this)">';
		foreach ($sizes as $val) {
			$select = $this->list_rows==$val ? 'selected' : '';
			$string .= '<option value="'.$val.'" '.$select.'>'.$val.L('article_page').'</option>';
		}
		$string .= '</select>';
		return $string;
	}
	
	
	/**
	 * 数字数字列表页---[1][2][3][4][5]
	 */
	public function getlist(){
		$str = '';
		if($this->total_page<=5){
			for($i=1; $i<=$this->total_page; $i++){
				$class = $this->now_page==$i ? ' curpage' : '';
				$str.='<a href="'.$this->make_url($i).'" class="listpage'.$class.'">'.$i.'</a>';
			}
		}else{	
			if($this->now_page <= 3){
				$p =5;
			}else{
				$p = ($this->now_page+2)>=$this->total_page ? $this->total_page : $this->now_page+2;
			} 
			for($i=$p-4; $i<=$p; $i++){
				$class = $this->now_page==$i ? ' curpage' : '';
				$str.='<a href="'.$this->make_url($i).'" class="listpage'.$class.'">'.$i.'</a>';
			}
		}
		return $str;
	}


	/**
	 * 跳转到指定页
	 */
	public function getjump(){
		return '<span class="jumpbox">'.L('jump_to').'<input type="text" name="page" placeholder="'.L('page_number').'" onkeypress="return yzm_page_jump(this)" class="input-text jumppage" data-url="'.$this->url.'">'.L('page').'</span>';
	}	
	
	
	/**
	 * 获取全部列表---首页上页[1][2][3][4][5]下页尾页
	 */
	public function getfull($show_jump = true){
		if($this->total_rows == 0) return '';
	    return $this->gethome().$this->getpre().$this->getlist().$this->getnext().$this->getend().($show_jump ? $this->getjump() : '');
	}


	/**
	 * 获取每页展示条数
	 */
	private function _get_page_size(){
		$page_size_c = intval(get_cookie('page_size'));
		$page_size = isset($_GET['page_size']) ? intval($_GET['page_size']) : $page_size_c;

		if($page_size>0 && $page_size!=$page_size_c) {
			set_cookie('page_size', $page_size);
		}
		return $page_size>0 ? $page_size : 10;
	}


	/**
	 * 获取前端列表分页URL
	 */
	private function _list_url(){
		
		// 如果为后台批量生成栏目
		if(defined('ADMIN_CREATE_HTML')){
			if(!defined('TOTAL_PAGE')) define('TOTAL_PAGE', $this->total_page);
			$catdir = getcache('update_html_catdir_'.$_SESSION['adminid']);
			return SITE_URL.$catdir.'/'.$this->page_prefix.'PAGE.html'; 
		}		

		$parameter = '';
		$request_url = trim(str_replace(array(C('url_html_suffix'),$this->page_prefix.$this->now_page), '', $_SERVER['REQUEST_URI']), '/');

		// 支持传入自定义参数  ?aa=1&bb=2
		$pos = strpos($request_url, '?');
		if($pos !== false){
			list($request_url, $parameter) = explode('?', $request_url);
			if($parameter){
				parse_str($parameter, $vars);  
				$parameter = '?'.http_build_query($vars);
			}
			$request_url = trim($request_url, '/');
		}

		if($request_url) $request_url .= '/';
		if(SITE_PATH == '/'){
			return SITE_URL.$request_url.$this->page_prefix.'PAGE'.C('url_html_suffix').$parameter; 
		}
		return SERVER_PORT.HTTP_HOST.'/'.$request_url.$this->page_prefix.'PAGE'.C('url_html_suffix').$parameter; 
	}

}