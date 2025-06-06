<?php
/**
 * tree.class.php 通用的树型类，可以生成任何树型结构
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-10-17 
 */
 

class tree {
    
    /**
     * 生成树型结构所需要的二维数组
     * @var array
     */
    public $arr = array();

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    public $icon = array('│','├','└');
    public $nbsp = "&nbsp;";
    public $ret = '';
    public $str = '';

    /**
     * 构造函数，初始化类
     * @param array 二维数组，例如：
     * array(
     *      1 => array('id'=>'1','parentid'=>0,'name'=>'一级栏目一'),
     *      2 => array('id'=>'2','parentid'=>0,'name'=>'一级栏目二'),
     *      3 => array('id'=>'3','parentid'=>1,'name'=>'二级栏目一'),
     *      4 => array('id'=>'4','parentid'=>1,'name'=>'二级栏目二'),
     *      5 => array('id'=>'5','parentid'=>2,'name'=>'二级栏目三'),
     *      6 => array('id'=>'6','parentid'=>3,'name'=>'三级栏目一'),
     *      7 => array('id'=>'7','parentid'=>3,'name'=>'三级栏目二')
     *      )
     */
    public function init($arr=array()){
        $this->arr = $arr;
        $this->ret = '';
        return is_array($arr);
    }

    /**
     * 得到父级数组
     * @param int
     * @return array
     */
    public function get_parent($myid){
        $newarr = array();
        if(!isset($this->arr[$myid])) return false;
        $pid = $this->arr[$myid]['parentid'];
        $pid = $this->arr[$pid]['parentid'];
        if(is_array($this->arr)){
            foreach($this->arr as $id => $a){
                if($a['parentid'] == $pid) $newarr[$id] = $a;
            }
        }
        return $newarr;
    }

    /**
     * 得到子级数组
     * @param int
     * @return array
     */
    public function get_child($myid){
        $a = $newarr = array();
        if(is_array($this->arr)){
            foreach($this->arr as $id => $a){
                if($a['parentid'] == $myid) $newarr[$id] = $a;
            }
        }
        return $newarr ? $newarr : false;
    }

    /**
     * 得到当前位置数组
     * @param int
     * @return array
     */
    public function get_pos($myid,&$newarr){
        $a = array();
        if(!isset($this->arr[$myid])) return false;
        $newarr[] = $this->arr[$myid];
        $pid = $this->arr[$myid]['parentid'];
        if(isset($this->arr[$pid])){
            $this->get_pos($pid,$newarr);
        }
        if(is_array($newarr)){
            krsort($newarr);
            foreach($newarr as $v){
                $a[$v['id']] = $v;
            }
        }
        return $a;
    }

