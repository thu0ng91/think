<?php
header("Content-Type: text/html; charset=UTF-8"); 

if(file_exists('../Conf/install.lock')){ die('您已经安装过本程序，如要再次安装本程序，请先删除以下文件： Conf/.lock'); }

$step = (int)$_GET['step'];
if($step == 2){
	check_step();
	include('step2.html');
}else if($step == 3){
	check_step();
	include('step3.html');
}else if($step == 4){
	check_step();
	$status = $error_msg = '';
	
	write_admin_info();
	
	$flag = check_db();
	
	if($flag){ lock_install(); }
	$checked = $flag ? 1 : 0;
	
	write_js_info();
	
	delete_cache();	// 一般不需要
	
	include('step4.html');
}else{
	include('step1.html');
}

function delete_cache(){
	$fname1 = '../Runtime/~app.php';
	$fname1 = '../Runtime/~runtime.php';
	if(file_exists($fname1)) unlink($fname1);
	if(file_exists($fname2)) unlink($fname2);
}

function lock_install(){
	global $status;
	@unlink('res/__data__.sql');
	file_put_contents('../Conf/install.lock', 'installed:'.date('Y-m-d H:i:s'));
	$status .= '<p><span class="successed">成功</span>锁定安装程序</p>';
	$status .= '<p><span class="successed">成功</span>结束安装</p>';
}

function write_admin_info(){
	global $status;
	$name	= $_POST['admin_name'];
	$email	= $_POST['admin_email'];
	$pass	= $_POST['admin_pass'];
	$pass2	= $_POST['admin_pass2'];
	if($pass != $pass2){ die('两次密码不同'); }
	if(empty($pass)){ die('管理员密码不能为空'); }
	if(empty($name) || empty($email)){ die('管理员账号和Email不能为空'); }
	$password	= md5($pass);
	$time		= time();
	$ip			= '127.0.0.1';
	$contents	= file_get_contents('./res/default_data.sql');
	$contents  .= "INSERT INTO `admin` VALUES ('1','$name','$password','1','$time','$ip');\n";
	$contents  .= "INSERT INTO `user` VALUES ('1','3','$name','$password','$email','0','$name','$time','$ip','1','');\n";
	$contents  .= "INSERT INTO `user_info` VALUES ('1','','','','','','','','','0','0','0','0','$time','1','','');\n";
	file_put_contents('./res/__data__.sql', $contents);
	$status .= '<p><span class="successed">成功</span>建立初始配置</p>';
}

function check_db(){
	global $status, $error_msg;
	$db = array(
		'host' => $_POST['db_host'],
		'port' => $_POST['db_port'],
		'name' => $_POST['db_name'],
		'user' => $_POST['db_user'],
		'pass' => $_POST['db_pass'],
		'prefix' => $_POST['db_prefix']
	);
	
	$lnk = @mysql_connect($db['host'].':'.$db['port'], $db['user'], $db['pass']);
	if(!$lnk){
		$flag = false;
		$error_msg = '数据库无法连接，请检测数据库相关设置！';
	}else{
		mysql_query("set names utf8");			//必须，设置utf-8编码数据传输
		$flag = @mysql_select_db($db['name'], $lnk);
		if(!$flag){
			$flag = mysql_query('create database '.$db['name'], $lnk);
			if($flag){
				mysql_select_db($db['name'], $lnk);
			}else{
				$error_msg = '数据库不存在，尝试建立失败！';
			}
		}
	}
	$status .= show_status($flag, '连接到数据库');
	if($flag){
		write_db_info($db);
		$flag = create_table($lnk);
		if($flag){ $flag = create_data($lnk); }
	}
	
	return $flag;
}

function write_db_info($db){
	global $status;
	$contents	= file_get_contents('./res/default_config.php');
	foreach($db as $key=>$value){
		$search[] = '{db_'.$key.'}';
		$replace[]= $value;
	}
	$contents	= str_replace($search, $replace, $contents);
	file_put_contents('../Conf/config_var.php', $contents);
}

function write_js_info(){
	$contents	= file_get_contents('./res/global.js');
	$url	= str_replace('http://','',$_SERVER['PHP_SELF']);
	$from	= strpos($url, '/');
	$to		= strpos($url, 'install');
	$root	= substr($url, $from, $to - $from);
	$contents	= str_replace('{baseurl}', $root, $contents);
	file_put_contents('../tpl/default/js/global.js', $contents);
}

function create_table($lnk){
	global $status;
	$fname		= "./res/default_struct.sql";
	$contents	= file_get_contents($fname);
	
	$contents	= str_replace("\r\n", "\n", $contents);
	$arrays		= explode(";\n", $contents);
	$checked	= true;
	$force		= (bool)$_POST['db_force'];
	foreach($arrays as $sql){
		$sql	= str_replace("\n", "", $sql);
		if($sql == ""){ continue; }
		if(strpos($sql, 'DROP TABLE IF EXISTS') !== false){
			$force && mysql_query($sql, $lnk);
		}else{
			preg_match("/create table `(.+?)`.*/i", $sql, $matches);
			$tname	= $matches[1];
			$flag	= mysql_query($sql, $lnk);
			if(!$flag){ $checked= false; }
			$str .= show_status($flag, '　创建数据表['.$tname.']');
		}
	}
	$status .= show_status($checked, '开始创建数据库结构').$str;
	
	return $checked;
}

function create_data($lnk){
	global $status;
	$fname		= "./res/__data__.sql";
	$contents	= file_get_contents($fname);
	$contents	= str_replace("\r\n", "\n", $contents);
	$arrays		= explode(";\n", $contents);
	$checked	= true;
	foreach($arrays as $sql){
		$sql	= str_replace("\n", "", $sql);
		if($sql == ""){ continue; }
		if(!mysql_query($sql, $lnk)){ $checked = false; }
	}
	$status .= show_status($checked, '安装初始数据');
	return $checked;
}

function show_status($status, $note){
	$str  = $status ? '<p><span class="successed">成功' : '<p><span class="failed">失败';
	$str .= '</span>'.$note.'</p>';
	return $str;
}

function check_step(){
	if(!isset($_POST['accept']) || $_POST['accept'] != 1){
		header('location:index.php?step=1');
	}
}
?>