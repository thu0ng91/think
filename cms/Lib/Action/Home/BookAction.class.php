<?php
/**
 * 书籍列表模块（含书籍搜索）
 *
 * @author flashfxp
 */
class BookAction extends MainAction {
	// 书籍首页
	public function index(){
		$book_id	= (int)$_GET['id'];
		$book		= book_search(array('book.book_id'=>$book_id), true);
		if($book){
			$book['chapter_detail'] = $this->chapter_info($book['last_chapterid']);
			$book['ave_score']		= round($book['ping_score']/$book['ping_num'], 1);
			$this->add_visit($book_id);	// 浏览数加1
			$this->assign('book', $book);
			$this->display(TEMPLATE_PATH.'/home/book.html');
		}else{
			$this->error('书籍不存在！');
		}
	}

	// 章节列表
	public function menu(){
		$book_id	= (int)$_GET['id'];
		$book		= book_search(array('book.book_id'=>$book_id), true);
		if($book){
			$this->assign('book',$book);
			$chapter_obj	=M('Book_chapter');
			$list			= $chapter_obj->where('book_id='.$book_id)->select();
			
			foreach($list as $ch){
				if($ch['chapter_type'] == 0){
					$vid = $ch['volume_id'];
					if($vid == 0 && empty($chapter[0])){
						$volume[0]	= array('chapter_id'=>0, 'chapter_name'=>'正文');
					}
					$chapter[$vid][]= $ch;
				}else{
					$vid = $ch['chapter_id'];
					$volume[$vid]	= $ch;
				}
			}
			sort($volume);
			$this->assign('chapter', $chapter);
			$this->assign('volume', $volume);
			$this->display(TEMPLATE_PATH.'/home/book_menu.html');
		}else{
			$this->error('书籍不存在！');
		}
	}

	// 显示章节内容（含分卷阅读）
	public function read(){
		$chapter_id		= (int)$_GET['id'];
		$chapter_obj	= M('Book_chapter');
		$chapter		= $chapter_obj->find($chapter_id);
		if($chapter){
			if($chapter['is_vip']){
				redirect(VLink($chapter, 1));
			}
			$book_id	= $chapter['book_id'];
			$book		= book_search(array('book.book_id'=>$book_id), true);
			$this->assign('book', $book);
			$this->assign('chapter', $chapter);

			$volume_id	= $chapter['volume_id'];
			if($chapter['chapter_type'] == 0){		// 单章阅读
				if($volume_id > 0){
					$volume	= $chapter_obj->find($volume_id);
					$this->assign('volume', $volume);
				}
				$this->assign('next', get_next_chapter($book, $chapter_id, 'next'));
				$this->assign('prev', get_next_chapter($book, $chapter_id, 'prev'));
				$this->display(TEMPLATE_PATH.'/home/book_chapter.html');
			}else{									// 分卷阅读
				$list	= $chapter_obj->where('volume_id='.$chapter_id)->select();
				$this->assign('list', $list);
				$this->display(TEMPLATE_PATH.'/home/book_volume.html');
			}
		}else{
			$this->error('章节不存在！');
		}
	}

	// 全文阅读
	public function full(){
		$book_id= (int)$_GET['id'];
		$book	= book_search(array('book.book_id'=>$book_id), true);
		if(!$book){ $this->error('书籍不存在！'); }
		
		$this->assign('book', $book);
		$chapter_obj = M('Book_chapter');
		//$list = $chapter_obj->where('book_id='.$book_id.' and chapter_type=0')->select();
		$list = $chapter_obj->where('book_id='.$book_id)->select();
		$this->assign('list', $list);
		$this->display(TEMPLATE_PATH.'/home/book_full.html');
	}

	// 书籍投票
	public function vote(){
		$book_id	= (int)$_REQUEST['id'];
		$this->add_vote($book_id);
		$this->success('投票成功！');
	}

	// 书籍下载
	public function down(){
		$bid		= (int)$_REQUEST['id'];
		$book		= book_search(array('book.book_id'=>$bid), true);
		$list		= book_list('63,10,'.$bid);
		$this->assign('list', $list);
		$this->assign('book', $book);
		$this->display(TEMPLATE_PATH.'/home/book_down.html');
	}

	// 书籍分类首页
	public function lists(){
		$sort_id	= (int)$_GET['id'];
		$where		= $sort_id < 1 ? '`sort_dir`="'.$_GET['id'].'"' : '`sort_id`='.$sort_id;
		$sort_obj	= M('Book_sort');
		$sort		= $sort_obj->where($where)->find();
		$this->assign('sort', $sort);
		if(C('book.has_channel')){
			$this->display(TEMPLATE_PATH.'/home/book_channel.html');
		}else{
			$map['book_sort.path']	= array('like', $sort['path'].'%');
			$result		= book_search($map, false, C('book.perpage_book'));
			//$result		= book_search(array('book.sort_id'=>$sort['sort_id']), false, C('book.perpage_book'));
			$this->assign($result);
			$this->display(TEMPLATE_PATH.'/home/book_lists.html');
		}
	}

	// 书籍分类列表页
	public function show(){
		$sort_id	= (int)$_GET['id'];
		$where		= $sort_id < 1 ? '`sort_dir`="'.$_GET['id'].'"' : '`sort_id`='.$sort_id;
		$sort_obj	= M('Book_sort');
		$sort		= $sort_obj->where($where)->find();

		$map['book_sort.path']	= array('like', $sort['path'].'%');
		$result		= book_search($map, false, C('book.perpage_book'));
		//$result		= book_search(array('book.sort_id'=>$sort['sort_id']), false, C('book.perpage_book'));
		$this->assign($result);
		$this->assign('sort', $sort);
		$this->display(TEMPLATE_PATH.'/home/book_lists.html');
	}

	// 书籍搜索（暂时只根据书籍名称搜索）
	public function search(){
		$keyword	= safe_str($_REQUEST['keyword']);
		$tid		= (int)$_REQUEST['tid'];
		$key		= $tid == 1 ? 'book_name' : 'author';
		$map[$key]	= array('like', '%'.$keyword.'%');
		$result		= book_search($map, false, C('book.perpage_book'));
		C('book.search_history') && $this->search_history($tid, $keyword, count($result['list']));
		$this->assign($result);
		$this->assign('keyword', $keyword);
		$this->display(TEMPLATE_PATH.'/home/book_search.html');
	}

	// 添加搜索记录
	protected function search_history($tid, $keyword, $num){
		$search_obj	= M('Book_search');
		$data		= array('tid'=>$tid, 'keyword'=>$keyword);
		$search		= $search_obj->where($data)->find();
		if($search){
			$search_obj->setInc('snum','sid='.$search['sid']);
		}else{
			$data['result'] = $num > 0 ? 1 : 0;
			$search_obj->add($data);
		}
	}

	// 浏览次数递增
	protected function add_visit($book_id){
		check_period_amount();	// 检测是否一天、一周、一月的开始
		$book_obj	= M('Book');
		$book_obj->execute('update `book` set `day_visit`=`day_visit`+1,`week_visit`=`week_visit`+1,`month_visit`=`month_visit`+1,`all_visit`=`all_visit`+1 where `book_id`='.$book_id);
	}

	// 获取章节预览信息
	protected function chapter_info($chapter_id){
		if($chapter_id < 1){ return ''; }
		$chapter_obj	= M("Book_chapter");
		$chapter_obj->find($chapter_id);
		return msubstr($chapter_obj->chapter_detail, 0, 120);
	}
}
?>
