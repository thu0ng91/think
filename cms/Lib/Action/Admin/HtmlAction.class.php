<?php
/**
 * 静态文件生成模块
 *
 * @author flashfxp
 */
class HtmlAction extends BaseAction {
	private $next_page;		// 下一页

	public function _initialize(){
		import("@.Action.MainAction");
		$main	= new MainAction();
	}

	// 生成全站首页
    public function index(){
		$this->display('./Public/admin/html.html');
	}

	// 生成书籍首页
	public function book(){
		$where = $extra	= '';
		if(isset($_REQUEST['book_id'])){	// 单本书籍生成
			$bid	= intval($_REQUEST['book_id']);
			$where	= 'book.book_id='.$bid;
			$extra	= 'book_id/'.$bid.'/';
		}
		if(isset($_REQUEST['by_name'])){	// 批量生成（按编号、按更新时间、全部书籍）
			$by_name = $_REQUEST['by_name'];
			$extra	 = 'by_name/'.$by_name.'/';
			switch($by_name){
				case 'id':
					$from	= (int)$_REQUEST['from_id'];
					$to		= (int)$_REQUEST['to_id'];
					if($from <= 0 || $from > $to){
						$this->error('开始、结束序号不正确！');
					}else{
						$where	 = 'book.book_id>'.$from.' and book.book_id<'.$to;
						$extra	.= 'from_id/'.$from.'/to_id/'.$to.'/';
					}
					break;
				case 'time':
					$from		= $_REQUEST['from_time'];
					$to			= $_REQUEST['to_time'];
					if(strpos($from, '-')){
						$from	= strtotime($from);
						$to		= strtotime($to);
					}

					if($from <= 0 || $from > $to){
						$this->error('开始、结束时间不正确！');
					}else{
						$where	 = 'book.last_update>'.$from.' and book.last_update<'.$to;
						$extra	.= 'from_time/'.$from.'/to_time/'.$to.'/';
					}
					break;
				case 'all':
					$where	 = '';
					break;
				default:
					$this->error('参数提交错误！');
			}
		}
		if(isset($_REQUEST['type'])){		// 生成选项：html full
			$type		= $_REQUEST['type'];
			if(is_array($type)){
				$extra	.= 'type/'.implode(',', $type).'/';
			}else{
				$extra	.= 'type/'.$type.'/';
				$type	= explode(',', $type);
			}
		}
		if(empty($type)){
			$this->error('参数非法，请选择要生成的选项！');
		}else{
			$this->create($where, $extra, $type, 1, $bid);
		}
	}

	// 页面生成（html, txt, zip, umd)
	protected function create($where, $extra, $type, $perpage, $book_id){
		$list	= $this->get_book($where, $perpage);
		if(!$list){
			$jump_url	= empty($book_id) ? '?s=admin/html' : '?s=admin/chapter/index/book_id/'.$book_id;
			$this->assign('jumpUrl', $jump_url);
			$this->success('操作成功！');
		}
		
		foreach($list as $book){
			$this->create_book($book, $type);
		}
		$this->assign('jumpUrl', '?s=admin/html/book/'.$extra.'page/'.$this->next_page);
		$this->success('开始生成第 '.$this->next_page.' 页');
	}

	// 生成单本书籍的首页、列表页、全文阅读页、下载页
	public function for_auto($book_id, $type, $chapters){
		$book	= $this->get_book('book.book_id='.$book_id, 1, true);
		if($book){
			$this->create_book($book, $type, $chapters);
		}
	}

	// 生成书籍静态页面（含书籍首页、章节列表页、章节内容页、全文阅读页、下载页）
	protected function create_book($book, $type, $chapters=null){
		$chapter_obj= M('Book_chapter');
		$list		= $chapter_obj->where('book_id='.$book['book_id'])->order('chapter_id')->select();
		$this->assign('book', $book);
		$this->assign('list', $list);
		
		// 生成书籍首页
		in_array('index', $type) && $this->buildFile(get_filename($book, 'index'), TEMPLATE_PATH.'/home/book.html');

		// 生成书籍章节列表页
		in_array('menu', $type) && $this->create_menu($book, $list);

		// 生成全文阅读页面
		in_array('full', $type) && $this->buildFile(get_filename($book, 'full'), TEMPLATE_PATH.'/home/book_full.html');

		// 生成电子书下载页面
		in_array('down', $type) && $this->buildFile(get_filename($book, 'down'), TEMPLATE_PATH.'/home/book_down.html');

		// 生成章节内容页
		if(in_array('read', $type)){
			if(!empty($chapters)){
				$list = $chapters;
			}
			foreach($list as $chapter){
				if($chapter['chapter_type'] == 0){
					$this->create_chapter($chapter, $book);	// 普通章节
				}else{
					$this->create_volume($chapter, $book);	// 分卷章节
				}
			}
		}
	}

	// 生成章节列表页
	protected function create_menu($book, $list){
		foreach($list as $ch){
			$vid = $ch['volume_id'];
			if($ch['chapter_type'] == 0){
				$chapter[$vid][]= $ch;
			}else{
				$volume[]		= $ch;
			}
		}
		empty($volume) && $volume[0] = array('chapter_id'=>0, 'chapter_name'=>'正文');

		$this->assign('chapter', $chapter);
		$this->assign('volume', $volume);
		$this->buildFile(get_filename($book, 'menu'), TEMPLATE_PATH.'/home/book_menu.html');
	}

