<?php
/**
 * 数据备份还原模块
 *
 * @author flashfxp
 */
class BackupAction extends BaseAction {
	private $back_dir	= './files/backup/';
	private $from_key	= 0;

    public function index() {
		$table = $this->getTable();
		$this->assign('list', $table);
		$this->assign('title', '数据库备份');
		$this->display('./Public/admin/backup.html');
	}

	// 备份所选表结构
	public function backup(){
		$tables = $_POST['backup_type'] == 'custom' ? $_POST['tables'] : $this->getTable();
		if(empty($tables) || count($tables) < 1){
			$this->error('参数错误！#BK01');
		}
		$struct	= $this->bakStruct($tables);

		$dname	= date('Ymd-His');
		$size	= (int)$_POST['vol_size'];
		$size < 10 && $size = 10;
		S('backup_dir', $dname);					// 设置本次备份目录名
		S('backup_size', $size);					// 设置本次备份分卷大小
		S('backup_tables', implode('|', $tables));	// 设置本次备份表数组

		$fname	= $this->back_dir.$dname.'/__struct__.sql';
		write_to_file($fname, $struct); // 备份表结构
		
		$this->assign('jumpUrl', '?s=admin/backup/bdata');
		$this->success('已备份所选表结构，现在开始备份表数据！');
	}

	// 备份表数据
	public function bdata(){
		if(S('backup_dir') == '' || !S('backup_tables') || S('backup_size') < 10){
			$this->error('非法操作！#BK02');
		}
		$tables = explode('|', S('backup_tables'));
		$num = (int)$_REQUEST['n'];
		$num < 1 && $num  = 0;
		if($num >= count($tables)){
			S('backup_dir', NULL);
			S('backup_tables', NULL);
			S('backup_size', NULL);
			$this->assign('jumpUrl', '?s=admin/backup');
			$this->success('备份已完成！');
		}else{
			$table	= $tables[$num];
			$from	= (int)$_REQUEST['p'];
			$from < 1 && $from = 0;
			$fdir	= $this->back_dir.S('backup_dir').'/'.$table;
			$url	= '?s=admin/backup/bdata/n/';
			$message= '已备份表：'.$table;
			if($this->from_key > 0){
				$fname = $fdir.'___'.$from.'.sql';
				$message .= ' 到第 '.$this->from_key.' 项';
				$this->assign('jumpUrl', $url.$num.'/p/'.$this->from_key);
			}else{
				$fname = $fdir.'.sql';
				$this->assign('jumpUrl', $url.++$num);
			}
			$contents  = $this->bakRecord($table, $from);
			$contents != '' && write_to_file($fname, $contents);
			$this->success($message);
		}
	}

	/**
	 * 返回数据库中的数据表
	 */
	protected function getTable() {
		$dbName = C('DB_NAME');
		$result = M()->query('show tables from '.$dbName);
		foreach ($result as $v) {
			$tbArray[] = $v['Tables_in_'.$dbName];
		}
		return $tbArray;
	}

	/**
	 * 备份全部数据表结构
	 */
	protected function bakStruct($array) {
		foreach ($array as $tbName) {
			//$sql .= "--\r\n";
			//$sql .= "-- 数据表结构: `$tbName`\r\n";
			//$sql .= "--\r\n\r\n";
			$sql .= "DROP TABLE IF EXISTS ".$tbName.";\n";
			$sql .= "create table `$tbName` (\r\n";

			$result		= M()->query('show columns from ' . $tbName);
			$rsCount	= count($result);
			foreach ($result as $k => $v) {
				$field	= $v['Field'];
				$type	= $v['Type'];
				$extra	= $v['Extra'];

				$default= $v['Default'] != '' ? 'default \''.$v['Default'].'\'' : '';
				$null	= $v['Null'] == 'NO' ? 'not null' : "null";
				$key	= $v['Key'] == 'PRI' ? 'primary key' : '';

				$sql .= "`$field` $type $null $default $key $extra ";
				$sql .= $k < ($rsCount - 1) ? ",\r\n" : "\r\n";
			}
			$sql.=")engine=MyISAM charset=utf8;\r\n\r\n";
		}
		return str_replace(',)', ')', $sql);
	}

	/**
	 * 备份单个数据表数据
	 */
	protected function bakRecord($tbName, $from=0){
		$rs = M()->query('select * from '.$tbName.' limit '.$from.',100000000');
		if(count($rs) < 1){  return ''; }

		$size	= S('backup_size') * 1024;
		foreach($rs as $k => $v){
			$sql .= "INSERT INTO `$tbName` VALUES (";
			foreach($v as $key => $value){
				if($value == ''){
					$value = 'null';
				}
				$type = gettype($value);
				if($type == 'string') {
					$value = "'" . mysql_escape_string($value) . "'";
				}
				$sql .= $value.',';
			}
			$sql .= ");\r\n";
			if(strlen($sql) > $size){
				$this->from_key = $from;
				break;
			}else{
				$from++;
			}
		}
		return str_replace(',)', ')', $sql);
	}

}
?>
