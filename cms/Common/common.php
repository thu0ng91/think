<?php
/**
 +------------------------------------------------------------------------------
 * 书籍搜索
 * where_arr	搜索条件
 * is_one		是否单本搜索
 * per_page		每页显示书籍数
 +------------------------------------------------------------------------------
 */
function book_search($where, $is_one=false, $per_page=10, $order='', $all=false){
	import("ORG.Util.Page");			// 导入分页类
	$book_obj		= M('Book');
	$page_no		= empty($_GET['p']) ? "1" : (int)$_GET['p'];
	if($all == false){
		$where['book.if_check']		= 1;
		$where['book.if_display']	= 1;
	}
	$order == '' && $order = 'book.book_id desc';
	$result['list']	= $book_obj	->field('book.*, book_sort.sort_name, book_sort.sort_dir, book_sort.path')
								->join(' book_sort on book.sort_id=book_sort.sort_id')
								->where($where)->order($order)
								->page($page_no.','.$per_page)->select();
	if($is_one){
		return $result['list'][0];
	}else{
		$total			= $book_obj->where($where)->count();// 查询满足要求的总记录数
		$page_obj		= new Page($total, $per_page);		// 实例化分页类传入总记录数和每页显示的记录数
		$page_obj->setConfig('theme', '共 %totalRow% 本书 %nowPage%/%totalPage% 页  %upPage%  %linkPage%  %downPage%');
		$result['page']	= $page_obj->show();				// 分页显示输出
		return $result;
	}
}

/**
 +------------------------------------------------------------------------------
 * 评论搜索，review，reply，book 三表联合
 +------------------------------------------------------------------------------
 */
function review_search($where_arr, $page_no=1){
	import("ORG.Util.Page");			// 导入分页类
	$review_obj		= M('Book_review');
	$perpage		= C('book.perpage_review');
	$result['list']	= $review_obj
						->field('book_review.*, book.book_name, book_reply.*')
						->join(' book on book.book_id=book_review.book_id')
						->join(' book_reply on book_reply.review_id=book_review.review_id and book_reply.is_topic=1')
						->where($where_arr)->order('book_review.review_id desc')
						->page($page_no.','.$perpage)
						->select();

	$total			= $review_obj
						->field('book_review.*, book.book_name, book_reply.*')
						->join(' book on book.book_id=book_review.book_id')
						->join(' book_reply on book_reply.review_id=book_review.review_id and book_reply.is_topic=1')
						->where($where_arr)->count();
	$page_obj		= new Page($total, $perpage);
	$result['page']	= $page_obj->show();
	$result['total']= $total;
	$result['pages']= ceil($total/$perpage);
	return $result;
}

/**
 +------------------------------------------------------------------------------
 * 生成默认配置参数文件
 +------------------------------------------------------------------------------
 */
function create_default_config(){
	$config_obj	= M('Config');
	$list		= $config_obj->field('type, name, value')->select();
	$array		= Array();
	foreach($list as $item){
		$type	= $item['type'];
		$name	= $item['name'];
		$array[$type][$name] = $item['value'];
	}
	update_config_file($array);
}

/**
 +------------------------------------------------------------------------------
 * 保存设置参数
 +------------------------------------------------------------------------------
 */
function update_config($array, $mod = "book"){
	$config_obj	= M('Config');
	$data_arr	= Array();
	$conf_arr	= Array();
	foreach($array as $key => $val){
		if($mod == ""){
			$conf_arr[$key]	= $val;
		}else{
			$conf_arr[$mod][$key]	= $val;
		}
		$data['value']		= $val;
		$config_obj->where(array('type'=>$mod, 'name'=>$key))->save($data);
	}
	
	$split = $mod == '' ? false : true;
	update_config_file($conf_arr, $split);
}
 
/**
 +------------------------------------------------------------------------------
 * 更新配置参数文件
 * split: $array 是否是参数配置中一个完整模块的分割子模块
 +------------------------------------------------------------------------------
 */
