<?php
/**
 * 会员评论、回复、查看评论模块
 *
 * @author flashfxp
 */
class ReviewAction extends MainAction {
	// 查看评论（含回复）
	public function index(){
		$this->check_open(false);
		
		$review_id	= (int)$_REQUEST['id'];
		$reply_obj	= M('Book_reply');
		$review		= $reply_obj	->join(' book_review on book_review.review_id=book_reply.review_id')
									->where(array('book_reply.review_id'=>$review_id, 'book_reply.is_topic'=>1))->find();
		if($review){
			$review_obj	= M('Book_review');	// 评论浏览数递增
			$review_obj->setInc('view_num','review_id='.$review_id);
			$book		= book_search(array('book_id'=>$review['book_id']), true);

			$page_no	= empty($_GET['p']) ? "1" : (int)$_GET['p'];
			$perpage	= C('book.perpage_reply');
			$where		= array('review_id'=>$review_id, 'is_topic'=>0);
			$list		= $reply_obj->where($where)->page($page_no.','.$perpage)->select();
			$total		= $reply_obj->where($where)->count();
			$page_obj	= new Page($total, $perpage);
			$page		= $page_obj->show();

			$this->assign('review', $review);
			$this->assign('list', $list);
			$this->assign('page', $page);
			$this->assign('book', $book);
			$this->display(TEMPLATE_PATH.'/home/review.html');
		}else{
			$this->error('评论不存在！');
		}
	}

	// 查看主题评论列表
	public function lists(){
		$this->check_open(true);
		$book_id	= (int)$_REQUEST['book_id'];
		$page_no	= empty($_GET['p']) ? "1" : (int)$_GET['p'];
		$result		= review_search(array('book_review.book_id'=>$book_id), $page_no);
		$this->assign($result);
		$res['contents']	= $this->fetch(TEMPLATE_PATH.'/home/review_lists.html');
		$res['total']		= $result['total'];
		$res['pages']		= $result['pages'];
		$res['status']		= 2;
		die(json_encode($res));
	}

	// 发表评论
	public function post(){
		$this->check_if_login();
		$this->check_open(true);
		$book_id		= (int)$_REQUEST['id'];
		$review_obj		= M('Book_review');
		$result			= $review_obj->add(array('book_id'=>$book_id, 'if_check'=>C('book.review_auto')));
		if($result){
			$review_id	= $review_obj->getLastInsID();
			$data2		= $this->get_form_attr($_REQUEST, $review_id, '1');
			$reply_obj	= M('Book_reply');
			$result		= $reply_obj->add($data2);
			if(!$result){ $reply_obj->del($review_id); }
		}
		$this->show_result_msg($result, '评论成功！', '评论失败！');
	}

	// 回复评论
	public function reply(){
		$this->check_if_login();
		$this->check_open(true);
		$review_id	= (int)$_REQUEST['id'];
		$data		= $this->get_form_attr($_REQUEST, $review_id, '0');
		$reply_obj	= M('Book_reply');
		$result		= $reply_obj->add($data);
		if($result){	// 评论回复数加1
			$review_obj	= M('Book_review');
			$review_obj->setInc('reply_num', 'review_id='.$review_id);
		}
		$this->show_result_msg($result, '回复发表成功！', '回复发表失败！');
	}

	// 显示编辑评论主题（回复不允许编辑）
	public function update(){
		$reply	= $this->check_auth();
		$this->assign($reply);
		$this->assign('title','修改评论');
		$this->assign('action','do_update');
		$this->display(TEMPLATE_PATH.'/home/review_post.html');
	}

	// 编辑评论主题
	public function do_update(){
		$reply		= $this->check_auth();
		$reply_obj	= M('Book_reply');
		$data		= $this->get_form_attr($_POST, 0, 0, 'update');
		$result		= $reply_obj->save($data);
		$this->show_result_msg($result, '评论修改成功！', '评论修改失败', '?s=review/index/id/'.$reply['review_id']);
	}

	// 屏蔽评论
	public function hide(){
		$this->set_value('Book_reply', 'reply_id', $_REQUEST['reply_id'], 'if_display', $_REQUEST['value']);
	}

	// 检测当前用户是否有修改评论的权限
	protected function check_auth(){
		$reply_id	= (int)$_REQUEST['reply_id'];
		$reply_obj	= M('Book_reply');
		$reply		= $reply_obj->field('reply_id, review_id, subject, detail, poster_id')->find($reply_id);
		if($reply){
			if(Cookie::get('user_id') != $reply['poster_id']){
				$this->error('非法操作！');
			}else{
				return $reply;
			}
		}else{
			$this->error('评论不存在！');
		}
	}
	
	// 获取评论表单数据
	protected function get_form_attr($array, $review_id, $is_topic=0, $type='add'){
		if($type == 'add'){
			$data	= Array(
				'review_id'	=> $review_id,
				'is_topic'	=> $is_topic,
				'poster'	=> Cookie::get('user_name'),
				'poster_id'	=> (int)Cookie::get('user_id'),
				'post_time'	=> time(),
				'post_ip'	=> get_client_ip(),
				'if_display' => C('book.reply_auto')
			);
		}else{
			$data	= Array(
				'reply_id'	=> (int)$array['reply_id']
			);
		}
		$data['subject']	= safe_str($array['subject']);
		$data['detail']		= safe_str($array['detail']);

		return $data;
	}

	// 评论是否开启检测
	protected function check_open($ajax = false){
		if(C('book.review_open') == 0){
			$this->error('评论已关闭', $ajax);
		}
	}
}
?>
