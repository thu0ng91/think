<?php
/**
 * 数据备份还原模块
 *
 * @author flashfxp
 */
class RestoreAction extends BaseAction {
	private $back_dir	= './files/backup/';

    public function index() {
		$bdir	= $this->back_dir;
		$array	= glob($bdir.'*');
		foreach($array as $file){
			$name	= basename($file);
			$array2	= glob($file.'/*.sql');
			$size	= 0;
			foreach($array2 as $f){
				$num	 = ceil(filesize($f)/1024);
				$size	+= $num;
				$n		 = basename($f);
				$files[$name][$n] = $num;
			}
			$total[$name] = $size;
		}
		$this->assign('list', $files);
		$this->assign('size', $total);
		$this->assign('title', '数据还原');
		$this->display('./Public/admin/restore.html');
	}

	// 还原数据
	public function restore(){
		$fname = $this->back_dir.$_REQUEST['name'];
		if(file_exists($fname)){
			$struct= $fname.'/__struct__.sql';	// 还原表结构
			$this->resSql(file_get_contents($struct));

			$files = glob($fname.'/*.sql');
			foreach($files as $file){
				if($file == $struct){ continue; }
				$this->resSql(file_get_contents($file));
			}
			$this->success('数据还原成功！');
		}else{
			$this->error('备份文件不存在！');
		}
	}

	// 删除备份
	public function delete(){
		$fname = $this->back_dir.$_REQUEST['name'];
		if(file_exists($fname)){
			rrmdir($fname);
			$this->success('备份删除成功！');
		}else{
			$this->error('备份文件不存在！');
		}
	}

	/**
	 *  还原sql文件数据
	 */
	protected function resSql($sql){
		$sql = str_replace("\r\n", "\n", $sql);
		$sql_array = explode(";\n", $sql);
		foreach ($sql_array as $key => $value){
			if(empty($value)){ continue; }

//			if(get_cfg_var("magic_quotes_gpc")){
//				stripslashes($value);
//			}
			
			M()->query($value);
		}
	}
}
?>