function update_config_file($array, $split=true){
	$filename	= APP_PATH.'/Conf/config_var.php';
	$config_arr	= require $filename;

	foreach($array as $key=>$value){
		if(is_array($value)){
			if(!$split){ unset($config_arr[$key]); }
			foreach($value as $k=>$v){
				$config_arr[$key][$k] = $v;
			}
		}else{
			$config_arr[$key] = $value;
		}
	}

	array_to_file($filename, $config_arr);
	@unlink(APP_PATH.'/Runtime/~app.php');
}

/**
 +------------------------------------------------------------------------------
 * 保存文件
 +------------------------------------------------------------------------------
 */
function write_to_file($filename, $contents=''){
	$dir = dirname($filename);
	if(!is_dir($dir)){
		mk_dir($dir);	// 框架自带函数
	}
	return @file_put_contents($filename, $contents);
}

/**
 +------------------------------------------------------------------------------
 * （参数）数组保存到（配置）文件
 +------------------------------------------------------------------------------
 */
function array_to_file($filename, $array=''){
	if(is_array($array)){
		$contents = var_export($array,true);
	} else{
		$contents = $array;
	}
	$contents = "<?php\nreturn $contents;\n?>";
	write_to_file($filename, $contents);
}

/**
 +------------------------------------------------------------------------------
 * 获取客户端IP地址
 +------------------------------------------------------------------------------
 */
function get_client_ip(){
   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")){
       $ip = getenv("HTTP_CLIENT_IP");
   }else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")){
       $ip = getenv("HTTP_X_FORWARDED_FOR");
   }else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")){
       $ip = getenv("REMOTE_ADDR");
   }else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
       $ip = $_SERVER['REMOTE_ADDR'];
   }else{
       $ip = "unknown";
   }
   return $ip;
}

/**
 +------------------------------------------------------------------------------
 * 字符串输入安全过滤函数
 +------------------------------------------------------------------------------
 */
function safe_str($str, $for_html=false){
	if(!get_magic_quotes_gpc()){
		$str = addslashes($str);
	}
	!$for_html && $str = htmlspecialchars($str);
	
	return $str;
}

/**
 +------------------------------------------------------------------------------
 * 计算中英文混合字符串的长度（不包括标点符号等特殊字符）
 +------------------------------------------------------------------------------
 */
function count_words_num($str){
	//$str = str_replace(array("~","!","@","#","$","%","^","&","*",",",".","?",";",":","'",'"',"[","]","{","}","！","￥","……","…","、","，","。","？","；","：","‘","“","”","’","【","】","～","！","※","＠","＃","＄","％","＾","＆","＊","，","．","＜","＞","；","：","＇","＂","［","］","｛","｝","／","＼"),'',$str);
	$str = str_replace(array("\n"," ","\t"),'',$str);
	return mb_strlen($str, 'utf-8');
}

/**
 +------------------------------------------------------------------------------
 * 每天、每周、每月第一天清理周期统计数据并保留已清理标记
 +------------------------------------------------------------------------------
 */
function check_period_amount(){
	$book_obj		= M('Book');
	$config_flag	= 'period_flag';
	$now_key		= date('n|w|j');
	if(C($config_flag)){
		list($s_m,$s_w,$s_d)	= explode('|',C($config_flag));
		list($n_m,$n_w,$n_d)	= explode('|',$now_key);
		$flag		= false;
		if($s_m != $n_m){	//非本月
			$flag	= true;
			$book_obj->execute('update `book` set `month_visit`=0,`month_vote`=0');
		}
		if($s_w != $n_w){	//非本周
			$flag	= true;
			$book_obj->execute('update `book` set `week_visit`=0,`week_vote`=0');
		}
		if($s_d != $n_d){	//非本日
			$flag	= true;
			$book_obj->execute('update `book` set `day_visit`=0,`day_vote`=0');
		}
		if($flag){
			update_config_file(array($config_flag=>$now_key), false);
		}
	}else{
		update_config_file(array($config_flag=>$now_key), false);
	}
}

// 获取上一页、下一页章节url
function get_next_chapter($book, $chapter_id, $type='next') {
	$str = 'book_id=' . $book['book_id'] . ' and `chapter_type`=0 and `chapter_id`';
	$str .= $type == 'next' ? '>' : '<';
	$str .= $chapter_id;
	$order = $type == 'next' ? 'chapter_id asc' : 'chapter_id desc';
	$chapter_obj = M('Book_chapter');
	$chapter = $chapter_obj->where($str)->order($order)->find();
	if($chapter){
		return BU($book, 'read', $chapter);
	}else{
		return '';
	}
}

