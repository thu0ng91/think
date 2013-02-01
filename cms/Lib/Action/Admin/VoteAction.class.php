<?php
/**
 * 投票管理模块
 *
 * @author flashfxp
 */
class VoteAction extends BaseAction {
    // 投票列表
	public function index(){
		import("ORG.Util.Page");			// 导入分页类
		$vote_obj	= M('Book_vote');
		$perpage	= 20;					// 每页显示投票数
		$page_no	= empty($_GET['page']) ? "1" : (int)$_GET['page'];
		$where_arr	= $this->get_where_array($_GET);
		$list		= $vote_obj	->field('book_vote.*, book.book_name')
								->join(' book on book.book_id=book_vote.book_id')
								->where($where_arr)->order('book_vote.vote_id desc')
								->page($page_no.','.$perpage)->select();
		$this->assign('list', $list);

		$total      = $vote_obj->where($where_arr)->count();	// 查询满足要求的总记录数
		$page_obj	= new Page($total, $perpage);	// 实例化分页类传入总记录数和每页显示的记录数
		$page_show	= $page_obj->show();	// 分页显示输出
		$this->assign('page', $page_show);	// 赋值分页输出
		$this->assign('title', '投票管理');
		$this->display('./Public/admin/vote.html');
	}

	// 显示添加投票
	public function add(){
		$vote['book_id']	= (int)$_GET['book_id'];
		$this->assign('vote', $vote);
		$this->assign('title', '添加投票');
		$this->assign('action', 'do_add');
		$this->display('./Public/admin/vote_add.html');
	}

	// 添加投票
	public function do_add(){
		$vote_obj	= M('Book_vote');
		$data		= $this->get_form_attr($_POST, 'add');
		$result		= $vote_obj->add($data);
		if($result){
			$data2['vote_id']	= $vote_obj->getLastInsID();	// 获取最后一次插入的id 
			$vote_state_obj		= M('Book_vote_state');
			$vote_state_obj->add($data2);
		}
		$this->show_result_msg($result, '投票建立成功！', '投票建立失败！', '?s=admin/vote');
	}

	// 显示修改投票
	public function update(){
		$vote_id	= (int)$_GET['id'];
		$vote_obj	= M('Book_vote');
		$vote		= $vote_obj->find($vote_id);
		$this->assign('vote', $vote);
		$this->assign('title', '修改投票');
		$this->assign('action', 'do_update');
		$this->display('./Public/admin/vote_add.html');
	}

	// 修改投票，开始投票后修改应该有限制，todo
	public function do_update(){
		$vote_obj	= M('Book_vote');
		$data		= $this->get_form_attr($_POST, 'update');
		$result		= $vote_obj->save($data);
		$this->show_result_msg($result, '投票修改成功！', '投票修改失败！', '?s=admin/vote');
	}

	// 删除投票
	public function delete(){
		$vote_id	= (array)$_REQUEST['id'];
		$vote_obj	= M('Book_vote');
		$state_obj	= M('Book_vote_state');
		foreach($vote_id as $vid){
			$vid	= (int)$vid;
			if($vid < 1){ continue; }
			if($vote_obj->delete($vid)){
				$state_obj->delete($vid);
			}
		}
		$this->show_result_msg(true, '操作成功！', '', '?s=admin/vote');
	}

	// 投票审核
	public function check(){
		$this->set_value('Book_vote', 'vote_id', $_REQUEST['id'], 'if_check', $_REQUEST['value']);
	}

	// 投票开关
	public function hide(){
		$this->set_value('Book_vote', 'vote_id', $_REQUEST['id'], 'if_display', $_REQUEST['value']);
	}

	// 获取post提交的投票参数数组
	protected function get_form_attr($array, $type="add"){
		$data = Array(
			'book_id'		=> (int)$array['book_id'],
			'start_time'	=> strtotime($array['start_time']),
			'end_time'		=> strtotime($array['end_time']),
			'subject'		=> safe_str($array['subject']),
			'description'	=> safe_str($array['description']),
			'if_display'	=> (int)$array['if_display'],
			'need_login'	=> (int)$array['need_login'],
			'multi_select'	=> (int)$array['multi_select']
		);
		$num = 0;
		for($i=0; $i<10; ){
			$key1		= 'item'.++$i;
			if($array[$key1] != ""){
				$key2	= 'item'.++$num;
				$data[$key2]	= $array[$key1];
			}
		}
		$data['use_items']		= $num;

		if($type == "add"){
			$data['poster_id']	= Cookie::get('admin_id');
			$data['poster']		= Cookie::get('admin_name');
			$data['post_time']	= time();
		}else{
			$data['vote_id']	= (int)$array['vote_id'];
		}
		return $data;
	}

	// 获取查询参数数组
	protected function get_where_array($array){
		$where_arr = Array();
		if(!empty($array['book_id'])){
			$where_arr['book_vote.book_id']		= (int)$array['book_id'];
		}
		if(!empty($array['user_id'])){
			$where_arr['book_vote.poster_id']	= (int)$array['user_id'];
		}
		return $where_arr;
	}
}
?>
