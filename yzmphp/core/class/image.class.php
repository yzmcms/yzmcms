<?php
/**
 * image.class.php	 图像处理类
 *
 * @author           袁志蒙  
 * @license          http://www.yzmcms.com
 * @lastmodify       2016-08-17
 */

class image {
	public $w_pct = 100;  //水印透明度 	0代表完全透明，100代表不透明
	public $w_pos = 9;    //水印位置 范围是0 - 9 ，0代表随机
	public $w_img = '';    //水印图片
	public $w_quality = 90;  //JPEG 水印质量 0-100之间的数字
	public $w_minwidth = 300;  //水印添加条件 width 300px
	public $w_minheight = 300;  //水印添加条件 height 300px
	public $thumb_enable;   //是否启用缩略图
	public $watermark_enable;  //是否启用水印
	public $interlace = 0;

	
	/**
	 * 构造函数
	 * @param	int	$thumb_enable	是否启用缩略图
	 * @param	int	$watermark_enable	是否启用水印
	 */	
    public function __construct($thumb_enable = 0 , $watermark_enable = 0) {
    	$this->thumb_enable = $thumb_enable;
		$this->watermark_enable = $watermark_enable;
		$this->w_pos = C('watermark_position');
		$this->w_img = YZMPHP_PATH.'common/data/water/'.C('watermark_name');
		$this->w_minwidth = get_config('watermark_minwidth');
		$this->w_minheight = get_config('watermark_minheight');
    }

	public function set($w_img, $w_pos, $w_minwidth = 300, $w_minheight = 300, $w_quality = 90, $w_pct = 100) {
		$this->w_img = $w_img;
		$this->w_pos = $w_pos;
		$this->w_minwidth = $w_minwidth;
		$this->w_minheight = $w_minheight;
		$this->w_quality = $w_quality;
		$this->w_pct = $w_pct;
	}

    public static function info($img) {
        $imageinfo = getimagesize($img);
        if($imageinfo === false) return false;
		$imagetype = strtolower(substr(image_type_to_extension($imageinfo[2]),1));
		$imagesize = filesize($img);
		$info = array(
				'width'=>$imageinfo[0],
				'height'=>$imageinfo[1],
				'type'=>$imagetype,
				'size'=>$imagesize,
				'mime'=>$imageinfo['mime']
				);
		return $info;
    }
    
    public function getpercent($srcwidth,$srcheight,$dstw,$dsth) {
    	if (empty($srcwidth) || empty($srcheight) || ($srcwidth <= $dstw && $srcheight <= $dsth)) $w = $srcwidth ;$h = $srcheight;
    	if ((empty($dstw) || $dstw == 0)  && $dsth > 0 && $srcheight > $dsth) {
			$h = $dsth;
			$w = round($dsth / $srcheight * $srcwidth);
		} elseif ((empty($dsth) || $dsth == 0) && $dstw > 0 && $srcwidth > $dstw) {
			$w = $dstw;
			$h = round($dstw / $srcwidth * $srcheight);
		} elseif ($dstw > 0 && $dsth > 0) {
			if (($srcwidth / $dstw) < ($srcheight / $dsth)) {
					$w = round($dsth / $srcheight * $srcwidth);
					$h = $dsth;
			} elseif (($srcwidth / $dstw) > ($srcheight / $dsth)) {
					$w = $dstw;
					$h = round($dstw / $srcwidth * $srcheight );
			} else {
				$h = $dsth;
				$w = $dstw;
			}
		}
		$array['w']  = $w;
		$array['h']  = $h;
		return $array;
    }
	