// 把章节文本段落规范化
function pure_txt($str){
	$str = trim($str);
	$str = preg_replace("/[\s　]*\n+[\s　]*/", "\n\n　　", $str);
	$str = preg_replace("/^[\s　]*/", "　　", $str);
	return $str;
}

/**
 +------------------------------------------------------------------------------
 * 权限数位转换为整数
 * authority => int
 +------------------------------------------------------------------------------
 * @param array[] $authority
 */
//function auth_bite_to_int($auth){
//    $len = count($auth);
//    for($i = 0; $i < $len; $i++){
//        $int += $auth[$i]*(2^$i);
//    }
//    return $int;
//}

/**
 +------------------------------------------------------------------------------
 * 权限整数转换为权限数位
 * int => authority
 +------------------------------------------------------------------------------
 * @param int $int
 */
//function auth_int_to_bite($int){
//    return $auth;
//}

/**
 +------------------------------------------------------------------------------
 * 生产指定长度的随机数
 +------------------------------------------------------------------------------
 * @param int $len 指定生产的长度，若为空则为8位
 */
function randomkeys($len = 8){

	$output='';
	for ($i = 0; $i < $len; $i++) {
		$output .= chr(mt_rand(33, 126));    //生成php随机数
	}
	return $output;
}

/*
 * 只针对前台（Home分组）
 *  action == top 时， $book 为 tid
 */
function BU($book, $action, $extra=''){
	if($action == 'read' && $extra == ''){
		if($book['last_chapterid'] == 0){
			$action	= 'index';
		}else{
			$chapter_obj= M('Book_chapter');
			$extra		= $chapter_obj->find($book['last_chapterid']);
		}
	}
	if($action == 'read' && is_array($extra) && $extra['is_vip'] == 1){
		return VLink($extra, 1);
	}
	if(C('book.chapter_html') == 3){	// 纯动态
		if($action != 'top'){
			$str	= 'home-book/'.$action.'/id/';
			if($action == 'lists' || $action == 'show'){
				$str .= $book['sort_id'];
			}else if($action == 'read'){
				$str .= $extra['chapter_id'];
			}else{
				$str .= $book['book_id'];
			}
		}else{
			$str	= 'home-top/index/id/'.$book;
		}
		$url = U($str);
	}else{
		if($action == 'lists' && !C('book.has_channel')){
			$action = 'show';
		}
		$url	= get_filename($book, $action, $extra, true);
	}
	return $url;
}

// 获取文件路径（或url）
function get_filename($book, $action, $extra='', $is_url=false){
	$style		= C('book.url_'.$action);
	if($action == 'top'){
		$search	= array('{tid}');
		$replace= array($book);
	}else if($action == 'show' || $action == 'lists'){
		$search	= array('{sid}', '{sdir}', '{page}');
		$extra < 1 && $extra = 1;
		$replace= array($book['sort_id'], $book['sort_dir'], $extra);
	}else{
		$btime	= $book['post_time'];
		list($byear, $bmonth, $bday) = explode('-', date('Y-m-d', $btime));
		$search	= array('{sid}', '{sdir}', '{bid}', '{bdir}', '{btime}', '{byear}', '{bmonth}', '{bday}');
		$replace= array($book['sort_id'], $book['sort_dir'], $book['book_id'], floor($book['book_id']/1000), $btime, $byear, $bmonth, $bday);
		if($action == 'read'){
			array_push($search, '{cid}', '{ctime}');
			array_push($replace, $extra['chapter_id'], $extra['post_time']);
		}
	}
	$fname	= $is_url ? __ROOT__ : APP_PATH;
	$fname .= str_replace($search, $replace, $style);

	if(!in_array($action, array('txt', 'zip', 'umd', 'epub'))){
		$fname .= C('book.html_ext');
	}
	
	return $fname;
}


function zeroFill($i, $size=2){
	do{
		$i = (strlen($i) < $size) ? '0'.$i : $i;
	}while(strlen($i) < $size);

	return $i;
}

