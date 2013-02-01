<?php
/**
 * Vip阅读模块
 *
 * @author flashfxp
 */
class VipAction extends MainAction {
	public function index(){
		$chapter_id		= (int)$_GET['id'];
		$chapter_obj	= M('Book_chapter');
		$chapter		= $chapter_obj->find($chapter_id);
		if($chapter){
			$book_id	= $chapter['book_id'];
			$book		= book_search(array('book.book_id'=>$book_id), true);
			if(!$chapter['is_vip']){
				redirect(BU($book, 'read', $chapter));
			}
			$this->assign('book', $book);
			$this->assign('chapter', $chapter);

			// 显示购买页面
			if(!$this->check_buyed($chapter_id)){
				$vip_list = $this->get_vip_list($book_id, $chapter_id);
				$this->assign('list', $vip_list);
				$this->display(TEMPLATE_PATH.'/home/vip_buy.html');
				exit;
			}

			$volume_id	= $chapter['volume_id'];
			if($volume_id > 0){
				$volume	= $chapter_obj->find($volume_id);
				$this->assign('volume', $volume);
			}

			$page	= (int)$_GET['page'];	// 注意这里变量不能用p，在book_search中已被占用
			if($page < 1){ $page = 1; }
			$pages	= $this->split_chapter($chapter['chapter_detail']);
			$next	= $page < count($pages) ? VLink($chapter, $page + 1) : get_next_chapter($book, $chapter_id, 'next');
			$prev	= $page < 2 ? get_next_chapter($book, $chapter_id, 'prev') : VLink($chapter, $page - 1);
			$this->assign('next', $next);
			$this->assign('prev', $prev);
			$this->assign('page', $page);
			$this->display(TEMPLATE_PATH.'/home/vip.html');
		}else{
			$this->error('章节不存在！');
		}
	}

	// 获取vip图片章节
	public function image(){
		$id	= (int)$_GET['id'];
		$p	= (int)$_GET['page'] - 1;
		if($p < 0){ $p = 0; }
		$chapter	= M('Book_chapter')->find($id);
		if($chapter){
			$detail	= $chapter['chapter_detail'];
			$pages	= $this->split_chapter($detail);
			$this->str2png($pages[$p]);
			exit;
		}else{
			$this->error('章节不存在！');
		}
	}

	// 订购VIP章节
	public function order(){
		if(!Cookie::is_set('user_id')){
			$this->error('请先登录！', true);
		}else{
			$user_id = (int)Cookie::get('user_id');
		}
		$cost_obj	= M('Cost_history');
		$chap_obj	= M("Book_chapter");
		$data		= array('user_id'=>$user_id, 'time'=>time());
		$cost_total	= 0;
		$id_array	= explode(',', $_REQUEST['data']);
		foreach($id_array as $cid){
			$cid	= (int)$cid;
			if($cid < 1){ continue; }
			$chapter= $chap_obj->find($cid);
			if($chapter && $chapter['is_vip']){
				$data['book_id']	= $chapter['book_id'];
				$data['chapter_id']	= $cid;
				$data['cost']		= $chapter['sale_price'];
				if($cost_obj->add($data)){
					$cost_total += $chapter['sale_price'];
					$chap_obj->setInc('sale_num', 'chapter_id='.$cid);
				}
			}
		}
		if($cost_total > 0){
			M('User_info')->setDec('vip_money', 'id='.$user_id, $cost_total);
		}
		$this->success('购买成功！', true);
	}

	// 获取未订阅vip章节列表
	protected function get_vip_list($book_id, $chapter_id){
		$where	= array('book_id'=>$book_id, 'user_id'=>Cookie::get('user_id'));
		$buyed	= M('Cost_history')->where($where)->select();
		$result	= array();
		foreach($buyed as $item){
			$result[] = $item['chapter_id'];
		}
		
		$sql	= 'select * from __TABLE__ where `book_id`='.$book_id.' and `is_vip`=1 and `chapter_type`=0';
		count($result) > 0 && $sql .= ' and chapter_id not in ('.implode(',', $result).')';
		$list	= M('Book_chapter')->query($sql);
		
		return $list;
	}