	/**
	 * 对指定的图像进行缩放
	 * @param	string	$image	    需要处理的图片名称[带物理路径]
	 * @param	string	$filename	处理后的图片名称[带物理路径]
	 * @param	int	  $maxwidth	    缩略图宽度
	 * @param	int	  $maxheight	缩略图高度
	 * @param	string	$suffix	    是新图片的前缀
	 * @param	int	   $autocut     是否自动裁剪 默认不裁剪，当高度或宽度有一个数值为0时，自动关闭
	 * @param	int	   $ftp         是否删除原图
	 * @return	mixed		成功返回缩放后图片的物理路径,失败返回false
	 */	
    public function thumb($image, $filename = '', $maxwidth = 200, $maxheight = 200, $suffix='', $autocut = 0, $ftp = 0) {
		if(!$this->thumb_enable || !$this->check($image)) return false;
        $info  = self::info($image);
        if($info === false) return false;
		$srcwidth  = $info['width'];
		$srcheight = $info['height'];
		$pathinfo = pathinfo($image);
		$type = $info['type'];
		if(!$type) $type =  $pathinfo['extension'];
		$type = strtolower($type);
		unset($info);

		$creat_arr = $this->getpercent($srcwidth,$srcheight,$maxwidth,$maxheight);
		$createwidth = $width = $creat_arr['w'];
		$createheight = $height = $creat_arr['h'];

		$psrc_x = $psrc_y = 0;
		if($autocut && $maxwidth > 0 && $maxheight > 0) {
			if($maxwidth/$maxheight<$srcwidth/$srcheight && $maxheight>=$height) {
				$width = $maxheight/$height*$width;
				$height = $maxheight;
			}elseif($maxwidth/$maxheight>$srcwidth/$srcheight && $maxwidth>=$width) {
				$height = $maxwidth/$width*$height;
				$width = $maxwidth;
			}
			$createwidth = $maxwidth;
			$createheight = $maxheight;
		}
		$createfun = 'imagecreatefrom'.($type=='jpg' ? 'jpeg' : $type);
		$srcimg = $createfun($image);
		if($type != 'gif' && function_exists('imagecreatetruecolor'))
			$thumbimg = imagecreatetruecolor($createwidth, $createheight); 
		else
			$thumbimg = imagecreate($width, $height); 

		if(function_exists('imagecopyresampled'))
			imagecopyresampled($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height, $srcwidth, $srcheight); 
		else
			imagecopyresized($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $width, $height,  $srcwidth, $srcheight); 

		if($type=='gif' || $type=='png') {
		    $thumbimg = imagecreatetruecolor($createwidth, $createheight); 

		    // 设置透明背景
		    $background_color = imagecolorallocatealpha($thumbimg, 255, 255, 255, 127);
		    imagefill($thumbimg, 0, 0, $background_color);

		    // 保存透明通道信息
		    imagesavealpha($thumbimg, true);

		    if (function_exists('imagecopyresampled')) {
		        imagecopyresampled($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $createwidth, $createheight, $srcwidth, $srcheight); 
		    } else {
		        imagecopyresized($thumbimg, $srcimg, 0, 0, $psrc_x, $psrc_y, $createwidth, $createheight,  $srcwidth, $srcheight); 
		    }
		}
		
		if($type=='jpg' || $type=='jpeg') imageinterlace($thumbimg, $this->interlace);
		$imagefun = 'image'.($type=='jpg' ? 'jpeg' : $type);
		if(empty($filename)) $filename  = substr($image, 0, strrpos($image, '.')).$suffix.'.'.$type;
		$imagefun($thumbimg, $filename);
		imagedestroy($thumbimg);
		imagedestroy($srcimg);
		if($ftp) {
			@unlink($image);
		}
		return $filename;
    }

	/**
     * 裁剪图像
	 * @param   string	$image	    需要处理的图片名称[带物理路径]
	 * @param	string	$filename	处理后的图片名称[带物理路径],为空则覆盖原图
     * @param   integer $w      裁剪区域宽度
     * @param   integer $h      裁剪区域高度
     * @param   integer $x      裁剪区域x坐标
     * @param   integer $y      裁剪区域y坐标
     */
    public function crop($image, $filename, $w, $h, $x = 0, $y = 0 ){
		if(!$this->check($image)) return false;
		$w = round($w);
		$h = round($h);
		$x = round($x);
		$y = round($y);
		$filename = $filename ? $filename : $image;
		$filepath = rtrim(dirname($filename), '/').'/';
		if(!is_dir($filepath) && !mkdir($filepath, 0755, true)) return false;
		$info  = self::info($image);
        if($info === false) return false;
		$pathinfo = pathinfo($image);
		$type = $info['type'];
		if(!$type) $type =  $pathinfo['extension'];
		$type = strtolower($type);
		unset($info);

        $createfun = 'imagecreatefrom'.($type=='jpg' ? 'jpeg' : $type);
		$srcimg = $createfun($image);
		if($type != 'gif' && function_exists('imagecreatetruecolor'))
			$thumbimg = imagecreatetruecolor($w, $h);
		else
			$thumbimg = imagecreate($w, $h); 
 
		imagecopyresampled($thumbimg, $srcimg, 0, 0, $x, $y, $w, $h, $w, $h);

		if($type=='jpg' || $type=='jpeg') imageinterlace($thumbimg, $this->interlace);
		$imagefun = 'image'.($type=='jpg' ? 'jpeg' : $type);
		if(empty($filename)) $filename  = substr($image, 0, strrpos($image, '.')).'.'.$type;
		$imagefun($thumbimg, $filename);
		imagedestroy($thumbimg);
		imagedestroy($srcimg);

		return self::info($filename);
    }
	
