<?php
/**
 * 用户投票模块
 *
 * @author flashfxp
 */

class VoteAction extends MainAction {
	// 已参与投票信息的cookie name
	private $vote_cookie_name	= 'has_vote';

	// 显示投票
	public function index(){
		$vote_id	= (int)$_REQUEST['id'];
		$result		= $this->check_vote($vote_id);
		if(false == $result['flag']){
			$this->error($result['data']);
		}
		$vote		= $result['data'];
		$state		= M('Book_vote_state')->find($vote_id);
		for($i=1; $i<=$vote['use_items'];$i++){
			$list[$i]	= array('name'=>$vote['item'.$i],'state'=>$state['state'.$i]);
		}
		$vote['type']	= $vote['multi_select'] ? 'checkbox' : 'radio';
		$this->assign('vote', $vote);
		$this->assign('list', $list);
		$this->display(TEMPLATE_PATH.'/home/vote.html');
	}

	// 显示某本书籍的投票列表
	public function lists(){
		$book_id	= (int)$_REQUEST['book_id'];
		$vote_obj	= M('Book_vote');
		$list		= $vote_obj->where('book_id='.$book_id)->select();
		foreach($list as $vote){
			$tmp	= $this->check_vote($vote);
			if($tmp['flag']){
				$result[]	= $tmp['data'];
			}
		}
		echo json_encode($result);exit;
	}

	// 参与投票，ajax提交
	public function add(){
		$vote_id	= (int)$_REQUEST['vote_id'];
		$ajax		= isset($_REQUEST['ajax']) ? true : false;
		$result		= $this->check_vote($vote_id);
		if(false == $result['flag']){
			$this->error($result['data'], $ajax);
		}
		$this->check_is_voted($vote_id, $ajax);

		$state_obj	= M('Book_vote_state');
		$items		= (array)$_REQUEST['items'];
		foreach($items as $key){
			if($key < 1){ continue; }
			$result	= $state_obj->setInc('state'.$key, 'vote_id='.$vote_id);
		}
		if($result){
			add_to_cookie($this->vote_cookie_name, $vote_id);
			$vote_obj	= M('Book_vote');	// 投票总数加一
			$result	= $vote_obj->setInc('vote_num', 'vote_id='.$vote_id);
			$this->success('投票成功！', $ajax);
		}else{
			$this->success('投票失败！', $ajax);
		}
	}

	// 显示投票结果
	public function result(){
		$vote_id	= (int)$_REQUEST['id'];
		$vote_obj	= M('Book_vote');
		$vote		= $vote_obj	->join(' book_vote_state on book_vote_state.vote_id=book_vote.vote_id')
								->where('book_vote.vote_id='.$vote_id)->find();
		if(!$vote){
			$this->error('投票不存在！');
		}
		for($i=1;$i<=$vote['use_items'];$i++){
			$list[$i]	= array('name'=>$vote['item'.$i],'state'=>$vote['state'.$i]);
		}
		$this->assign('vote', $vote);
		$this->assign('list', $list);
		$this->assign('title', '投票结果');
		$this->display(TEMPLATE_PATH.'/home/vote_result.html');
	}

	// 检测投票是否开放，是则返回该投票数组，否则返回false
	protected function check_vote($vote_id){
		$vote_obj	= M('Book_vote');
		$vote_arr	= $vote_obj->find($vote_id);
		// 投票不存在
		if(!$vote_arr){
			$result['flag']	= false;
			$result['data']	= '投票不存在！';
			return $result;
		}
		// 投票没通过审核或暂时不开放投票
		if(!$vote_obj->if_check || !$vote_obj->if_display){
			$result['flag']	= false;
			$result['data']	= '不允许投票！';
			return $result;
		}
		// 不在允许投票时间内
		$tm	= time();
		if($tm < $vote_obj->start_time || $tm > $vote_obj->end_time){
			$result['flag']	= false;
			$result['data']	= '不开放投票！';
			return $result;
		}
		
		$result['flag']	= true;
		$result['data']	= $vote_arr;
		return $result;
	}

	// 检测是否已允许投票
	protected function check_is_voted($vote_id, $ajax){
		// 需要登录才能参与投票
		if($vote_obj->need_login && Cookie::get('user_id') < 1){
			$this->error('需要登录才能投票！', $ajax);
		}
		// 是否已参与投票
		if(check_in_cookie($this->vote_cookie_name, $vote_id)){
			$this->error('你已经投过票了！', $ajax);
		}
	}
}
?>
