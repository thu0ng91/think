<?php
/**
 * 一些与前台相关的，根据设置可能需要登录才能进行的操作
 *
 * @author flashfxp
 */
class AjaxAction extends Action {
    // 书籍下载
	public function down(){
		$bid		= (int)$_REQUEST['id'];
		$book		= book_search(array('book.book_id'=>$bid), true);
		$type		= $_REQUEST['type'];
		if($type == 'txt' && C('book.ebook_txt_zip')){
			$type	= 'zip';
		}
		$filename	= get_filename($book, $type);
		if(file_exists($filename)){
		/*
			header('Content-type: application/zip');
			header('Content-Disposition: attachment; filename="'.basename($filename).'"');
			readfile($filename);
		*/
			redirect(get_filename($book, $type, 0, true));
		}else{
			$this->error('文件不存在！');
		}
	}

	// 检测登录状态
	public function checklogin(){
		$result	= array();
		if(Cookie::get('user_id') > 0){
			$result['status'] = true;
			$result['username'] = Cookie::get('user_name');
		}else{
			$result['status'] = false;
		}
		die(json_encode($result));
	}

	// 投推荐票
	public function recommend(){	// 需要限制每天投票数，todo
		$result	= array();
		if(Cookie::get('user_id') > 0){
			check_period_amount();	// 检测是否一天、一周、一月的开始
			$book_id	= (int)$_REQUEST['id'];
			$book_obj	= M('Book');
			$book		= $book_obj->find($book_id);
			$book_obj->execute('update `book` set `day_vote`=`day_vote`+1,`week_vote`=`week_vote`+1,`month_vote`=`month_vote`+1,`all_vote`=`all_vote`+1 where `book_id`='.$book_id);
			$result['status']	= true;
			$result['info']		= '推荐票投票成功！';
		}else{
			$result['status']	= false;
			$result['info']		= '请先登录！';
		}
		die(json_encode($result));
	}

	// 网站广告显示、统计模块
	public function ads(){
		$id		= (int)$_REQUEST['id'];
		$ad_obj	= M('ads');
		$ad		= $ad_obj->find($id);
		if($ad){
			$ad_obj->setInc('hit_num','aid='.$id);
			redirect($ad['url']);
		}else{
			redirect(__ROOT__.'/');
		}
	}

	// 书籍评分
	public function score(){
		$cookie_score	= 'has_ping';
		$book_id	= (int)$_REQUEST['id'];
		if(check_in_cookie($cookie_score, $book_id)){
			$this->error('您已参与评分！', true);
		}
		$book_obj	= M('Book');
		$value		= (int)$_REQUEST['value'];
		$book		= $book_obj->find($book_id);
		if($book){
			$data	= array(
				'book_id'	=> $book_id,
				'ping_num'	=> $book['ping_num'] + 1,
				'ping_score'=> $book['ping_score'] + $value
			);
			$result	= $book_obj->data($data)->save();
			if($result){
				add_to_cookie($cookie_score, $book_id);
				$data['status']		= 2;
				$data['ave_score']	= round($data['ping_score']/$data['ping_num'], 1);
				die(json_encode($data));
			}else{
				$this->error('评分失败！', true);
			}
		}else{
			$this->error('书籍不存在', true);
		}
	}

	// 验证码获取
	public function vcode(){
		import('ORG.Util.Image');
		Image::buildImageVerify();
	}

	// 获取用户信息
	public function userinfo(){
		$user_id= (int)Cookie::get('user_id');
		$user	= M('User_info')->find($user_id);
		if($user){
			$user['status'] = 2;
			die(json_encode($user));
		}else{
			$this->error('非法操作#user');
		}
	}
}
?>