    /**
     * 得到树型结构
     * @param int $myid 表示获得这个ID下的所有子级
     * @param string $str 生成树型结构的基本代码，例如："<option value=\$id \$selected>\$spacer\$name</option>"
     * @param mixed $sid 被选中的ID，可以是单个ID或数组
     * @param string $adds 前缀
     * @param string $str_group 分组样式
     * @return string
     */
    public function get_tree($myid, $str, $sid = 0, $adds = '', $str_group = ''){
        $number=1;
        $child = $this->get_child($myid);
        if(is_array($child)){
            $total = count($child);
            foreach($child as $id=>$value){
                $j=$k='';
                if($number==$total){
                    $j .= $this->icon[2];
                }else{
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds.$j : '';
                
                $selected = '';
                if(is_array($sid)){
                    $selected = in_array($id, $sid) ? 'selected' : '';
                }else{
                    $selected = $id==$sid ? 'selected' : '';
                }
                
                if(!is_array($value)) return false;
                if(isset($value['str']) || isset($value['str_group'])) return false;
                @extract($value);
                $parentid == 0 && $str_group ? eval("\$nstr = \"$str_group\";") : eval("\$nstr = \"$str\";");
                $this->ret .= $nstr;
                $nbsp = $this->nbsp;
                $this->get_tree($id, $str, $sid, $adds.$k.$nbsp,$str_group);
                $number++;
            }
        }
        return $this->ret;
    }
    
    /**
     * 同上一方法类似,但允许多选
     * @param int $myid 要查询的ID
     * @param string $str 第一种HTML代码方式
     * @param string $str2 第二种HTML代码方式
     * @param mixed $sid 默认选中值，可以是单个ID或数组
     * @param string $adds 前缀
     * @return string
     */
    public function get_tree_multi($myid, $str, $str2, $sid = 0, $adds = ''){
        $number=1;
        $child = $this->get_child($myid);
        if(is_array($child)){
            $total = count($child);
            foreach($child as $id=>$a){
                $j=$k='';
                if($number==$total){
                    $j .= $this->icon[2];
                }else{
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds.$j : '';
                
                $selected = $this->have($sid,$id) ? 'selected' : '';
                if(!is_array($a) || isset($a['str'])) return false;
                @extract($a);
                if (empty($html_disabled)) {
                    eval("\$nstr = \"$str\";");
                } else {
                    eval("\$nstr = \"$str2\";");
                }
                $this->ret .= $nstr;
                $this->get_tree_multi($id, $str, $str2, $sid, $adds.$k.'&nbsp;');
                $number++;
            }
        }
        return $this->ret;
    }
    
    /**
     * @param integer $myid 要查询的ID
     * @param string $str 第一种HTML代码方式
     * @param string $str2 第二种HTML代码方式
     * @param mixed $sid 默认选中值，可以是单个ID或数组
     * @param integer $adds 前缀
     * @return string
     */
    public function get_tree_category($myid, $str, $str2, $sid = 0, $adds = ''){
        $number=1;
        $child = $this->get_child($myid);
        if(is_array($child)){
            $total = count($child);
            foreach($child as $id=>$a){
                $j=$k='';
                if($number==$total){
                    $j .= $this->icon[2];
                }else{
                    $j .= $this->icon[1];
                    $k = $adds ? $this->icon[0] : '';
                }
                $spacer = $adds ? $adds.$j : '';
                
                $selected = '';
                if(is_array($sid)){
                    $selected = in_array($id, $sid) ? 'selected' : '';
                }else{
                    $selected = $this->have($sid,$id) ? 'selected' : '';
                }
                
                if(!is_array($a) || isset($a['str']) || isset($a['str2'])) return false;
                @extract($a);
                if (empty($html_disabled)) {
                    eval("\$nstr = \"$str\";");
                } else {
                    eval("\$nstr = \"$str2\";");
                }
                $this->ret .= $nstr;
                $this->get_tree_category($id, $str, $str2, $sid, $adds.$k.'&nbsp;');
                $number++;
            }
        }
        return $this->ret;
    }
    
    /**
     * 同上一类方法，jquery treeview 风格，可伸缩样式（需要treeview插件支持）
     * @param $myid 表示获得这个ID下的所有子级
     * @param $effected_id 需要生成treeview目录数的id
     * @param $str 末级样式
     * @param $str2 目录级别样式
     * @param $showlevel 直接显示层级数，其余为异步显示，0为全部限制
     * @param $style 目录样式 默认 filetree 可增加其他样式如'filetree treeview-famfamfam'
     * @param $currentlevel 计算当前层级，递归使用 适用改函数时不需要用该参数
     * @param $recursion 递归使用 外部调用时为FALSE
     * @param $selectedIds 被选中的ID数组
     */
    function get_treeview($myid, $effected_id='example', $str="<span class='file'>\$name</span>", 
                        $str2="<span class='folder'>\$name</span>", $showlevel = 0, 
                        $style='filetree', $currentlevel = 1, $recursion=false, $selectedIds = array()) {
        $child = $this->get_child($myid);
        if(!defined('EFFECTED_INIT')){
            $effected = ' id="'.$effected_id.'"';
            define('EFFECTED_INIT', 1);
        } else {
            $effected = '';
        }
        $placeholder = '<ul><li><span class="placeholder"></span></li></ul>';
        if(!$recursion) $this->str .='<ul'.$effected.'  class="'.$style.'">';
        foreach($child as $id=>$a) {
            if(!is_array($a) || isset($a['str']) || isset($a['str2'])) return false;
            @extract($a);
            if($showlevel > 0 && $showlevel == $currentlevel && $this->get_child($id)) $folder = 'hasChildren';
            $floder_status = isset($folder) ? ' class="'.$folder.'"' : '';
            
            $selected = in_array($id, $selectedIds) ? ' selected' : '';
            $this->str .= $recursion ? '<ul><li'.$floder_status.$selected.' id=\''.$id.'\'>' : '<li'.$floder_status.$selected.' id=\''.$id.'\'>';
            
            $recursion = FALSE;
            if($this->get_child($id)){
                eval("\$nstr = \"$str2\";");
                $this->str .= $nstr;
                if($showlevel == 0 || ($showlevel > 0 && $showlevel > $currentlevel)) {
                    $this->get_treeview($id, $effected_id, $str, $str2, $showlevel, $style, $currentlevel+1, TRUE, $selectedIds);
                } elseif($showlevel > 0 && $showlevel == $currentlevel) {
                    $this->str .= $placeholder;
                }
            } else {
                eval("\$nstr = \"$str\";");
                $this->str .= $nstr;
            }
            $this->str .=$recursion ? '</li></ul>': '</li>';
        }
        if(!$recursion) $this->str .='</ul>';
        return $this->str;
    }
    
    /**
     * 获取子栏目json
     * @param int $myid 父级ID
     * @param string $str 自定义格式
     * @return string JSON格式数据
     */
    public function creat_sub_json($myid, $str='') {
        $sub_cats = $this->get_child($myid);
        $n = 0;
        if(is_array($sub_cats)) foreach($sub_cats as $c) {            
            $data[$n]['id'] = iconv('utf-8','utf-8',$c['catid']);
            if($this->get_child($c['catid'])) {
                $data[$n]['liclass'] = 'hasChildren';
                $data[$n]['children'] = array(array('text'=>'&nbsp;','classes'=>'placeholder'));
                $data[$n]['classes'] = 'folder';
                $data[$n]['text'] = iconv('utf-8','utf-8',$c['catname']);
            } else {                
                if($str) {
                    @extract(array_iconv($c,'utf-8','utf-8'));
                    eval("\$data[$n]['text'] = \"$str\";");
                } else {
                    $data[$n]['text'] = iconv('utf-8','utf-8',$c['catname']);
                }
            }
            $n++;
        }
        return json_encode($data);        
    }
    
    /**
     * 检查是否选中
     * @param mixed $list 可以是数组或逗号分隔的字符串
     * @param mixed $item 要检查的项目
     * @return bool
     */
    private function have($list, $item){
        if(is_array($list)){
            return in_array($item, $list);
        }
        return (strpos(',,'.$list.',', ','.$item.',') !== false);
    }
}