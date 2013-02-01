<?php
/*
 * 网站首页
 *
 * @author flashfxp
 */

class IndexAction extends MainAction{
    public function index(){
		$filename	= 'default.html';
		if(C('book.chapter_html')==1 && file_exists($filename) && !isset($_GET['html'])){
			include($filename); exit;
		}

		$this->display(TEMPLATE_PATH.'/index.html');
    }
}
?>