/**
 +------------------------------------------------------------------------------
 * 将组权限兑换成 menu.js
 +------------------------------------------------------------------------------
 * @param int $group_id
 * @param string $group_auth
 */
function write_group_menu_js($group_id, $group_auth){

    $js_file = './PUBLIC/js/user/'.$group_id.'.js';
    $js_contents = 'var menu = {';
    $auth_array = explode(',', $group_auth);

    // config 目录的生成
    $auth = str_split($auth_array[0]);
    $config = write_config_to_js($auth);

    if( !empty($config) ){
        $js_contents .= $config[0].$config[1];
    }    

    // book 目录的生成
    $auth = str_split($auth_array[1]);
    $book = write_book_to_js($auth);
    if( empty($config) && !empty($book)){
        $js_contents .= '"config"'.$book[1];
    }elseif(!empty($book)){
        $js_contents .= ','.$book[0].$book[1];
    }

    // user 目录的生成
    $auth = str_split($auth_array[2]);
    $user = write_user_to_js($auth);
    if( empty($config)  &&  empty($book) && !empty($user) ){
        $js_contents .= '"config"'.$user[1];
    }elseif( !empty($user) ){
        $js_contents .= ','.$user[0].$user[1];
    }

    // collector 目录的生成
    $auth = str_split($auth_array[3]);
    $collector = write_collector_to_js($auth);
    if( empty($config) && empty($book) && empty($user) && !empty($collector) ){
        $js_contents .= '"config"'.$collector[1];
    }elseif( !empty($collector) ){
        $js_contents .= ','.$collector[0].$collector[1];
    }

    // paylist 目录的生成
    $auth = str_split($auth_array[4]);
    $paylist = write_paylist_to_js($auth);
    if( empty($config) && empty($book) && empty($user) && empty($collector) && !empty($paylist) ){
        $js_contents .= '"config"'.$paylist[1];
    }elseif( !empty($paylist) ){
        $js_contents .= ','.$paylist[0].$paylist[1];
    }


    // themes 目录的生成
    $auth = str_split($auth_array[5]);
    $themes = write_themes_to_js($auth);
    if( empty($config)  &&  empty($book) && empty($user) && !empty($themes) ){
        $js_contents .= '"config"'.$themes[1];
    }elseif( !empty($themes) ){
        $js_contents .= ','.$themes[0].$themes[1];
    }

    // data 目录的生成
    $auth = str_split($auth_array[6]);
    $database = write_database_to_js($auth);
    if( empty($config)  &&  empty($book) && empty($user) && empty($themes) && empty($collector) && !empty($database) ){
        $js_contents .= '"config"'.$database[1];
    }elseif( !empty($database) ){
        $js_contents .= ','.$database[0].$database[1];
    }

    $js_contents .= '};';
    write_to_file($js_file, $js_contents);
}

function write_config_to_js($auth){
    if($auth[0] != '1') return '';

    $name = '"config"';
    $contents = ':{"text":"系统管理","subtext":"系统管理","default":"config","children":{';
    
    if($auth[1] == '1') $contents .= '"config":{"text":"系统设置","url":"?s=admin/config"}';
    
    if($auth[1] == '1' && $auth[2] == '1') $contents .= ',';
    if($auth[2] == '1') $contents .= '"adminlist":{"text":"管理员列表","url":"?s=admin/admin/admin_list"}';
    
    if(($auth[1] == '1' || $auth[2] == '1') && $auth[3] == '1') $contents .= ',';
    if($auth[3] == '1') $contents .= '"admingroup":{"text":"管理员分组","url":"?s=admin/admingroup/group_list"}';
    
    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1') && $auth[4] == '1') $contents .= ',';
    if($auth[4] == '1') $contents .= '"links":{"text":"友情链接","url":"?s=admin/links"}';
    
    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1' || $auth[4] == '1') && $auth[5] == '1') $contents .= ',';
    if($auth[5] == '1') $contents .= '"ads":{"text":"广告管理","url":"?s=admin/ads"}';
    
    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1' || $auth[4] == '1' || $auth[5] == '1') && $auth[6] == '1') $contents .= ',';
    if($auth[6] == '1') $contents .= '"msg":{"text":"管理员信息","url":"?s=admin/msg"}';
    
    $contents .= '}}';
    return array($name, $contents);
}

