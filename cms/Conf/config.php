<?php 
$config_arr	= require 'config_var.php';

$system_arr	= array(
	'APP_DEBUG' => false, // 开启调试模式
	'APP_GROUP_LIST'=>'Admin,User,Home', // 开启分组
	'DEFAULT_GROUP'=>'Home', // 默认分组
	'URL_PATHINFO_MODEL'=>2, // 1 普通模式 参数没有顸序 2 智能模式 自劢识删模块和操作
	'APP_CONFIG_LIST' => array('database'),
	'NOT_AUTH_MODULE'=>'login,install,do,Index,payment,order',// 默认无需认证模块,字母有大小写
	'REQUIRE_AUTH_MODULE'=>'',// 默认需要认证模块
	'NOT_AUTH_ACTION'=>'lists,show',// 默认无需认证操作
	'TMPL_ACTION_ERROR'=> '/refresh.html', // 默认错误跳转对应的模板文件
	'TMPL_ACTION_SUCCESS'=> '/refresh.html', // 默认成功跳转对应的模板文件
	'DB_FIELDTYPE_CHECK'=>true, // 开启字段类型验证
	'TOKEN_ON'=>false,  // 是否开启令牌验证
	'URL_CASE_INSENSITIVE' => true, // URL访问不再区分大小写
);

return array_merge($config_arr, $system_arr); 

?>
