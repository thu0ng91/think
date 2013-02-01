<?php
/**
 * 电子书批量生成模块
 *
 * @author flashfxp
 */
class EbookAction extends BaseAction {
	private $next_page;		// 下一页

	// 电子书批量生成首页
    public function index(){
		$this->display('./Public/admin/ebook.html');
	}

	// 电子书生成选项
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
		if(isset($_REQUEST['type'])){		// 生成选项：zip txt umd
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

	// 单本电子书生成
	public function create_one($book, $type){
		if(!is_array($book)){
			$book	= $this->get_book('book.book_id='.$book, 1, true);
		}
		in_array('txt', $type) && $this->txt($book);
		in_array('umd', $type) && $this->umd($book);
		in_array('epub', $type) && $this->epub($book);
	}

	// 电子书生成（txt, zip, umd)
	protected function create($where, $extra, $type, $perpage, $book_id){
		$list	= $this->get_book($where, $perpage);
		if(!$list){
			$jump_url	= empty($book_id) ? '?s=admin/ebook' : '?s=admin/chapter/index/id/'.$book_id;
			$this->assign('jumpUrl', $jump_url);
			$this->success('操作成功！');
		}
		foreach ($list as $book) {
			$this->create_one($book, $type);
		}
		$this->assign('jumpUrl', '?s=admin/ebook/book/'.$extra.'page/'.$this->next_page);
		$this->success('开始生成第 '.$this->next_page.' 页');
	}

	// 生成txt文件(含zip)
	protected function txt($book){
		$chapter_obj= M('Book_chapter');
		$list		= $chapter_obj->where('book_id=' . $book['book_id'] . ' and chapter_type=0')->select();
		$contents	= $book['book_name'] . ' 作者：' . $book['author'];
		
		foreach ($list as $chapter) {
			$contents .= "\n\n" . $chapter['chapter_name'] . "\n\n" . $chapter['chapter_detail'];
		}
		$contents	= iconv('utf-8', 'gbk', $contents); // 转为gbk编码
		$filename	= get_filename($book, 'txt');
		$result		= write_to_file($filename, $contents);

		if($result && C('book.ebook_txt_zip')){		// 同时生成zip文件
			Vendor('pclzip');
			$archive	= new PclZip(get_filename($book, 'zip'));
			$result		= $archive->create($filename, PCLZIP_OPT_REMOVE_PATH, dirname($filename), PCLZIP_OPT_ADD_PATH, './');
		}
	}

	// 生成umd电子书
	protected function umd($book){
		$book_info	= Array(
			'title'	=> $book['book_name'],
			"author"=> $book['author'],
			"cover"	=> $book['image_url']
		);
		vendor('UMD');
		$umd = new UMD();
		$umd->setCharset('UTF-8');
		$umd->addBookInfo($book_info);

		$chapter_obj= M('Book_chapter');
		$list		= $chapter_obj->where('book_id='.$book['book_id'].' and chapter_type=0')->select();
		foreach($list as $chapter){
			$umd->addChapter($chapter['chapter_name'], $chapter['chapter_detail']);
		}
		$fname	= get_filename($book, 'umd');
		mk_dir(dirname($fname));
		$umd->makeUmd($fname);
	}

	protected function epub($book){
		//$fileTime	= date("D, d M Y H:i:s T");
		vendor('EPub');
		$epub_obj	= new EPub();
		// 必需参数
		$epub_obj->setTitle($book['book_name']);
		$epub_obj->setIdentifier("http://www.baidu.com/", "URI");
		// 可选参数
		$epub_obj->setLanguage("zh");
		$epub_obj->setDescription($book['introduce']);
		$epub_obj->setAuthor($book['author']);
		$epub_obj->setDate(time());
		$epub_obj->setSourceURL("http://www.163.com/");

		$cssData = "body {\n  margin-left: .5em;\n  margin-right: .5em;\n  text-align: justify;\n}\n\np {\n  font-family: serif;\n  font-size: 10pt;\n  text-align: justify;\n  text-indent: 1em;\n  margin-top: 0px;\n  margin-bottom: 1ex;\n}\n\nh1, h2 {\n  font-family: sans-serif;\n  font-style: italic;\n  text-align: center;\n  background-color: #6b879c;\n  color: white;\n  width: 100%;\n}\n\nh1 {\n    margin-bottom: 2px;\n}\n\nh2 {\n    margin-top: -2px;\n    margin-bottom: 2px;\n}\n";
		$epub_obj->addCSSFile("styles.css", "css1", $cssData);

		// ePub uses XHTML 1.1, preferably strict.
		$content_start =
			"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
			. "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n"
			. "    \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n"
			. "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n"
			. "<head>"
			. "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n"
			. "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles.css\" />\n"
			. "<title>Test Book</title>\n"
			. "</head>\n"
			. "<body>\n";

		//$cover = $content_start . "<h1>Test Book</h1>\n<h2>By: John Doe Johnson</h2>\n</body>\n</html>\n";
		//$book->addChapter("Cover", "Cover.html", $cover);

		$chapter_obj= M('Book_chapter');
		$list		= $chapter_obj	->where('book_id='.$book['book_id'].' and chapter_type=0')->order('chapter_id')->select();
		foreach($list as $chapter){
			$contents = $content_start.'<h1>'.$chapter['chapter_name']."</h1>\n<p>".str_replace("\n\n","</p>\n<p>", $chapter['chapter_detail'])."</body>\n</html>\n";
			$epub_obj->addChapter($chapter['chapter_name'], $chapter['chapter_id'].'.html', $contents);
		}
		$epub_obj->finalize();
		$file = $epub_obj->getBook();
		write_to_file(get_filename($book, 'epub'), $file);
	}

        //生成jar文件
        protected function jar($book){
            vendor('Jar');
            $jar_obj = new jar();
            $jar_obj->bookInfo['title'] = $book['book_name'];
            $jar_obj->bookInfo['author'] = $book['author'];
            
            $chapter_obj= M('Book_chapter');

            $list = $chapter_obj ->where('book_id'.$book['book_id'].'and chapter_type=0')->order('chapter_id')->select();
            foreach($list as $chapter)
            {
                $jar_obj->addChapter($chapter['chapter_name'], $contents);
            }

            $jar_obj->makeJar($book['book_id']);

            
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
}
?>
