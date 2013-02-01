<?php
/**
 * 书籍管理模块(已验证)
 *
 * @author flashfxp
 */
class BookAction extends BaseAction {
	// 书籍管理列表
    public function index(){
		$where_arr	= Array();
		if(!empty($_GET['sort_id'])){
			$where_arr['book.sort_id']	= (int)$_GET['sort_id'];
		}
		if(!empty($_GET['is_vip'])){
			$where_arr['book.is_vip']	= (int)$_GET['is_vip'];
		}
		
		$res	= book_search($where_arr, false, C('book.perpage_book'), '', true);
		$this->assign('list', $res['list']);
		$this->assign('page', $res['page']);
		$this->assign('title', '书籍管理');
		$this->display('./Public/admin/book.html');
	}

	// 显示添加书籍
	public function add(){
		//$sort_obj	= M('Book_sort');
		//$sort_list	= $sort_obj->order('sort_order')->select();
		$sort_list	= get_root_sort();
		$this->assign('book_sort', $sort_list);
		
		$this->assign('title', '添加书籍');
		$this->assign('action', 'do_add');
		$this->display('./Public/admin/book_add.html');
	}

	// 添加书籍
	public function do_add(){
		$book_obj	= M('Book');
		$data		= $this->get_form_attr($_POST, "add");
		$result		= $book_obj->add($data);
		$this->show_result_msg($result, '书籍添加成功', '书籍添加失败！', '?s=admin/book');
	}

	// 显示修改书籍
	public function update(){
		//$sort_obj	= M('Book_sort');
		//$sort_list	= $sort_obj->order('sort_order')->select();
		$sort_list	= get_root_sort();
		$this->assign('book_sort', $sort_list);

		$book_id	= (int)$_GET['id'];
		$book_obj	= M('Book');
		$book		= $book_obj->find($book_id);
		$this->assign('book', $book);

		$this->assign('title','修改书籍');
		$this->assign('action', 'do_update');
		$this->display('./Public/admin/book_add.html');
	}

	// 修改书籍
	public function do_update(){
		$book_id	= (int)$_POST['book_id'];
		$book_obj	= M('Book');
		$data		= $this->get_form_attr($_POST, "update");
		$result		= $book_obj->save($data);
		$this->show_result_msg($result, '书籍修改成功', '书籍修改失败！');
	}

	/*
	 *  删除一本或多本书籍，同时会删除该书籍的所有章节！
	 *  需要特别注意，尽量用取消审核来代替删除
	 */
	public function delete(){
		$books	= (array)$_REQUEST['id'];
		$book_obj	= M('Book');
		$chapter_obj= M('Book_chapter');

		foreach($books as $bid){
			$bid = (int)$bid;
			if($bid < 1){ continue; }
			$result	= $book_obj->delete($bid);
			if($result){
				$chapter_obj->where('book_id='.$bid)->delete();
			}
		}
		$this->show_result_msg(true, '操作成功！', '');
	}
	
	// 设置或取消审核书籍
	public function check(){
		$this->set_value('Book', 'book_id', $_REQUEST['id'], 'if_check', $_REQUEST['value']);
	}

	// 设置或取消隐藏书籍
	public function hide(){
		$this->set_value('Book', 'book_id', $_REQUEST['id'], 'if_display', $_REQUEST['value']);
	}

	// 设置或取消VIP书籍
	public function vip(){
		$this->set_value('Book', 'book_id', $_REQUEST['id'], 'is_vip', $_REQUEST['value']);
	}

	// 添加或修改书籍时，从表单中提取并检测需要的字段信息，返回一个数组
	protected function get_form_attr($array, $type="add"){
		$data = array(
			'book_name'	=> safe_str($array['book_name']),
			'author'	=> safe_str($array['author']),
			'keywords'	=> safe_str($array['keywords']),
			'sort_id'	=> (int)$array['sort_id'],
			'is_vip'	=> (int)$array['is_vip'],
			'is_power'	=> (int)$array['is_power'],
			'is_first'	=> (int)$array['is_first'],
			'is_full'	=> (int)$array['is_full'],
			'image_url'	=> $this->get_image($array['image_url'], $array['down'], $_FILES['upload']),
			'introduce'	=> safe_str($array['introduce'])
		);
		if($type == "add"){
			$data['poster']		= Cookie::get('admin_name');
			$data['poster_id']	= (int)Cookie::get('admin_id');
			$data['post_time']	= $data['last_update'] = $data['last_visit'] = $data['last_vote'] = time();
		}else{
			$data['book_id']	= (int)$array['book_id'];
		}
		return $data;
	}

	// 获取图片链接（可上传或下载到本地）
	protected function get_image($url, $down, $upfile){
		$fdir	= 'files/images/';
		if(!empty($upfile['name'])){
			return upload_file($fdir);
		}
		if($down){
			return download_file($url, $fdir);
		}else{
			return $url;
		}
	}
}
?>
