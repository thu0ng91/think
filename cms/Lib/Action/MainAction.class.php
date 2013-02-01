<?php
/*
 * 自定义基类，所有Admin分组action的父类
 *
 * @author flashfxp
 */

class MainAction extends Action {
    public function  __construct() {
		parent::__construct();
		$this->init_site();
	}
	
	// 全站头部、尾部页面获取
	protected function init_site(){
		$this->assign('cmsroot', __ROOT__.'/');		// cms目录
		$tpldir		= str_replace('/./', '/', APP_TMPL_PATH);
		$this->assign('tpldir', $tpldir);		// 当前风格目录
		$this->assign('tplcms', __ROOT__.'/tpl/default/');	// 默认风格目录

		$sort_obj	= M('Book_sort');		// 获取书籍分类
		$sort_list	= $sort_obj->where('`super_id`=0')->order('sort_order')->limit(8)->select();
		$this->assign('sorts', $sort_list);

		$links_obj	= M('Links');			// 获取友情链接
		$links		= $links_obj->where('`status`=1')->order('`orderid`')->select();
		$this->assign('links', $links);

		if(C('book.search_history')){			// 获取热门搜索
			$search_obj	= M('Book_search');
			$search		= $search_obj->where('`snum`>=3')->order('snum desc')->limit(5)->select();
			$this->assign('search', $search);
		}

		if(C('DEFAULT_THEME') != 'default' && file_exists(TEMPLATE_PATH.'/header.html')){
			$header		= $this->fetch(TEMPLATE_PATH.'/header.html');
			$header		= "<script>var baseurl	= '".__ROOT__."/';</script>\n".$header;
			$this->assign('header', $header);
		}
		if(C('DEFAULT_THEME') != 'default' && file_exists(TEMPLATE_PATH.'/footer.html')){
			$footer		= $this->fetch(TEMPLATE_PATH.'/footer.html');
			$this->assign('footer', $footer);
		}
	}

	// 登录状态判断（供需要登录，并且是 ajax 调用的函数使用）
	protected function check_if_login(){
		if(Cookie::get('user_id') < 1){
			$res['status']	= false;
			$res['info']	= '请先登录！';
			die(json_encode($res));
		}
	}

	// 登录状态检测（供 User 分组使用）
	protected function check_has_login($ajax=false){
		$user_id	= (int)Cookie::get('user_id');
		if($user_id < 1){
			Cookie::set('last_url', $_SERVER['REQUEST_URI']);
			$this->assign('jumpUrl', '?s=login');
			$this->error('请先登录！', $ajax);
		}else{
			return $user_id;
		}
	}

		/**
     +-----------------------------------------------------------------------------
	 * 根据结果进行页面跳转和信息显示
     +-----------------------------------------------------------------------------
	 */
	protected function show_result_msg($result, $success_msg, $error_msg, $success_url='', $error_url=''){
		$ajax = isset($_REQUEST['ajax']) ? true : false;

		if($result){
			!$ajax && $this->assign('jumpUrl', $success_url);
			$this->success($success_msg, $ajax);
		}else{
			!$ajax && $this->assign('jumpUrl', $error_url);
			$this->error($error_msg, $ajax);
		}
	}
}
?>