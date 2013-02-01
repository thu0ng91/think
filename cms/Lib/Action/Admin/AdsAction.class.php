<?php
/**
 * 网站广告管理模块
 *
 * @author flashfxp
 */
class AdsAction extends BaseAction {
    private $per_page = 20;

    public function index(){
            import("ORG.Util.Page");			// 导入分页类
            $ad_obj		= M('ads');
            $page_no	= empty($_GET['page']) ? "1" : (int)$_GET['page'];
            $list		= $ad_obj->order('aid')->page($page_no.','.$this->per_page)->select();
            $this->assign('list', $list);		// 赋值数据集

            $total      = $ad_obj->count();	// 查询满足要求的总记录数
            $page_obj	= new Page($total, $this->per_page);	// 实例化分页类传入总记录数和每页显示的记录数
            $page_show	= $page_obj->show();	// 分页显示输出
            $this->assign('page', $page_show);	// 赋值分页输出
            $this->assign('title','广告列表');
            $this->display('./Public/admin/ads.html');
	}

	// 广告预览
	public function show(){
		$aid	= (int)$_GET['id'];
		$ad		= M('ads')->find($aid);
		if($ad){
			$this->assign('ad', $ad);
			$this->display('./Public/admin/ads_show.html');
		}else{
			$this->assign('jumpUrl', __ROOT__.'/');
			$this->error('广告不存在！');
		}
	}

	// 添加友情链接
	public function add(){
		$this->assign('title', '添加广告');
		$this->assign('action', 'do_add');
		$this->display('./Public/admin/ads_add.html');
	}

	// 执行添加动作
	public function do_add(){
		$data	= $this->get_form_attr($_POST, 'add');
		$result	= M('ads')->add($data);
		if($result){
			$data['aid'] = $result;
			$this->create_js($data);
			$this->assign('jumpUrl', '?s=admin/ads');
			$this->success('添加广告成功');
		}else{
			$this->error('添加广告失败');
		}
	}

	// 修改链接
	public function update(){
		$aid	= (int)$_GET['id'];
		$ad		= M('ads')->find($aid);

		$this->assign('ad', $ad);
		$this->assign('title','修改广告');
		$this->assign('action', 'do_update');
		$this->display('./Public/admin/ads_add.html');
	}

	// 执行修改动作
	public function do_update(){
		$data	= $this->get_form_attr($_POST, 'update');
		$result	= M('ads')->save($data);
		$this->create_js($data);
		$this->show_result_msg($result, '广告修改成功', '广告修改失败！', '?s=admin/ads');
	}

	/*
	 *  删除广告
	 */
	public function delete(){
		$ids		= (array)$_REQUEST['id'];
		$ads_obj	= M('ads');
		foreach($ids as $id){
			$id		= (int)$id;
			if($id < 1){ continue; }
			$ads_obj->delete($id);
			$fname	= 'files/ads/'.$id.'.js';
			if(file_exists($fname)){ unlink($fname); }
		}
		$this->success('广告删除成功！');
	}

	// 生成广告调用js
	protected function create_js($ad){
		$contents   = 'document.write(\'<a href="'.__ROOT__.'/index.php?s=ajax/ads/id/'.$ad['aid'];
		$contents  .= '" target="'.$ad['target'].'" title="'.$ad['note'].'">';
		if(strpos($ad['pic'], 'http://') === false){
			$ad['pic'] = __ROOT__.'/'.$ad['pic'];
		}
		$contents  .= '<img src="'.$ad['pic'].'" width="'.$ad['width'].'" height="'.$ad['height'].'" /></a>\');';
		$fname		= 'files/ads/'.$ad['aid'].'.js';
		if(file_exists($fname)){ unlink($fname); }
		write_to_file($fname, $contents);
	}

	// 添加或修改时，从表单中提取并检测需要的字段信息
	protected function get_form_attr($array, $type="add"){
		$data = array(
			'name'		=> safe_str($array['name']),
			'url'		=> safe_str($array['url']),
			'target'	=> safe_str($array['target']),
			'note'		=> safe_str($array['note']),
			'pic'		=> safe_str($array['pic']),
			'width'		=> (int)$array['width'],
			'height'	=> (int)$array['height'],
			'is_show'	=> safe_str($array['is_show']),
		);
		if($type == "update"){
			$data['aid']	= (int)$array['aid'];
		}
		return $data;
	}
}
?>