	/**
	 * 对指定的图像进行水印添加
	 * @param string $source    原图片路径
	 * @param string $target    生成水印图片途径，默认为空，覆盖原图
	 * @param int $w_pos        水印位置
	 * @param string $w_img     水印图片名称
	 * @param string $w_text    文字水印
	 * @param int $w_font       文字水印大小
	 * @param string $w_color   文字水印颜色
	 * @return	bool            成功返回true,失败返回false
	 */
	public function watermark($source, $target = '', $w_pos = '', $w_img = '', $w_text = 'yzmcms', $w_font = 8, $w_color = '#ff0000') {
		$w_pos = $w_pos ? $w_pos : $this->w_pos;
		$w_img = $w_img ? $w_img : $this->w_img;
		if(!$this->watermark_enable || !$this->check($source)) return false;
		if(!$target) $target = $source;
		$source_info = getimagesize($source);
		$source_w    = $source_info[0];
		$source_h    = $source_info[1];		
		if($source_w < $this->w_minwidth || $source_h < $this->w_minheight) return false;
		switch($source_info[2]) {
			case 1 :
				$source_img = imagecreatefromgif($source);
				break;
			case 2 :
				$source_img = imagecreatefromjpeg($source);
				break;
			case 3 :
				$source_img = imagecreatefrompng($source);
				imagealphablending($source_img, true);
    			imagesavealpha($source_img, true);
				break;
			default :
				return false;
		}
		if(!empty($w_img) && is_file($w_img)) {
			$ifwaterimage = 1;
			$water_info   = getimagesize($w_img);
			$width        = $water_info[0];
			$height       = $water_info[1];
			switch($water_info[2]) {
				case 1 :
					$water_img = imagecreatefromgif($w_img);
					break;
				case 2 :
					$water_img = imagecreatefromjpeg($w_img);
					break;
				case 3 :
					$water_img = imagecreatefrompng($w_img);
					break;
				default :
					return;
			}
		} else {		
			$ifwaterimage = 0;
			$temp = imagettfbbox(ceil($w_font*2.5), 0, YZMPHP_PATH.'common/data/font/elephant.ttf', $w_text);
			$width = $temp[2] - $temp[6];
			$height = $temp[3] - $temp[7];
			unset($temp);
		}
		switch($w_pos) {
			case 1:
				$wx = 5;
				$wy = 5;
				break;
			case 2:
				$wx = ($source_w - $width) / 2;
				$wy = 0;
				break;
			case 3:
				$wx = $source_w - $width;
				$wy = 0;
				break;
			case 4:
				$wx = 0;
				$wy = ($source_h - $height) / 2;
				break;
			case 5:
				$wx = ($source_w - $width) / 2;
				$wy = ($source_h - $height) / 2;
				break;
			case 6:
				$wx = $source_w - $width;
				$wy = ($source_h - $height) / 2;
				break;
			case 7:
				$wx = 0;
				$wy = $source_h - $height;
				break;
			case 8:
				$wx = ($source_w - $width) / 2;
				$wy = $source_h - $height;
				break;
			case 9:
				$wx = $source_w - $width;
				$wy = $source_h - $height;
				break;
			case 0:
				$wx = rand(0,($source_w - $width));
				$wy = rand(0,($source_h - $height));
				break;				
			default:
				$wx = rand(0,($source_w - $width));
				$wy = rand(0,($source_h - $height));
				break;
		}
		if($ifwaterimage) {
			if($water_info[2] == 3) {
				imagecopy($source_img, $water_img, $wx, $wy, 0, 0, $width, $height);
			} else {
				imagecopymerge($source_img, $water_img, $wx, $wy, 0, 0, $width, $height, $this->w_pct);
			}
		} else {
			if(!empty($w_color) && (strlen($w_color)==7)) {
				$r = hexdec(substr($w_color,1,2));
				$g = hexdec(substr($w_color,3,2));
				$b = hexdec(substr($w_color,5));
			} else {
				return;
			}
			imagestring($source_img,$w_font,$wx,$wy,$w_text,imagecolorallocate($source_img,$r,$g,$b));
		}
		
		switch($source_info[2]) {
			case 1 :
				imagegif($source_img, $target);
				break;
			case 2 :
				imagejpeg($source_img, $target, $this->w_quality);
				break;
			case 3 :
				imagepng($source_img, $target);
				break;
			default :
				return;
		}

		if(isset($water_info)) {
			unset($water_info);
		}
		if(isset($water_img)) {
			imagedestroy($water_img);
		}
		unset($source_info);
		imagedestroy($source_img);
		return true;
	}

	public function check($image) {
		return extension_loaded('gd') && preg_match("/\.(jpg|jpeg|gif|png)/i", $image, $m) && is_file($image) && function_exists('imagecreatefrom'.($m[1] == 'jpg' ? 'jpeg' : $m[1]));
	}
	
}