function write_book_to_js($auth){
    if($auth[0] != '1') return '';

    $name = '"book"';
    $contents = ':{"text":"书籍管理","subtext":"书籍管理","default":"booklist","children":{';

    if($auth[1] == '1') $contents .= '"base_setting":{"text":"参数设置","url":"?s=admin/serial"}';

    if($auth[1] == '1' && $auth[2] == '1') $contents .= ',';
    if($auth[2] == '1') $contents .= '"booklist":{"text":"书籍管理","url":"?s=admin/book"}';
    
    if(($auth[1] == '1' || $auth[2] == '1') && $auth[3] == '1') $contents .= ',';
    if($auth[3] == '1') $contents .= '"booksort":{"text":"书籍分类","url":"?s=admin/booksort"}';

    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1') && $auth[4] == '1') $contents .= ',';
    if($auth[4] == '1') $contents .= '"recommend":{"text":"书籍推荐","url":"?s=admin/recommend"}';

    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1' || $auth[4] == '1') && $auth[5] == '1') $contents .= ',';
    if($auth[5] == '1') $contents .= '"review":{"text":"书评管理","url":"?s=admin/review"}';

    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1' || $auth[4] == '1' || $auth[5] == '1') && $auth[6] == '1') $contents .= ',';
    if($auth[6] == '1') $contents .= '"vote":{"text":"投票管理","url":"?s=admin/vote"}';

    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1' || $auth[4] == '1' || $auth[5] == '1' || $auth[6] == '1') && $auth[7] == '1') $contents .= ',';
    if($auth[7] == '1') $contents .= '"work":{"text":"文集管理","url":"?s=admin/work"}';

    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1' || $auth[4] == '1' || $auth[5] == '1' || $auth[6] == '1' || $auth[7] == '1') && $auth[8] == '1') $contents .= ',';
    if($auth[8] == '1') $contents .= '"ebook":{"text":"电子书批量生成","url":"?s=admin/ebook"}';

    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1' || $auth[4] == '1' || $auth[5] == '1' || $auth[6] == '1' || $auth[7] == '1' || $auth[8] == '1') && $auth[9] == '1') $contents .= ',';
    if($auth[9] == '1') $contents .= '"html":{"text":"静态页面生成","url":"?s=admin/html"}';

    if(($auth[1] == '1' || $auth[2] == '1' || $auth[3] == '1' || $auth[4] == '1' || $auth[5] == '1' || $auth[6] == '1' || $auth[7] == '1' || $auth[8] == '1' || $auth[9] == '1' ) && $auth[10] == '1') $contents .= ',';
    if($auth[10] == '1') $contents .= '"search":{"text":"搜索记录管理","url":"?s=admin/search"}';

    $contents .= '}}';
    return array($name, $contents);
}

function write_user_to_js($auth){
    if($auth[0] != '1') return '';

    $name = '"user"';
    $contents = ':{"text":"用户管理","subtext":"用户管理","default":"userlist","children":{';

    if($auth[1] == '1') $contents .= '"userlist":{"text":"用户列表","url":"?s=admin/user/user_list"}';
    if($auth[1] == '1' && $auth[2] == '1') $contents .= ',';
    if($auth[2] == '1') $contents .= '"usergroup":{"text":"用户组列表","url":"?s=admin/usergroup/group_list"}';
    /*if($auth[3] == '1') $contents .= ',"favor":{"text":"用户收藏夹","url":"?s=user/favor"}';
    if($auth[4] == '1') $contents .= ',"work":{"text":"用户个人文集","url":"?s=user/work"}';
    if($auth[5] == '1') $contents .= ',"userinfo":{"text":"用户个人信息(前台)","url":"?s=user/user"}';
    if($auth[6] == '1') $contents .= ',"userinfo-admin":{"text":"用户个人信息","url":"?s=admin/user/info"}';*/

    $contents .= '}}';
    return array($name, $contents);
}

