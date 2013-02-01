<?php
/**
 * 书籍章节管理模块
 *
 * @author flashfxp
 */
class ChapterAction extends BaseAction {
	// 章节管理首页
    public function index(){
		$book_id	= (int)$_GET['id'];
		$book		= $this->check_auth($book_id);

		$volumes	= $this->get_volume_list($book['book_id']);
		$this->assign('volumes', $volumes);

		$chapter_obj= M('Book_chapter');
		$list		= $chapter_obj	->where('book_id='.$book['book_id'])->select();
		$this->assign('list', $list);
		$this->assign('book', $book);
		$this->display('./Public/admin/chapter.html');
	}

	// 显示添加章节页面
	public function add(){
		$book_id	= (int)$_GET['id'];
		$book		= $this->check_auth($book_id);
		$volumes	= $this->get_volume_list($book['book_id']);
		$this->assign('list', $volumes);

		$this->assign('title','添加章节');
		$this->assign('book',$book);
		$this->assign('action','do_add');
		$this->display('./Public/admin/chapter_add.html');
	}

	/*
	 * 添加章节
	 * 可以设置允许多人上传，但只有该书籍的添加者或管理员才有权限进行管理操作
	 * 同时，修改或删除章节时，需要同时传入book_id和chapter_id两个参数
	 */
	public function do_add(){
		$chapter_obj	= M('Book_chapter');
		$data			= $this->get_form_attr($_POST, "add");
		$result			= $chapter_obj->add($data);
		if($result){
			$this->update_book($data['book_id'], $result);
		}else{
			die($chapter_obj->getDbError()."<br />".$chapter_obj->getLastSql());
		}
		$this->show_result_msg($result, '章节添加成功', '章节添加失败！','?s=admin/chapter/index/id/'.$data['book_id']);
	}

	// 显示修改章节页面
	public function update(){
		$book			= $this->check_auth();
		$volumes		= $this->get_volume_list($book['book_id']);
		$this->assign('list', $volumes);
		$this->assign('book', $book);

		$chapter_obj	= M('Book_chapter');
		$chapter		= $chapter_obj->find($book['chapter_id']);
		$this->assign('chapter', $chapter);
		$this->assign('title','修改章节');
		$this->assign('action','do_update');
		$this->display('./Public/admin/chapter_add.html');
	}

	// 修改章节
	public function do_update(){
		$this->check_auth();
		$chapter_obj	= M('Book_chapter');
		$data			= $this->get_form_attr($_POST, "update");
		$result			= $chapter_obj->save($data);
		$this->update_book($data['book_id'], $data['chapter_id']);
		$this->show_result_msg($result, '章节修改成功', '章节修改失败！','?s=admin/chapter/index/id/'.$data['book_id']);
	}

	// 删除章节(一章或多章）
	public function delete(){
		$book_id		= (int)$_REQUEST['book_id'];
		$this->check_auth($book_id);

		$chapter_obj	= M('Book_chapter');
		$chapter_arr	= (array)$_REQUEST['id'];
		$where_arr		= array('book_id'=>$book_id);
		foreach($chapter_arr as $cid){
			$where_arr['chapter_id'] = $cid;
			$chapter_obj->where($where_arr)->delete();
		}
		$this->update_book($book_id);
		$this->show_result_msg(true, '操作成功！', '');
	}

	// 设置或取消vip章节
	public function vip(){
		$_REQUEST['value'] == 1 && update_vip_price((array)$_REQUEST['id']);
		$this->set_value('Book_chapter', 'chapter_id', $_REQUEST['id'], 'is_vip', $_REQUEST['value']);
	}

	// 设置或取消草稿章节
	public function draft(){
		$this->set_value('Book_chapter', 'chapter_id', $_REQUEST['id'], 'is_draft', $_REQUEST['value']);
	}

	// 设置或取消审核章节
	public function check(){
		$this->set_value('Book_chapter', 'chapter_id', $_REQUEST['id'], 'if_check', $_REQUEST['value']);
	}

	// 转移章节到新的分卷
	public function transfer(){
		$this->set_value('Book_chapter', 'chapter_id', $_REQUEST['id'], 'volume_id', $_REQUEST['volume_id']);
	}

