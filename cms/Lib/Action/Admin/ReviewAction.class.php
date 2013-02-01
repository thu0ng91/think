<?php
/**
 * 书籍评论管理模块
 *
 * @author flashfxp
 */
class ReviewAction extends BaseAction {
	// 所有评论主题列表
    public function index(){
		$where_arr	= $this->get_where_array($_REQUEST);
		$page_no	= empty($_GET['p']) ? "1" : (int)$_GET['p'];
		$result		= review_search($where_arr, $page_no);
		$this->assign($result);	// $list, $page 两个变量赋值
		$this->assign('title', '评论管理');
		$this->display('./Public/admin/review.html');
	}

	// 删除评论（不删除回复表）
	public function delete(){
		$review_id	= (array)$_REQUEST['id'];
		$review_obj	= M('Book_review');
		foreach($review_id as $rid){
			$rid	= (int)$rid;
			if($rid < 1){ continue; }
			$review_obj->delete($rid);
		}
		$this->show_result_msg(true, '操作成功！', '', '?s=admin/review');
	}

	// 设置或取消审核
	public function check(){
		$this->set_value('Book_review', 'review_id', $_REQUEST['id'], 'if_check', $_REQUEST['value']);
	}

	// 设置或取消评论锁定
	public function lock(){
		$this->set_value('Book_review', 'review_id', $_REQUEST['id'], 'if_lock', $_REQUEST['value']);
	}

	// 设置或取消评论加精
	public function good(){
		$this->set_value('Book_review', 'review_id', $_REQUEST['id'], 'if_good', $_REQUEST['value']);
	}

	// 设置或取消评论置顶
	public function top(){
		$this->set_value('Book_review', 'review_id', $_REQUEST['id'], 'if_top', $_REQUEST['value']);
	}

	// 获取查询参数数组
	protected function get_where_array($array){
		$where_arr = Array();
		if(!empty($array['id'])){
			$where_arr['book.book_id']			= (int)$array['id'];
		}
		if(!empty($array['book_name'])){
			$where_arr['book.book_name']		= safe_str($array['book_name']);
		}
		if(!empty($array['poster_id'])){
			$where_arr['book_reply.poster_id']	= (int)$array['poster_id'];
		}
		if(!empty($array['poster'])){
			$where_arr['book_reply.poster']	= safe_str($array['poster']);
		}
		return $where_arr;
	}

	// 删除回复，一条或多条
	public function reply_del(){
		$reply_id	= (array)$_REQUEST['id'];
		$reply_obj	= M('Book_reply');
		$review_obj	= M('Book_review');
		foreach($reply_id as $rid){
			if($rid < 1){ continue; }
			$reply	= $reply_obj->find($rid);
			if($reply){
				$reply_obj->delete($rid);
				$review_obj->setDec('reply_num', 'review_id='.$reply['review_id']);
			}
		}
		$this->show_result_msg(true, '操作成功！', '');
	}

	// 显示或屏蔽回复
	public function reply_show(){
		$this->set_value('Book_reply', 'reply_id', $_REQUEST['id'], 'if_display', $_REQUEST['value']);
	}
}
?>
