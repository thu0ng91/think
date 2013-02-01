<?php
define('THINK_PATH', './ThinkPHP'); //ThinkPHP路径
define('APP_NAME', '.'); //项目名称
define('APP_PATH', '.'); //项目路径
require(THINK_PATH.'/ThinkPHP.php'); //加载框架入口文件
App::run(); //实例化一个网站应用实例
?>