	// 获取分卷列表
	protected function get_volume_list($book_id){
		$chapter_obj	= M('Book_chapter');
		$list			= $chapter_obj->field('chapter_id,chapter_name')
						->where(array('book_id'=>$book_id, 'chapter_type'=>1))
						->order('chapter_order')->select();
		return (array)$list;
	}

	// 添加或修改章节时，从POST表单中提取并检测需要的字段信息，返回一个数组
	protected function get_form_attr($array, $type="add"){
		$time	= time();
		$data	= Array(
			'book_id'		=> (int)$array['book_id'],
			'chapter_name'	=> safe_str($array['chapter_name']),
			'chapter_type'	=> (int)$array['chapter_type'],
			'chapter_order'	=> (int)$array['chapter_order'],
			'update_time'	=> $time
		);
		if($data['chapter_type'] == 0){	//普通章节
			$data['is_draft']		= (int)$array['is_draft'];
			$data['volume_id']		= (int)$array['volume_id'];
			$data['chapter_detail']	= pure_txt(safe_str($array['chapter_detail']));	// 章节段落规范化
			$data['chapter_size']	= count_words_num($data['chapter_detail']);
			$data['sale_price']		= calculate_price($data['chapter_size']);
		}
		if($type == "add"){
			$data['poster_id']		= Cookie::get('admin_id');
			$data['poster']			= Cookie::get('admin_name');
			$data['post_time']		= $time;
			$data['if_check']		= (int)C('book.chapter_auto_check');
		}else{
			$data['chapter_id']		= (int)$array['chapter_id'];
		}
		return $data;
	}

	// 更新书籍信息(最后更新时间、最新章节、总字数
	protected function update_book($book_id, $chapter_id=0){
		$chapter_obj	= M('Book_chapter');
		if($chapter_id > 0){
			$chapter	= $chapter_obj->find($chapter_id);
		}else{
			$chapter	= $chapter_obj->where(array('book_id'=>$book_id))->order('post_time desc')->limit(1)->find();
		}

		$data		= array(
			'book_id'		=> $book_id,
			'last_update'	=> $chapter['update_time'],
			'last_chapter'	=> $chapter['chapter_name'],
			'last_chapterid'=> $chapter['chapter_id'],
			'total_size'	=> $chapter_obj->where('book_id='.$book_id)->sum('chapter_size')
		);
		M('Book')->save($data);

		$list[0] = $chapter;
		if($chapter['volume'] != 0){
			$volume = $chapter_obj->find($chapter['volume_id']);
			$list[1] = $volume;
		}
		$this->do_auto($book_id, $list);
	}

	// 自动生成静态页面、电子书处理
	protected function do_auto($book_id, $chapters){
		C('book.ebook_txt_auto') && $type[] = 'txt';
		C('book.ebook_umd_auto') && $type[] = 'umd';
		C('book.ebook_epub_auto') && $type[] = 'epub';
		if(!empty($type)){
			$ebook = A("Admin.Ebook"); // 实例化HtmlAction控制器对象
			$ebook->create_one($book_id, $type);
		}

		if(C('book.html_auto')){
			$type = array('index', 'menu', 'down', 'full', 'read');
			$html = A("Admin.Html"); // 实例化HtmlAction控制器对象
			$html->for_auto($book_id, $type, $chapters);
		}
	}

	// 检测当前用户是否有权限操作本章节
	protected function check_auth($book_id=0){
		$field			= 'book.book_id,book.book_name,book.poster_id';
		if($book_id >0){
			$book_obj	= M('Book');
			$book		= $book_obj->field($field)->find($book_id);
		}else{
			if(!empty($_REQUEST['id'])){
				$chapter_id	= (int)$_REQUEST['id'];
				$chapter_obj= M('Book_chapter');
				$book		= $chapter_obj	->field($field.',book_chapter.chapter_id')
							->join(' book on book.book_id=book_chapter.book_id')
							->where('book_chapter.chapter_id='.$chapter_id)->find();
			}
		}
		return $book;
	}
}
?>
