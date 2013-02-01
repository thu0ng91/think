<?php
/**
 * 风格模板管理模块
 *
 * @author flashfxp
 */
class TplAction extends BaseAction {
	// 显示模板列表
	public function show(){
		$sid		= (int)$_GET['id'];
		$style_obj	= M('Style');
		$style		= $style_obj->find($sid);
		if($style){
			$tpl_obj= M('Style_tpl');
			$list	= $tpl_obj->field('`tid`, `group`, `file`, `note`, `is_system`')->where('`sid`='.$sid)->order('`group`')->select();
			foreach($list as $tpl){
				$group	= $tpl['group'];
				$data[$group][] = $tpl;
			}
			$this->assign($data);
			$this->assign('style', $style);
			$this->display('./Public/admin/tpl_show.html');
		}else{
			$this->error('风格不存在！');
		}
	}

	// 显示模板添加
	public function add(){
		$sid		= (int)$_GET['id'];
		$style_obj	= M('Style');
		$tpl		= $style_obj->find($sid);
		if($tpl){
			$tpl['group'] = $_GET['group'];
			$this->assign('tpl', $tpl);
			$this->assign('action', 'do_add');
			$this->display('./Public/admin/tpl_add.html');
		}else{
			$this->error('参数错误！');
		}
	}

	// 添加模板
	public function do_add(){
		$tpl_obj	= M('Style_tpl');
		$data		= $this->get_form_attr($_POST, "add");
		$result		= $tpl_obj->add($data);
		$fname		= $this->get_tpl_file($data['sid'], $data['group'], $data['file']);
		write_to_file($fname, stripslashes($data['contents']));
		$this->show_result_msg($result, '模板添加成功', '模板添加失败！', '?s=admin/tpl/show/id/'.$data['sid']);
	}

	// 显示模板编辑
	public function update(){
		$tid		= (int)$_GET['id'];
		$tpl_obj	= M('Style_tpl');
		$tpl		= $tpl_obj->where('style_tpl.tid='.$tid)->join(' style on style.sid=style_tpl.sid')->find();
		if($tpl){
			$tpl['contents'] = stripslashes($tpl['contents']);
			$tpl['contents'] = str_ireplace(array('<textarea>','</textarea>'), array('&lt;textarea>','&lt;/textarea>'), $tpl['contents']);
			$this->assign('tpl', $tpl);
			$this->assign('action', 'do_update');
			$this->display('./Public/admin/tpl_add.html');
		}else{
			$this->error('模板不存在！');
		}
	}

	// 编辑模板
	public function do_update(){
		$tid		= (int)$_POST['tid'];
		$tpl_obj	= M('Style_tpl');
		$tpl		= $tpl_obj->find($tid);
		if($tpl){
			$data		= $this->get_form_attr($_POST, "update", $tpl['is_system']);
			$result		= $tpl_obj->save($data);
			$fname		= $this->get_tpl_file($data['sid'], $data['group'], $data['file']);
			write_to_file($fname, stripslashes($data['contents']));
			$this->show_result_msg($result, '模板修改成功', '模板修改失败！', '?s=admin/tpl/show/id/'.$tpl['sid']);
		}else{
			$this->error('模板不存在！');
		}
	}

	// 删除模板
	public function delete(){
		$tid		= (int)$_REQUEST['id'];
		$tpl_obj	= M('Style_tpl');
		$tpl		= $tpl_obj->find($tid);
		if(!$tpl){ $this->error('模板不存在！'); }

		if($tpl['is_system']){
			$this->error('系统模板无法删除');
		}else{
			$tpl_obj->delete($tid);
			if(!empty($_REQUEST['all'])){
				$fname = $this->get_tpl_file($tpl['sid'], $tpl['group'], $tpl['file']);
				file_exists($fname) && unlink($fname);
			}
			$this->success('模板删除成功！');
		}
	}

	// 添加或修改模板时，从表单中提取并检测需要的字段信息，返回一个数组
	protected function get_form_attr(&$array, $type="add", $system=0){
		$data['contents'] = safe_str($array['contents'], true);			// 暂时不进行过滤

		//if($system == 0){
		$data['group']= safe_str($array['group']);		// 这里和下面的函数有点冲突，待修改
		$data['file'] = safe_str($array['file']);
		$data['note'] = safe_str($array['note']);
		//}
		$data['sid']= (int)$array['sid'];
		if($type == "add"){
			//$data['sid']= (int)$array['sid'];
		}else{
			$data['tid']= (int)$array['tid'];
		}
		return $data;
	}

	// 获取模板文件路径
	protected function get_tpl_file($sid, $group, $file){
		$style_obj	 = M('Style');
		$style		 = $style_obj->find($sid);
		$fdir		 = TMPL_PATH.$style['value'].'/';
		$fdir		.= $group == 'global' ? '' : $group.'/';
		return $fdir.$file;
	}
}
?>
