<?php
/**
 * 书籍连载模块参数设置
 *
 * @author flashfxp
 */
class SerialAction extends BaseAction {
	// 管理首页
	public function index(){
		$type	= empty($_GET['t']) ? 'base' : $_GET['t'];
		$this->assign('title', '基本参数设置');
		$this->assign('type', $type);
		$this->display('./Public/admin/config_book.html');
	}
	
	// 保存基本设置相关参数
	public function base(){
		$data = Array(
			'sort_depth'		=> (int)$_POST['sort_depth'],
			'chapter_in_db'		=> (int)$_POST['chapter_in_db'],
			'chapter_dir'		=> safe_str($_POST['chapter_dir']),
			'chapter_auto_check'=> (int)$_POST['chapter_auto_check'],
			'perpage_top'		=> (int)$_POST['perpage_top'],
			'perpage_book'		=> (int)$_POST['perpage_book'],
			'read_volume'		=> (int)$_POST['read_volume'],
			'read_full'			=> (int)$_POST['read_full'],
			'search_history'	=> (int)$_POST['search_history'],
			'has_channel'		=> (int)$_POST['has_channel'],
		);
		update_config($data, 'book');

		$this->assign('jumpUrl', '?s=admin/serial/index/t/base');
		$this->success('参数修改成功！');
	}

	// 静态化设置相关参数
	public function html(){
		$data = Array(
			'chapter_html'		=> (int)$_POST['chapter_html'],
			'html_auto'			=> safe_str($_POST['html_auto']),
			'html_ext'			=> safe_str($_POST['html_ext']),
		);
		$config	= Array(
			'url_index'			=> safe_str($_POST['url_index']),
			'url_menu'			=> safe_str($_POST['url_menu']),
			'url_read'			=> safe_str($_POST['url_read']),
			'url_full'			=> safe_str($_POST['url_full']),
			'url_down'			=> safe_str($_POST['url_down']),
			'url_top'			=> safe_str($_POST['url_top']),
			'url_lists'			=> safe_str($_POST['url_lists']),
			'url_show'			=> safe_str($_POST['url_show']),
			'url_vip'			=> safe_str($_POST['url_vip']),
		);
		update_config(array_merge($data, $config), 'book');

		$this->update_site($config, $data['chapter_html'], $data['html_ext']);	// 修改rewrite规则

		$this->assign('jumpUrl', '?s=admin/serial/index/t/html');
		$this->success('参数修改成功！');
	}

	// 保存积分设置相关参数
	public function credit(){
		$data = Array(
			'credit_txt'	=> (int)$_POST['credit_txt'],
			'credit_umd'	=> (int)$_POST['credit_umd'],
			'credit_epub'	=> (int)$_POST['credit_epub'],
			'credit_review'	=> (int)$_POST['credit_review'],
			'credit_review_good'	=> (int)$_POST['credit_review_good'],
		);
		update_config($data, 'book');

		$this->assign('jumpUrl', '?s=admin/serial/index/t/credit');
		$this->success('参数修改成功！');
	}

	// 保存VIP阅读设置相关参数
	public function vip(){
		$data = Array(
			'vip_font'				=> safe_str($_POST['vip_font']),
			'vip_font_size'			=> (int)$_POST['vip_font_size'],
			'vip_watermark'			=> (int)$_POST['vip_watermark'],
			'vip_watermark_pic'		=> $this->get_image(safe_str($_POST['vip_watermark_pic']), $_FILES['upload']),
			'vip_watermark_place'	=> safe_str($_POST['vip_watermark_place']),
			'vip_extra_info'		=> safe_str($_POST['vip_extra_info']),
			'vip_ticket_min_credit'	=> (int)$_POST['vip_ticket_min_credit'],
			'vip_ticket_per_credit'	=> (int)$_POST['vip_ticket_per_credit'],
			'vip_persize'			=> (int)$_POST['vip_persize'],
			'vip_perprice'			=> (int)$_POST['vip_perprice'],
			'vip_convert'			=> (int)$_POST['vip_convert']
		);
		update_config($data, 'book');

		$_POST['vip_update'] == 1 && update_vip_price();

		$this->assign('jumpUrl', '?s=admin/serial/index/t/vip');
		$this->success('参数修改成功！');
	}

	// 保存电子书设置相关参数
	public function ebook(){
		$data = Array(
			'ebook_txt_auto'	=> (int)$_POST['ebook_txt_auto'],
			'ebook_txt_type'	=> (int)$_POST['ebook_txt_type'],
			'ebook_txt_zip'		=> (int)$_POST['ebook_txt_zip'],
			'ebook_umd_auto'	=> (int)$_POST['ebook_umd_auto'],
			'ebook_epub_auto'	=> (int)$_POST['ebook_epub_auto'],
			'url_txt'			=> safe_str($_POST['url_txt']),
			'url_zip'			=> safe_str($_POST['url_zip']),
			'url_umd'			=> safe_str($_POST['url_umd']),
			'url_epub'			=> safe_str($_POST['url_epub']),
		);
		update_config($data, 'book');

		$this->assign('jumpUrl', '?s=admin/serial/index/t/ebook');
		$this->success('参数修改成功！');
	}

