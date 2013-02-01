<?php
/* 
 * 文集分类管理模块
 *
 * @author flashfxp
 */

Class WorksortAction extends BaseAction{
	// 显示分类中的书籍
	public function show(){
		$sort_id	= (int)$_GET['sort_id'];
		$sort_obj	= M('Book_works_sort');
		$sort		= $sort_obj	->field('book_works.work_name,book_works_sort.sort_name')
								->join(' book_works on book_works.work_id=book_works_sort.work_id')
								->find($sort_id);
		$this->assign('sort', $sort);

		$book_obj	= M('book');
		$list		= $book_obj->where(array('works_sid'=>$sort_id))->select();
		$this->assign('list', $list);

		$this->assign('title', '我的文集');
		$this->display(TEMPLATE_PATH.'/user/worksort.html');
	}

	// 显示添加文集分类
	public function add(){
		$this->check_auth();
		$sort['work_id']	= (int)$_REQUEST['work_id'];
		$this->assign('sort', $sort);
		$this->assign('title', '添加文集分类');
		$this->assign('action', 'do_add');
		$this->display(TEMPLATE_PATH.'/user/worksort_add.html');
	}

	// 添加文集分类
	public function do_add(){
		$this->check_auth();
		$sort_obj	= M('Book_works_sort');
		$data		= $this->get_form_attr($_POST, 'add');
		$result		= $sort_obj->add($data);
		$this->show_result_msg($result, '分类添加成功！', '分类添加失败！','?s=user/work');
	}

	// 显示修改文集分类
	public function update(){
		$sort_id	= (int)$_GET['sort_id'];
		$this->check_auth($sort_id);
		
		$sort_obj	= M('Book_works_sort');
		$sort		= $sort_obj->find($sort_id);
		$this->assign('sort', $sort);
		$this->assign('title', '修改文集分类');
		$this->assign('action', 'do_update');
		$this->display(TEMPLATE_PATH.'/user/worksort_add.html');
	}

	// 修改文集分类
	public function do_update(){
		$sort_id	= (int)$_POST['sort_id'];
		$this->check_auth($sort_id);

		$sort_obj	= M('Book_works_sort');
		$data		= $this->get_form_attr($_POST, 'update');
		$result		= $sort_obj->save($data);
		$this->show_result_msg($result, '分类修改成功！', '分类修改失败！','?s=user/work');
	}

	// 删除文集分类
	public function delete(){
		$sort_id	= (int)$_REQUEST['sort_id'];
		$this->check_auth($sort_id);

		$sort_obj	= M('Book_works_sort');
		$result		= $sort_obj->delete($sort_id);
		$this->show_result_msg($result, '分类删除成功！', '分类删除失败！','?s=user/work');
	}

	// 获取并检测表单中需要的文集分类参数，返回数组
	protected function get_form_attr($array, $type="add"){
		$data	= Array(
			'sort_name'			=> safe_str($array['sort_name']),
			'sort_order'		=> (int)$array['sort_order'],
			'sort_description'	=> safe_str($array['sort_description']),
			'work_id'			=> (int)$array['work_id']
		);
		if($type == "add"){
			$data['user_id']	= Cookie::get('user_id');
		}else{
			$data['sort_id']	= (int)$array['sort_id'];
		}
		return $data;
	}
	
	// 检测当前用户是否有操作权限
	protected function check_auth($sort_id='0'){
		if($sort_id > 0){
			$sort_obj	= M('Book_works_sort');
			$array		= $sort_obj->find($sort_id);
		}else{
			$work_id	= (int)$_REQUEST['work_id'];
			$work_obj	= M('Book_works');
			$array		= $work_obj->find($work_id);
		}
		$user_id		= Cookie::get('user_id');
		if($user_id != $array['user_id']){
			$this->error('非法操作！');
		}
	}
}

?>
