<?php
/* 
 * 收藏书籍、加入书签操作模块
 *
 * @author flashfxp
 */

class FavorAction extends MainAction {
	// 收藏夹书籍列表
	public function index(){
		$favor_obj	= M('Book_favorite');
		$user_id	= $this->check_has_login();
		$list		= $favor_obj->field('book_favorite.*, book.*, book_chapter.chapter_name')
								->join(' book on book.book_id=book_favorite.book_id')
								->join(' book_chapter on book_chapter.chapter_id=book_favorite.chapter_id')
								->where('book_favorite.user_id='.$user_id)->select();
		$this->assign('list', $list);
		$this->assign('title', '收藏夹');
        $this->assign('msg_unread',msg_unread_count());
		$this->display(TEMPLATE_PATH.'/user/favor.html');
	}

	// 添加书籍、书签到收藏夹，客户端通过ajax方式提交
	public function add(){
		$user_id	= $this->check_has_login(true);
		$book_id	= (int)$_REQUEST['bid'];
		$chap_id	= (int)$_REQUEST['cid'];
		$favor_obj	= M('Book_favorite');
		$favor		= $favor_obj->where(array('book_id'=>$book_id, 'user_id'=>$user_id))->find();
		$flag	= $chap_id > 0 ? '添加书签' : '收藏书籍';

		if(!$favor){
			$data	= $this->get_attr($book_id, $chap_id);
			$result	= $favor_obj->add($data);
			if(!$result){
				$this->error($flag.'失败！', true);
			}else{	
				$this->add_favor_num($book_id);
				$this->success($flag.'成功！', true);
			}
		}else{
			if($chap_id > 0 && $chap_id != $favor['chapter_id']){
				$favor_obj->chapter_id = $chap_id;
				$favor_obj->last_visit = time();
				$favor_obj->save();
				$this->success($flag.'成功！', true);
			}else{
				$this->success('您已收藏该书籍！', true);
			}
		}
	}

	// 从收藏夹中删除书籍, 只提供单本删除
	public function delete(){
		$user_id	= $this->check_has_login();
		$favor_id	= (int)$_REQUEST['id'];
		$favor_obj	= M('Book_favorite');
		$favor_obj->find($favor_id);
		if($favor_obj->user_id == $user_id){
			$favor_obj->delete($favor_id);
			$this->success('删除成功！');
		}else{
			$this->error('非法操作！');
		}
	}

	// 书籍被收藏数加1
	protected function add_favor_num($book_id){
		$book_obj	= M('Book');
		return $book_obj->setInc('store_num', 'book_id='.$book_id);
	}

	// 获取收藏夹参数数组
	protected function get_attr($book_id, $chapter_id){
		$time	= time();
		$data	= Array(
			'user_id'	=> Cookie::get('user_id'),
			'book_id'	=> $book_id,
			'chapter_id'=> $chapter_id,
			'add_time'	=> $time,
			'last_visit'=> $time
		);
		return $data;
	}
}

?>
