<?php
/**
 * 书籍排行模块
 *
 * @author flashfxp
 */
class TopAction extends MainAction {
	public $type_array	= Array(
	// 格式为： 排序字段|显示字段|排序列标题
		'11'	=> 'all_visit|all_visit|总点击',		//总点击排行，默认
		'12'	=> 'month_visit|month_visit|月点击',	//月点击排行
		'13'	=> 'week_visit|week_visit|周点击',		//周点击排行
		'14'	=> 'day_visit|dat_visit|日点击',		//日点击排行
		'21'	=> 'all_vote|all_vote|总推荐',			//总推荐排行
		'22'	=> 'month_vote|month_vote|月推荐',		//月推荐排行
		'23'	=> 'week_vote|week_vote|周推荐',		//周推荐排行
		'24'	=> 'day_vote|day_vote|日推荐',			//日推荐排行
		'31'	=> 'total_size|total_size|总字数',		//总字数排行
		'32'	=> 'store_num|store_num|总收藏',		//总收藏排行
		'33'	=> 'book_id|post_time|入库时间',		//新入库排行
		'34'	=> 'update_time|update_time|更新时间',	//最新更新排行
		'41'	=> 'book_id|all_visit|总点击',			//新全本排行
		'43'	=> 'ping_score|ping_score|总得分',		//总评分排行
	);

	// 热门排行榜（本周、本月、全部）
	public function index(){
		$tid		= (int)$_REQUEST['id'];
		$type		= $this->type_array[$tid];
		if(empty($type)){
			$type	= $this->type_array['11'];
		}
		list($order, $type_key, $type_name) = explode('|', $type);
		$where		= $tid == 41 ? array('book.is_full'=>1) : '';
		$result		= book_search($where, FALSE, C('book.perpage_top'), $order.' desc');
		$this->assign($result);
		$this->assign('type_name', $type_name);
		$this->assign('type_key', $type_key);
		$this->assign('top_id', $tid);
		$this->display(TEMPLATE_PATH.'/home/top.html');
	}

	/*
	 * 热门或推荐（直接返回数组）
	 */
	public function lists($tid, $limit, $sid){
		switch($tid){
			case ($tid < 40):
				$list = $this->get_list($tid, $limit, $sid); break;
			case 41:
				$list = $this->get_full($limit, $sid); break;
			case 42:
				$list = $this->get_today($limit, $sid); break;
			case 43:
				$list = $this->get_score($limit, $sid); break;
			case 51:
				$list = $this->get_review($limit); break;
			case 52:
				$list = $this->get_search($limit); break;
			case 61:
				$list = $this->get_sort($limit); break;
			case 62:
				$list = $this->get_links($limit); break;
			case 63:
				$list = $this->get_ebook($sid); break;
			case 99:
				$list = $this->get_recommend($limit, $sid); break;
			default:
				$list = ''; break;
		}
		return $list;
	}

	// 获取月、周、日、总点击、推荐榜，收藏榜，全本榜，总字数榜，最新入库
	protected function get_list($tid, $limit, $sid){
		$array	= Array(
			'11' => 'all_visit', '12' => 'month_visit', '13' => 'week_visit', '14' => 'day_visit',
			'21' => 'all_vote', '22' => 'month_vote', '23' => 'week_vote', '24' => 'day_vote',
			'31' => 'total_size', '32' => 'store_num', '33' => 'post_time', '34' => 'last_update'
		);
		$type	= $array[$tid];
		if(!empty($type)){
			if($sid > 0){
				$where['book_sort.path']	= array('like', $this->get_path($sid).'%');
			}else{
				$where	= '';
			}
			//$where	= $sid > 0 ? array('book.sort_id'=>$sid) : '';
			$result	= book_search($where, FALSE, $limit, $type.' desc');
			return $result['list'];
		}else{
			return '';
		}
	}

	// 获取全本列表
	protected function get_full($limit, $sid){
		$where['book.is_full'] = 1;
		//$sid > 0 && $where['book.sort_id'] = $sid;
		$sid > 0 && $where['book_sort.path'] = array('like', $this->get_path($sid).'%');
		$result	 = book_search($where, FALSE, $limit, 'book.book_id desc');
		return $result['list'];
	}

	// 获取今日更新列表
	protected function get_today($limit, $sid){
		$where['book.last_update']  = array('gt',strtotime(date('Y-m-d')));
		//$sid > 0 && $where['book.sort_id'] = $sid;
		$sid > 0 && $where['book_sort.path'] = array('like', $this->get_path($sid).'%');
		$result	 = book_search($where, false, $limit, 'last_update desc');
		return $result['list'];
	}

	// 获取总评分列表
	protected function get_score($limit, $sid){
		$sid > 0 && $where['book_sort.path'] = array('like', $this->get_path($sid).'%');
		$result	 = book_search($where, false, $limit, 'ping_score desc');
		return $result['list'];
	}

	// 获取热门评论
	protected function get_review($limit){
		return '';
	}

	// 获取热门搜索
	protected function get_search($limit){
		return '';
	}

	// 获取编辑推荐列表
	protected function get_recommend($limit, $sid){
		$recommend	= M('Book_recommend');
		$list		= $recommend
					->field('book_recommend.id, book.*, book_sort.sort_name')
					->join(' book on book_recommend.book_id=book.book_id')
					->join(' book_sort on book.sort_id=book_sort.sort_id')
					->where('book_recommend.sort_id='.$sid)
					->order('book_recommend.order')->limit($limit)->select();
		return $list;
	}

	// 获取书籍分类列表
	protected function get_sort($limit){
		$sort_obj	= M('Book_sort');
		$list		= $sort_obj->order('sort_order')->limit($limit)->select();
		return $limit;
	}

	// 获取友情链接列表
	protected function get_links($limit){
		$links_obj	= M('Links');			// 获取友情链接
		$list		= $links_obj->where('`status`=1')->order('`orderid`')->limit($limit)->select();
		return $list;
	}

	// 获取电子书列表
	public function get_ebook($bid){
		$book_obj	= M('Book');
		$book		= $book_obj->find($bid);
		$type_array	= array('txt', 'umd', 'epub');
		foreach($type_array as $type){
			$filename	= get_filename($book, $type);
			if(file_exists($filename)){
				$ebook[$type]= Array(
					'size'	=> round(filesize($filename)/1024),
					'time'	=> filectime($filename),
					'id'	=> $book['book_id'],
					'type'	=> strtoupper($type),
				);
			}
		}
		return $ebook;
	}

	// 多级分类中，获取分类的path信息
	protected function get_path($sort_id){
		$sort	= M('book_sort')->find($sort_id);
		if($sort){
			return $sort['path'];
		}else{
			return '';
		}
	}
}
?>