	// 权限检测，是否已购买该章节
	protected function check_buyed($chapter_id){
		$cost_obj	= M('Cost_history');
		$where		= array('chapter_id'=>$chapter_id, 'user_id'=>Cookie::get('user_id'));
		$cost		= $cost_obj->where($where)->find();
		return $cost ? true : false;
	}

	// 文字输入为png图片
	protected function str2png($str){
		//header("Content-type: image/png");
		//$str=iconv("Gb2312", "UTF-8", $str);
		$width	= 760;
		$font_size = C('book.vip_font_size') > 12 ? C('book.vip_font_size') : 12;
		$line_weight = floor($width / 0.455 / $font_size);
		
		$str	= implode("\n", $this->str2array($str, $line_weight));
		$str	= str_replace("\n\n", "\n", $str);
		$line	= substr_count($str, "\n");

		$height	= ($line + 1) * $font_size * 5/3;
		
		$im		= @imagecreate($width,$height) or die("Cannot Initialize new GD image stream");
		//颜色设置
		$bg_color	= imagecolorallocate($im, 255, 245, 255); //背景颜色
		$text_color	= imagecolorallocate($im, 0, 0, 0); //文字颜色
		//画图
		imagefill($im, 0, 0, $bg_color);//填充背景

		$font_type = C('book.vip_font');
		ImageTTFText($im, $font_size, 0, 16, 32, $text_color, $font_type, $str);

		// 图片水印
		if(C('book.vip_watermark') && file_exists(C('book.vip_watermark_pic'))){
			$waterImage = C('book.vip_watermark_pic');
			$water_info = getimagesize($waterImage);
			$water_w = $water_info[0];	//取得水印图片的宽
			$water_h = $water_info[1];	//取得水印图片的高
			$water_im = imagecreatefromgif($waterImage);

			imagealphablending($im, true);	//设定图像的混色模式，透明
//			$bg_color = imagecolorat($water_im,0,0);
//			imagecolortransparent($water_im, $bg_color);
			
			$water_place = C('book.vip_watermark_place');
			if(strpos($water_place, ',') !== false){
				$places	= explode(',', $water_place);
				foreach($places as $place){
					if(strpos($place, '|') !== false){
						$a = explode('|', $place);
						imagecopymerge($im, $water_im, (int)$a[0], (int)$a[1], 0, 0, $water_w, $water_h, 40);
					}
				}
			}
			ImageDestroy($water_im);
		}
		
		imagepng($im);	//输出图像
		imagedestroy($im);	//清理
	}

	// 字符串转化为数组，根据字符串显示长度（在图片中自动换行）
	protected function str2array($str, $length){
		$str	= str_replace("贇", "云", $str);
		$str	= str_replace("\n", "贇", $str);
		preg_match_all("/./u", $str, $words);
		$len	= count($words[0]);
		$one	= '';
		$i		= 1;
		foreach($words[0] as $w){
			if($w == "贇"){
				$one != '' && $line[] = $one;
				$line[] = '';
				$one = '';
			}else{
				if(mb_strlen($one.$w) <= $length){
					$one .= $w;
				}else{
					$line[] = $one;
					$one = $w;
				}
			}
			$i == $len && $line[] = $one;
			$i++;
		}
		return $line;
	}

	// 智能分割章节内容
	protected function split_chapter($str){
		$ave	= 5000;
		$min	= 2000;
		$length	= mb_strlen($str);
		$total	= round($length/$ave);
		if(($length - $ave * ($total - 1)) < $min){
			$ave	= ceil($length/$total);
		}
		
		for($i = 0; $i < $total; $i++){
			if($i < $total - 1){
				$sa[$i]	= $this->utf8_substr($str, $ave);
				//$sa[$i]	= mb_strcut($str, 0, $ave, 'utf-8');
				$str	= substr($str, strlen($sa[$i]));
			}else{
				$str .= C('book.vip_extra_info');
				$sa[]	= $str;
			}
		}
		//dump($sa);
		return $sa;
	}

	// 截取utf8编码字符串（只考虑只包含1或3位的情况）
	protected function utf8_substr($str, $len){
		for($i = 0;$i < $len; $i++){
			$temp_str=substr($str, $i, 1);
			if(ord($temp_str) > 127){
				$new_str .= substr($str, $i, 3);
				$i += 2;
			}else{
				$new_str .= substr($str, $i, 1);
			}
		}
		return $new_str;
	}
}

?>
