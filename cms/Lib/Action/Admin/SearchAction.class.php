<?php
/**
 * 搜索记录管理模块
 *
 * @author flashfxp
 */
class SearchAction extends BaseAction {
	// 文集管理首页
    public function index(){
		$search_obj	= M('Book_search');
		$tid		= (int)$_REQUEST['tid'];
		$where		= '';
		$order		= 'sid desc';
		if($tid == 1){			// 热门搜索
			$where	= '`snum`>=3';
			$order	= 'snum desc';
		}else if($tid == 2){	// 失败搜索
			$where	= '`result`=0';
		}
		import("ORG.Util.Page");
		$page_no	= empty($_GET['p']) ? "1" : (int)$_GET['p'];
		$perpage	= 20;
		$list		= $search_obj->where($where)->order($order)->page($page_no.','.$perpage)->select();
		$total      = $search_obj->where($where)->count();
		$page_obj	= new Page($total, $perpage);
		$page_show	= $page_obj->show();
		$this->assign('list', $list);
		$this->assign('page', $page_show);
		$this->assign('search_on', C('book.search_history'));
		$this->assign('title','搜索管理');
		$this->display('./Public/admin/search.html');
	}
	
	// 标记搜索状态
	public function check(){
		$this->set_value('Book_search', 'sid', $_REQUEST['id'], 'result', $_REQUEST['value']);
	}

	// 删除搜索记录
	public function delete(){
		$search_id	= (array)$_REQUEST['id'];
		$search_obj	= M('Book_search');
		foreach($search_id as $sid){
			$sid	= (int)$sid;
			if($sid < 1){ continue; }
			$search_obj->delete($sid);
		}
		$this->show_result_msg(true, '操作成功！', '');
	}

	// 开启或关闭统计
	public function open(){
		$data['search_history']	= $_REQUEST['value'] == 1 ? 1 : 0;
		update_config($data, 'book');
		$this->success('操作成功！', true);
	}
}
?>