	// 生成普通章节
	protected function create_chapter($chapter, $book=''){
		if($chapter['volume_id'] > 0){
			$chapter_obj	= M('Book_chapter');
			$volume			= $chapter_obj->find($chapter['volume_id']);
			$this->assign('volume', $volume);
		}

		$chapter_id	= $chapter['chapter_id'];
		$book_id	= $chapter['book_id'];
		if(!is_array($book)){
			$book	= $this->get_book('book.book_id='.$book_id, 1, true);
		}
		
		$this->assign('book', $book);
		$this->assign('chapter', $chapter);
		$this->assign('next', get_next_chapter($book_id, $chapter_id, 'next'));
		$this->assign('prev', get_next_chapter($book_id, $chapter_id, 'prev'));
		$this->buildFile(get_filename($book, 'read', $chapter), TEMPLATE_PATH.'/home/book_chapter.html');
	}

	// 生成分卷阅读
	protected function create_volume($chapter, $book=''){
		$chapter_obj= M('Book_chapter');
		$list		= $chapter_obj->where('volume_id='.$chapter['chapter_id'])->select();
		if(!is_array($book)){
			$book	= $this->get_book('book.book_id='.$book_id, 1, true);
		}
		$this->assign('book', $book);
		$this->assign('chapter', $chapter);
		$this->assign('list', $list);
		$this->buildFile(get_filename($book, 'read', $chapter), TEMPLATE_PATH.'/home/book_volume.html');
	}

	// 获取书籍信息
	protected function get_book($where, $perpage, $one=false){
		$book_obj	= M('Book');
		$limit		= $this->get_limit($perpage);
		$list		= $book_obj	->field('book.*, book_sort.sort_name, book_sort.sort_dir')
								->join(' book_sort on book.sort_id=book_sort.sort_id')
								->where($where)->order('book.book_id')->limit($limit)->select();
		return $one ? $list[0] : $list;
	}

	protected function get_limit($perpage){
		$page	= empty($_GET['page']) ? 0 : (int)$_GET['page'];
		$start	= $page * $perpage;
		$this->next_page = $page + 1;
		return  $start.','.$perpage;
	}

	// 生成书籍分类列表静态页面
	public function sort(){
		$sort_obj	= M('Book_sort');
		if(empty($_REQUEST['sort_id'])){
			$sort_list	= $sort_obj->select();
			foreach($sort_list as $item){
				$this->create_sort($item);
			}
		}else{
			$sort_id	= (int)$_GET['sort_id'];
			$item		= $sort_obj->find($sort_id);
			$this->create_sort($item);
		}
		$this->success('操作成功！');
	}
	
	// 生成单一分类列表页(频道首页)
	protected function create_sort($sort){
		$sort_id	= $sort['sort_id'];
		$this->assign('sort', $sort);

		$_GET['p']	= 1;
		$result		= book_search(array('book.sort_id'=>$sort_id), false, C('book.perpage'));

		// 生成分类频道首页，必须放前面2行之下，原因未知
		C('book.has_channel') && $this->buildFile(get_filename($sort, 'lists'), TEMPLATE_PATH.'/home/book_channel.html');

		while($result['list']){
			$book	= $result['list'][0];
			// 分页信息中url静态化
			preg_match_all("/href='.+?&p=([0-9]+)'/", $result['page'], $matches);
			foreach($matches[0] as $key => $value){
				$result['page'] = str_replace($value, "href='".BU($book, 'show', $matches[1][$key])."'", $result['page']);
			}
			
			$this->assign($result);
			$this->buildFile(get_filename($book, 'show', $_GET['p']), TEMPLATE_PATH.'/home/book_lists.html');

			if(count($result['list']) < C('book.perpage')){
				break;
			}else{
				$_GET['p'] += 1;
				$result	= book_search(array('book.sort_id'=>$sort_id), false, C('book.perpage'));
			}
		}
	}

	// 生成全站首页
	public function main(){
		$this->buildFile('./default.html', TEMPLATE_PATH.'/index.html');
		$this->success('操作成功！');
	}

	// 生成排行榜页面
	public function top(){
		import("@.Action.Home.TopAction");	// 导入当前项目下 Lib/Action/Home/TopAction 类
		$tops	= new TopAction();
		foreach($tops->type_array as $tid => $type){
			$where = $tid == 41 ? array('book.is_full'=>1) : '';
			list($order, $type_key, $type_name) = explode('|', $type);
			$result	= book_search($where, FALSE, C('book.perpage_top'), $order.' desc');
			$this->assign($result);
			$this->assign('type_name', $type_name);
			$this->assign('type_key', $type_key);
			$this->buildFile(get_filename($tid, 'top'), TEMPLATE_PATH.'/home/top.html');
		}
		$this->success('操作成功！');
	}

	// 静态文件生成
	public function buildFile($filename, $templateFile){
        $content	= $this->fetch($templateFile);
        if(!is_dir(dirname($filename))){	// 如果静态目录不存在 则创建
            mk_dir(dirname($filename));
		}
        return file_put_contents($filename, $content);
    }
}
?>
