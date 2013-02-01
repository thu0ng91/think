<?php
/**
 * 书籍推荐管理模块
 *
 * @author flashfxp
 */
class RecommendAction extends BaseAction {
	// 显示书籍分类推荐列表
    public function index(){
		$list	= $this->get_sort_list();
		$this->assign('list', $list);
		$this->assign('title', '书籍推荐');
		$this->display('./Public/admin/recommend.html');
	}

	// 显示某类推荐的书籍列表, 暂时没考虑分页（推荐数较少）
	public function show(){
		$sort_id		= (int)$_GET['sort_id'];
		$sort_obj		= M('Book_recommend_sort');
		$sort			= $sort_obj->find($sort_id);

		$recommend_obj	= M('Book_recommend');
		$list = $recommend_obj	->field('book_recommend.*, book.*')
								->join(' book on book.book_id=book_recommend.book_id')
								->where(array('book_recommend.sort_id'=>$sort_id))->select();
		$this->assign('sort', $sort);
		$this->assign('list', $list);
		$this->assign('title', '书籍推荐');
		$this->display('./Public/admin/recommend_show.html');
	}

	// 显示添加推荐书籍
	public function add(){
		$list	= $this->get_sort_list();
		if(empty($list)){
			$this->error('请先添加推荐分类！');
		}else{
			$this->assign('list', $list);
		}
		if(!empty($_GET['book_id'])){
			$book_id	= (int)$_GET['book_id'];
			$book_obj	= M('Book');
			$book		= $book_obj->field('book_id, book_name')->find($book_id);
			$this->assign('book', $book);
		}
		$this->assign('title', '推荐书籍');
		$this->assign('action', 'do_add');
		$this->display('./Public/admin/recommend_add.html');
	}

	// 添加推荐书籍
	public function do_add(){
		$data			= $this->get_recommend_attr($_POST, 'add');
		$recommend_obj	= M('Book_recommend');
		$book			= $recommend_obj->where('sort_id='.$data['sort_id'].' and book_id='.$data['book_id'])->find();
		if(!$book){
			$result			= $recommend_obj->add($data);
			$this->show_result_msg($result, '推荐成功！', '推荐失败！','?s=admin/recommend');
		}else{
			$this->error('本书已在该推荐列表中！');
		}
	}

	// 显示修改推荐书籍
	public function update(){
		$list			= $this->get_sort_list();
		$id				= (int)$_GET['id'];
		$recommend_obj	= M('Book_recommend');
		$book			= $recommend_obj->join(' book on book.book_id=book_recommend.book_id')->find($id);
		$this->assign('book', $book);
		$this->assign('list', $list);
		$this->assign('title', '推荐书籍');
		$this->assign('action', 'do_update');
		$this->display('./Public/admin/recommend_add.html');
	}

	// 添加推荐书籍
	public function do_update(){
		$data			= $this->get_recommend_attr($_POST, 'update');
		$recommend_obj	= M('Book_recommend');
		$result			= $recommend_obj->save($data);
		$this->show_result_msg($result, '修改成功！', '修改失败！','?s=admin/recommend');
	}

	// 删除推荐书籍
	public function delete(){
		$id_arr			= (array)$_REQUEST['id'];
		$recommend_obj	= M('Book_recommend');
		$suc_num		= $err_num	= 0;
		foreach($id_arr as $id){
			if($id < 1){ continue; }
			$result		= $recommend_obj->delete($id);
			$result ? $suc_num++ : $err_num++;
		}
		$message  = '操作成功！共删除'.$suc_num.'本书籍';
		$err_num > 0 && $message .= '，'.$err_num.'本书籍删除失败！';
		$this->show_result_msg(true, $message, '', '?s=admin/recommend');
	}

	// 显示添加推荐分类
	public function add_sort(){
		$this->assign('title', '添加推荐分类');
		$this->assign('action', 'do_add_sort');
		$this->display('./Public/admin/recommend_sort.html');
	}

	// 添加推荐分类
	public function do_add_sort(){
		$data['sort_name']	= safe_str($_POST['sort_name']);
		$sort_obj			= M('Book_recommend_sort');
		$result				= $sort_obj->add($data);
		$this->show_result_msg($result, '推荐分类添加成功！', '推荐分类添加失败！','?s=admin/recommend');
	}

	// 显示修改推荐分类
	public function update_sort(){
		$sort_id	= (int)$_GET['sort_id'];
		$sort_obj	= M('Book_recommend_sort');
		$sort		= $sort_obj->find($sort_id);
		$this->assign('sort', $sort);
		$this->assign('title', '修改推荐分类');
		$this->assign('action', 'do_update_sort');
		$this->display('./Public/admin/recommend_sort.html');
	}

	// 修改推荐分类
	public function do_update_sort(){
		$data	= Array(
			'sort_id'	=> (int)$_POST['sort_id'],
			'sort_name'	=> safe_str($_POST['sort_name'])
		);
		$sort_obj		= M('Book_recommend_sort');
		$result			= $sort_obj->save($data);
		$this->show_result_msg($result, '推荐分类修改成功！', '推荐分类修改失败！','?s=admin/recommend');
	}

	// 删除推荐分类，同时删除所有该分类的推荐书籍（从推荐书籍表中）
	public function delete_sort(){
		$sort_id	= (int)$_REQUEST['sort_id'];
		$sort_obj	= M('Book_recommend_sort');
		$result		= $sort_obj->delete($sort_id);
		if($result){
			$recommend_obj	= M('Book_recommend');
			$recommend_obj->where('sort_id='.$sort_id)->delete();
		}
		$this->show_result_msg($result, '推荐分类删除成功！', '推荐分类删除失败！','?s=admin/recommend');
	}

	// 获取推荐分类列表
	protected function get_sort_list(){
		$sort_obj	= M('Book_recommend_sort');
		$list		= $sort_obj->select();
		$this->assign('list', $list);
		return $list;
	}

	// 获取推荐书籍参数数组
	protected function get_recommend_attr($array, $type="add"){
		$data = Array(
			'sort_id'	=> (int)$array['sort_id'],
			'book_id'	=> (int)$array['book_id'],
			'order'		=> (int)$array['order']
		);
		if($type == "add"){
			$data['add_time']	= time();
		}else{
			$data['id']			= (int)$array['id'];
		}
		return $data;
	}
}
?>
