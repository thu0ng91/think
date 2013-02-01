<?php

$envi	= array(
	'php'	=> array('name'=>'PHP版本', 'rv'=>'5.0', 'nv'=>phpversion()),
	'mysql'	=> array('name'=>'MYSQL版本', 'rv'=>'4.3', 'nv'=>mysql_get_client_info()),
	'zend'	=> array('name'=>'ZEND库版本', 'rv'=>'2.0', 'nv'=>zend_version()),
);

$dirs	= array(
	'book/',
	'Conf/',
	'files/',
	'files/ads/',
	'files/ads/images/',
	'files/backup/',
	'files/ebook/',
	'files/images/',
	'install/res/',
	'Public/js/user/',
	'Runtime/',
	'tpl/',
	'.htaccess'
);

$writable = array();
foreach($dirs as $fname){
	$flag = N_writable('../'.$fname) ? '' : '不';
	$writable[]	= array('name'=>$fname, 'rv'=>'可写', 'nv'=>$flag);
}

$res	= array('envi'=>$envi, 'write'=>$writable);
echo json_encode($res);

function N_writable($pathfile) {	//fix windows bug
	$isDir = substr($pathfile, -1) == '/' ? true : false;
	if($isDir){
		if(is_dir($pathfile)){
			mt_srand((double) microtime() * 1000000);
			$pathfile = $pathfile . 'pw_' . uniqid(mt_rand()) . '.tmp';
		}elseif (@mkdir($pathfile)){
			return N_writable($pathfile);
		}else{
			return false;
		}
	}
	@chmod($pathfile, 0777);
	$fp = @fopen($pathfile, 'ab');
	if($fp === false){ return false; }
	fclose($fp);
	$isDir && @unlink($pathfile);
	return true;
}
?>