function write_collector_to_js($auth){
    if($auth[0] != '1') return '';

    $name = '"collector"';
    $contents = ':{"text":"采集管理","subtext":"采集管理","default":"listcollector","children":{';

    if($auth[1] == '1') $contents .= '"listcollector":{"text":"采集器管理","url":"?s=admin/collector/listcollector"}';
    if($auth[1] == '1' && $auth[2] == '1') $contents .= ',';
    if($auth[2] == '1') $contents .= '"tempManage":{"text":"临时库管理","url":"?s=admin/temp/tempmanage"}';
    if(($auth[1] == '1' || $auth[2] == '1') && $auth[3] == '1') $contents .= ',';
    if($auth[3] == '1') $contents .= '"listresource":{"text":"资源库管理","url":"?s=admin/resource/listresource"}';

    $contents .= '}}';
    return array($name, $contents);
}

function write_paylist_to_js($auth){
    if($auth[0] != '1') return '';

    $name = '"order"';
    $contents = ':{"text":"订单管理","subtext":"订单管理","default":"list","children":{';

    if($auth[1] == '1') $contents .= '"list":{"text":"订单列表","url":"?s=admin/order"}';
    if($auth[1] == '1' && $auth[2] == '1') $contents .= ',';
    if($auth[2] == '1') $contents .= '"payment":{"text":"支付方式","url":"?s=admin/payment"}';

    $contents .= '}}';
    return array($name, $contents);
}

function write_themes_to_js($auth){
    if($auth[0] != '1') return '';

    $name = '"themes"';
    $contents = ':{"text":"风格模板","subtext":"风格模板","default":"style","children":{';

    if($auth[1] == '1') $contents .= '"style":{"text":"风格管理","url":"?s=admin/style"}';
    //if($auth[2] == '1') $contents .= ',"templets":{"text":"编辑模板","url":"?s=admin/tpl"}';

    $contents .= '}}';
    return array($name, $contents);
}

function write_database_to_js($auth){
    if($auth[0] != '1') return '';

    $name = '"database"';
    $contents = ':{"text":"数据维护","subtext":"数据维护","default":"backup","children":{';

    if($auth[1] == '1') $contents .= '"backup":{"text":"数据库备份","url":"?s=admin/backup"}';
    if($auth[1] == '1' && $auth[2] == '1') $contents .= ',';
    if($auth[2] == '1') $contents .= '"restore":{"text":"数据库还原","url":"?s=admin/restore"}';

    $contents .= '}}';
    return array($name, $contents);
}

// 字符串截取，支持中文和其他编码
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(function_exists("mb_substr"))
        return mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}

// 删除目录（含子目录、文件）
function rrmdir($str){
	if(is_file($str)){
		return @unlink($str);
	}
	elseif(is_dir($str)){
		$scan = glob(rtrim($str,'/').'/*');
		foreach($scan as $index=>$path){
			rrmdir($path);
		}
		return @rmdir($str);
	}
}

// 模板调用中获取书籍列表
function book_list($str){
	$str	= str_replace(' ', '', $str);
	$result = S($str);
	if($result && is_array($result)){
		return $result;
	}else{
		list($tid, $limit, $sid) = explode(',', $str);
		$limit	= intval($limit);
		$sid	= intval($sid);
		import("@.Action.Home.TopAction");
		$top	= new TopAction();
		$list	= $top->lists($tid, $limit, $sid);
		S($str, $list, 120);
		return $list;
	}
}

/**
 +------------------------------------------------------------------------------
 * 生成验证码
 +------------------------------------------------------------------------------
 */
function verify(){
    //首先校验验证码
    import('ORG.Util.Image');
    //import("ORG.Util.String");
    Image::buildImageVerify();
}

// 文件上传，$fdir 以 / 结尾，开头无 /
function upload_file($fdir, $is_url = true, $exts = array('jpg', 'gif', 'png', 'jpeg')){
	import("ORG.Net.UploadFile");
	$upload = new UploadFile(); // 实例化上传类
	$upload->maxSize	= 1048576 ; // 设置附件上传大小，这里设为 1M
	$upload->allowExts	= $exts; // 设置附件上传类型
	$upload->savePath	= $fdir; // 设置附件上传目录
	$upload->saveRule	= uniqid(date('Ymd').'_'); // 设置新文件名（不含后缀）

	if(!$upload->upload()) { // 上传错误提示错误信息
		die($upload->getErrorMsg());
	}else{ // 上传成功获取上传文件信息
		$info	= $upload->getUploadFileInfo();
		$fname  = $is_url ? __ROOT__.'/' : '';
		$fname .= $fdir.$info[0]["savename"];
		return $fname; 
	}
}