	// 保存评论设置相关参数
	public function review(){
		$data = Array(
			'review_open'	=> (int)$_POST['review_open'],
			'review_auto'	=> (int)$_POST['review_auto'],
			'reply_auto'	=> (int)$_POST['reply_auto'],
			'perpage_review'=> (int)$_POST['perpage_review'],
			'perpage_reply'	=> (int)$_POST['perpage_reply'],
		);
		update_config($data, 'book');

		$this->assign('jumpUrl', '?s=admin/serial/index/t/review');
		$this->success('参数修改成功！');
	}

	// 获取图片链接（可上传或下载到本地）
	protected function get_image($fname, $upfile){
		$fdir	= 'files/vip/';
		if(!empty($upfile['name'])){
			return upload_file($fdir, false, array('gif'));
		}else{
			return $fname;
		}
	}

	// 切换静态化方式
	protected function update_site($config, $type, $ext){
		$mode	= $type == 2 ? 2 : 3;
		$array	= Array(
			'URL_MODEL'			=> $mode,
			'HTML_FILE_SUFFIX'	=> $ext,
		);
		update_config_file($array, false);

		$filename	= '.htaccess';
		$contents	= file_get_contents($filename);
		// RewriteEngine 开关
		$search		= 'RewriteEngine '.($type == 2 ? 'Off' : 'On');
		if(stripos($contents, $search)){
			$replace	= 'RewriteEngine '.($type == 2 ? 'On' : 'Off');
			$contents	= str_replace($search, $replace, $contents);
		}
		// RewriteBase
		$base		= __ROOT__ == '' ? '/' : __ROOT__;
		$contents	= preg_replace("/RewriteBase .+\n/", 'RewriteBase '.$base."\n", $contents);

		// RewriteRule
		$contents	= preg_replace("/RewriteRule .*\n/", "", $contents);
		foreach($config as $key => $value){
			$rule  .= $this->get_rule($value, $key, $ext);
		}
		file_put_contents($filename, $contents.$rule);
	}
	
	// 获取伪静态规则定义
	protected function get_rule($key, $action, $ext){
		if(strpos($key, '/') == 0){
			$key = substr($key, 1);
		}
		$action  = substr($action, 4);
		
		if($action == 'lists' || $action == 'show'){
			preg_match_all("/{sid}|{page}|{sdir}/", $key, $matches);
			foreach($matches[0] as $k => $v){
				$v == "{sid}"  && empty($b1) && $b1 = '$'.++$k;
				$v == "{sdir}" && empty($b2) && $b2 = '$'.++$k;
				$v == "{page}" && empty($b3) && $b3 = '/p/$'.++$k;
			}
			$b	= empty($b1) ? $b2.$b3 : $b1.$b3;
		}else if($action == 'top'){
			$b	= '$1';
		}else if($action == 'vip'){
			preg_match_all("/{cid}|{page}/", $key, $matches);
			foreach($matches[0] as $k => $v){
				$v == "{cid}"  && empty($b1) && $b1 = '$'.++$k;
				$v == "{page}" && empty($b2) && $b2 = '/page/$'.++$k;
			}
			$b	= $b1.$b2;
		}else if($action == 'read'){
			preg_match_all("/{sid}|{sdir}|{bid}|{bdir}|{btime}|{byear}|{bmonth}|{bday}|{cid}|{ctime}/", $key, $matches);
			foreach($matches[0] as $k => $v){
				if($v == '{cid}'){ $b = '$'.++$k; break; }
			}
		}else{
			preg_match_all("/{sid}|{sdir}|{bid}|{bdir}|{btime}|{byear}|{bmonth}|{bday}/", $key, $matches);
			foreach($matches[0] as $k => $v){
				if($v == '{bid}'){ $b = '$'.++$k; break; }
			}
		}
		
		$search	= array('{sid}','{bid}','{tid}','{cid}','{page}','{btime}','{ctime}','{sdir}','{bdir}','{byear}','{bmonth}','{bday}');
		$replace= array('(\d+)','(\d+)','(\d+)','(\d+)','(\d+)','(\d{10,10})','(\d{10,10})','(.+?)','(.+?)','(\d{4,4})','(\d{2,2})','(\d{2,2})');
		$from = str_replace($search, $replace, $key.$ext);

		if($action == 'top'){
			$a  = 'top/index/id/';
		}else if($action == 'vip'){
			$a  = 'vip/index/id/';
		}else{
			$a  = 'book/'.$action.'/id/';
		}

		return 'RewriteRule ^'.$from.'$ index.php\?s='.$a.$b."\n";
	}
}
?>