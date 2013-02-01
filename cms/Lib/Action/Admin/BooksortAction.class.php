<?php
/**
 * 书籍分类管理模块
 *
 * @author flashfxp
 */
class BooksortAction extends BaseAction {
	// 书籍分类管理首页
    public function index(){
		$sort_arr	= $this->get_sub_sort(0);
		$this->assign('list', $sort_arr);
		$this->assign('title', '书籍分类列表');
		$this->display('./Public/admin/booksort.html');
	}

	// 显示下级分类列表，若为空则跳到该分类书籍列表
	public function show(){
		$super_id	= (int)$_GET['super_id'];
		$sort_arr	= $this->get_sub_sort($super_id);
		if(!empty($sort_arr)){
			$sort	= M('Book_sort')->find($super_id);
			$this->assign('sort', $sort);
			$this->assign('list', $sort_arr);
			$this->assign('title', '书籍分类列表');
			$this->display('./Public/admin/booksort.html');
		}else{
			redirect('?s=admin/book/index/sort_id/'.$super_id);
		}
	}

	// 显示添加分类
	public function add(){
		$list = get_root_sort();
		$this->assign('list', $list);
		$this->assign('title','添加书籍分类');
		$this->assign('action','do_add');
		$this->display('./Public/admin/booksort_add.html');
	}

	// 添加书籍分类
	public function do_add(){
		$sort_obj	= M('Book_sort');
		$data		= $this->get_form_attr($_POST, "add");
		$result		= $sort_obj->add($data);
		if($result){ $this->update_path($sort_obj->getLastInsID(), $_POST['super_id']); }
		
		$this->show_result_msg($result, '分类添加成功！', '分类添加失败！', '?s=admin/booksort/');
	}

	// 显示修改书籍分类
	public function update(){
		$sort_id	= (int)$_GET['sort_id'];
		$sort_obj	= M('Book_sort');
		$sort		= $sort_obj->find($sort_id);
		$this->assign('sort', $sort);
		// 获取分类列表，如检测设置中的目录层级，如为1则不显示
		//$list = $this->get_sub_sort(0);
		$list = get_root_sort($sort_id);
		$this->assign('list', $list);

		$this->assign('action','do_update');
		$this->display('./Public/admin/booksort_add.html');
	}

	// 修改书籍分类
	public function do_update(){
		$sort_obj	= M('Book_sort');
		$data		= $this->get_form_attr($_POST, "update");
		$result		= $sort_obj->save($data);
		if($result){ $this->update_path($_POST['sort_id'], $_POST['super_id']); }
		
		$this->show_result_msg($result, '分类修改成功！', '分类修改失败！', '?s=admin/booksort/');
	}

	// 删除书籍分类
	public function delete(){
		$sort_id	= (int)$_GET['sort_id'];
		$sort_obj	= M('Book_sort');
		$sort		= $sort_obj->find($sort_id);
		if($sort){
			$result	= $sort_obj->delete($sort_id);
			if($result){ $this->update_delete($sort_id, $sort['path']); }
		}
		$this->show_result_msg($result, '分类删除成功！', '分类删除失败！');
	}
	
	// 获取子分类列表
	protected function get_sub_sort($super_id){
		$sort_obj	= M('Book_sort');
		$list		= $sort_obj->field('sort_id, sort_name')->where('super_id='.$super_id)->select();
		$array		= Array();
		foreach($list as $sort){
			$id			= $sort['sort_id'];
			$array[$id]	= $sort['sort_name'];
		}
		return $array;
	}

	// 添加或修改书籍分类时，从POST表单中提取并检测需要的字段信息，返回一个数组
	protected function get_form_attr($array, $type="add"){
		$data = Array(
			'sort_name'	=> safe_str($array['sort_name']),
			'super_id'	=> (int)$array['super_id'],
			'sort_order'=> (int)$array['sort_order'],
			'sort_dir'	=> safe_str($array['sort_dir'])
		);
		if($type == "update"){
			$data['sort_id']	= (int)$array['sort_id'];
		}
		return $data;
	}

	// 设置、修改分类的path信息
	protected function update_path($sort_id, $super_id){
		$sort_obj	= M('Book_sort');
		if($super_id > 0){
			$sort	= $sort_obj->find($super_id);
			$path	= $sort['path'].','.$sort_id;
		}else{
			$path	= '0,'.$sort_id;
		}
		$sort_obj->data(array('sort_id'=>$sort_id, 'path'=>$path))->save();

		$list = $sort_obj->where('`super_id`='.$sort_id)->select();
		foreach($list as $item){
			$data	= array('sort_id' => $item['sort_id'], 'path' => $path.','.$item['sort_id']);
			$sort_obj->data($data)->save();
		}
	}

	// 更新删除后分类path信息
	protected function update_delete($sort_id, $path){
		$sort_obj	= M('Book_sort');
		$list		= $sort_obj->where("`path` like '$path%'")->select();
		$search		= ','.$sort_id.',';
		foreach($list as $item){
			$n_path	= str_replace($search, ',', $item['path']);
			$item['path']	= $n_path;
			if(count(explode(',', $n_path)) == 2){
				$item['super_id']	= 0;
			}
			$sort_obj->data($item)->save();
		}
	}
}
?>