// 下载文件，$fdir 以 / 结尾，开头无 /
function download_file($url, $fdir){
	if(strpos($url, 'http://') !== 0){
		return $url;
	}
	vendor('Snoopy');
	$snoopy		= new Snoopy();
	$snoopy->fetch($url) ;

	if($snoopy->results != ''){
		$ext	= strrchr($url, '.');
		$fname	= $fdir.uniqid(date('Ymd').'_').$ext;
		write_to_file($fname, $snoopy->results);
		return __ROOT__.'/'.$fname;
	}else{
		return $url;
	}
}

function msg_unread_count(){
    $db = M('User_msg');
    $id = Cookie::get('user_id');
    $where = array(
        'msg_to' => $id,
        'is_read' => '0'
    );

    $msg_list = $db->where($where)->order("id desc")->select();
    $msg_unread_cout = count($msg_list);
    return $msg_unread_cout;
}

// 获取分类树
function get_root_sort($sort_id=0){
	$sort_obj	= M('Book_sort');
	$list		= $sort_obj->order('path')->select();
	$array		= Array();
	foreach($list as $sort){
		if($sort['sort_id'] == $sort_id){ continue; }
		$depth	= count(explode(',', $sort['path'])) - 1;
		$sep	= implode('', array_fill(0, $depth * 3, '&nbsp;'));
		$sort['show_name']	= $sep.$sort['sort_name'];
		$array[]= $sort;
	}
	return $array;
}

// 获取分类、书籍导航
function SN($book){
	$nav	= '<a href="'.__ROOT__.'">'.C('system.site_index').'</a> &gt; ';
	if(is_array($book)  && strpos($book['path'], ',') !== false){
		$list	= M('Book_sort')->query('select * from __TABLE__ where sort_id in ('.$book['path'].')');
		foreach($list as $sort){
			$nav	.= '<a href="'.BU($sort, 'lists').'">'.$sort['sort_name'].'</a> &gt; ';
		}
	}
	return $nav;
}

// 把信息加入cookie中，保存24小时
function add_to_cookie($name, $id){
	$new_msg	= $id.",";
	if(Cookie::is_set($name)){
		$contents = Cookie::get($name).$new_msg;
	}else{
		$contents = $new_msg;
	}
	Cookie::set($name, $contents, 86400);
}

// 检测信息是否在cookie中
function check_in_cookie($name, $id){
	if(Cookie::is_set($name)){
		$id_array	= explode(',', Cookie::get($name));
		if(in_array($id, $id_array)){
			return true;
		}
	}
	return false;
}

// 获取VIP章节的链接(只能是纯动态或伪静态）
function VLink($chapter, $page, $img = false){
	$chapter_id	= $chapter['chapter_id'];
	if($img){
		return __ROOT__.'/?s=home/vip/image/id/'.$chapter_id.'/page/'.$page;
	}
	if(C('book.chapter_html') == 2){	// 伪静态
		return __ROOT__.str_replace(array('{cid}','{page}'), array($chapter_id,$page), C('book.url_vip')).C('book.html_ext');
	}else{
		return U('home-vip/index/id/'.$chapter_id.'/page/'.$page);
	}
}

// 计算vip章节需付费数
function calculate_price($size){
	$persize	= (int)C('book.vip_persize'); $persize < 1 && $persize = 1000;
	$num		= floor($size/$persize);
	$total		= ($num * $persize + $persize * 0.8 <= $size) ? $num + 1 : $num;
	return $total * abs(C('book.vip_perprice'));
}

// 更新vip章节出售价格
function update_vip_price($ids=''){
	$chap_obj	= M('Book_chapter');
	$where		= '`is_vip`=1 and `chapter_type`=0';
	if($ids != '' && is_array($ids)){
		$where	= '`chapter_id` in ('.implode(',', $ids).')';
	}
	$list		= $chap_obj->field('chapter_id, chapter_size')->where($where)->select();
	foreach($list as $chap){
		$chap['sale_price']	= calculate_price($chap['chapter_size']);
		$chap_obj->save($chap);
	}
}
?>
