<?php
$site_config = require '../../Conf/config_var.php';

$dbhost = $site_config['DB_HOST'].':'.$site_config['DB_PORT'];
$dbuser = $site_config['DB_USER'];
$dbpass = $site_config['DB_PWD'];
$dbname = $site_config['DB_NAME'];
$link = mysql_connect($dbhost, $dbuser, $dbpass) or die("Unable to connect to database");
mysql_select_db($dbname,$link);
mysql_query("set names utf8");		//必须，设置utf-8编码数据传输

?>