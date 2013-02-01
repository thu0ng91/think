<?php
/**
 * 风格模板管理模块
 *
 * @author flashfxp
 */
class StyleAction extends BaseAction {
	// 风格模板列表
    public function index(){
		//$files		= glob(TMPL_PATH.'default/css/*');
		$style_obj		= M("Style");
		$list			= $style_obj->select();
		$this->assign('list', $list);
		$this->display('./Public/admin/style.html');
	}

	// 设置为默认风格
	public function set(){
		$style_id	= (int)$_REQUEST['id'];
		$style_obj	= M("Style");
		$style		= $style_obj->find($style_id);
		if($style){
			if($style['default'] != 1){
				$style_obj->where(array('default'=>1))->save(array('default'=>0));
				$this->set_default($style_id, $style['value']);
			}
			$this->success('设置成功！', true);
		}else{
			$this->error('参数无效！', true);
		}
	}

	// 添加风格
	public function add(){
		$name	= safe_str($_REQUEST['name']);
		$value	= safe_str($_REQUEST['value']);
		if($name == "" || $value == ""){
			$this->error('参数不能为空！', true);
		}
		$style_obj	= M("Style");
		$style		= $style_obj->where(array('value'=>$value))->find();
		if(!$style){
			$sid	= $style_obj->add(array('name'=>$name, 'value'=>$value));
			$fdir	= TMPL_PATH.$value;
			if(file_exists($fdir)){
				$this->import($sid, $fdir);
			}else{
				mk_dir($fdir);
			}
			$this->success('添加成功！', true);
		}else{
			$this->error('风格目录已存在，请直接修改相应风格！', true);
		}
	}

	// 删除风格
	public function delete(){
		$style_id	= (int)$_REQUEST['id'];
		$style_id  == 1 && $this->error('系统默认风格，无法删除！', true);
		
		$style_obj	= M("Style");
		$style		= $style_obj->find($style_id);
		if($style){
			$style['default'] == 1 && $this->set_default();		// 恢复系统默认风格
			$style_obj->delete($style_id);

			$tpl_obj= M('Style_tpl');							// 同时删除风格模板
			$tpl_obj->where(array('sid'=>$style_id))->delete();	
			
			rrmdir(TMPL_PATH.$style['value']);					// 同时删除风格目录
			$this->success('风格删除成功！', true);
		}else{
			$this->error('风格不存在！', true);
		}
	}

	// 修改风格
	public function edit(){
		$style_id	= (int)$_REQUEST['id'];
		$style_id  == 1 && $this->error('系统默认风格，无法删除！', true);

		$style_obj	= M("Style");
		$style		= $style_obj->find($style_id);
		if($style){
			$data	= array(
				'sid'	=> $style_id,
				'name'	=> safe_str($_REQUEST['name']),
				'value'	=> safe_str($_REQUEST['value']),
			);
			$style_obj->save($data);
			$this->success('风格修改成功！', true);
		}else{
			$this->error('风格不存在！', true);
		}
	}

	// 设置默认风格
	protected function set_default($id=1, $value='default'){
		$style_obj	= M("Style");
		$style_obj->save(array('sid'=>$id, 'default'=>1));
		update_config_file(array('DEFAULT_THEME'=>$value));
	}

	// 导入模板文件
	public function import($sid, $bdir){
		$style_obj	= M('Style_tpl');
		$data		= array('sid'=>$sid);
		$array		= require './Conf/tpl.php';
		
		foreach($array as $group=>$item){
			$data['group']	= $group;
			$fdir	= $group == "global" ? $bdir."/" : $bdir."/".$group."/";

			if($group  == "css" || $group == "js"){
				$files	= glob($fdir."*.".$group);
				$system = false;
			}else{
				$files	= glob($fdir."*.html");
				$system = true;
			}

			foreach($files as $file){
				$fname	= basename($file);
				$data['file']		= $fname;
				$data['note']		= empty($item[$fname]) ? $fname : $item[$fname];
				$data['is_system']	= ($system && isset($item[$fname])) ? 2 : 0;
				$data['contents']	= addslashes(file_get_contents($file));
				$style_obj->add($data);
			}
		}
	}
}
?>
