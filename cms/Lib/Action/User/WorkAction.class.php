<?php
/**
 * 个人文集用户操作模块
 *
 * @author flashfxp
 */
class WorkAction extends BaseAction {
	private $per_page = 20;

	// 文集管理首页
    public function index(){
		$work_obj		= M('Book_works');
		$user_id		= Cookie::get('user_id');
		$work			= $work_obj->where(array('user_id'=>$user_id))->find();
		$this->assign('work', $work);
		if(!empty($work)){
			$sort_obj	= M('Book_works_sort');
			$list		= $sort_obj->where(array('work_id'=>$work['work_id']))->select();
			$this->assign('list', $list);
		}
		$this->assign('title','我的文集');
		$this->display(TEMPLATE_PATH.'/user/work.html');
	}

	// 显示添加个人文集
	public function add(){
		$this->assign('title', '建立个人文集');
		$this->assign('action', 'do_add');
		$this->display(TEMPLATE_PATH.'/user/work_add.html');
	}

	// 添加个人文集
	public function do_add(){
		$works_obj	= M('Book_works');
		$data		= $this->get_form_attr($_POST, 'add');
		$result		= $works_obj->add($data);
		$this->show_result_msg($result, '文集建立成功！', '文集建立失败！','?s=user/work');
	}

	// 显示修改个人文集
	public function update(){
		$work_id	= (int)$_GET['work_id'];
		$works_obj	= M('Book_works');
		$work		= $works_obj->find($work_id);
		$this->assign('work', $work);
		$this->assign('title','修改个人文集');
		$this->assign('action', 'do_update');
		$this->display(TEMPLATE_PATH.'/user/work_add.html');
	}

	// 修改个人文集
	public function do_update(){
		$this->check_auth();
		$works_obj	= M('Book_works');
		$data		= $this->get_form_attr($_POST, 'update');
		$result = $works_obj->save($data);
		$this->show_result_msg($result, '文集修改成功！', '文集修改失败！','?s=user/work');
	}

	// 获取并检测表单中需要的文集参数，返回数组
	protected function get_form_attr($array, $type="add"){
		$data = Array(
			'work_name'			=> safe_str($array['work_name']),
			'work_template'		=> (int)$array['work_template'],
			'work_pic'			=> safe_str($array['work_pic']),
			'work_description'	=> safe_str($array['work_description'])
		);
		if($type == "add"){
			$data['user_id']	= Cookie::get('user_id');
			$data['user_name']	= Cookie::get('user_name');
		}else{
			$data['work_id']	= (int)$array['work_id'];
		}
		return $data;
	}
	
	// 检测work_id是否存在及是否属于当前用户
	protected function check_auth(){
		$user_id	= Cookie::get('user_id');
		$work_id	= (int)$_REQUEST['work_id'];
		$works_obj	= M('Book_works');
		$works_obj->find($work_id);
		if($user_id != $works_obj->user_id){
			$this->error('非法操作！');
		}
	}
}
?>
