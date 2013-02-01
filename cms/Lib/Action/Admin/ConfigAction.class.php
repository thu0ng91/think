<?php
/**
 * Description of Configclass
 *
 * @author delphi
 */
class ConfigAction  extends BaseAction {
    // 管理首页
	public function index(){
		$this->assign('title','系统参数设置');
		$this->display('./Public/admin/config.html');
	}

	// 保存基本设置相关参数
	public function update_base(){
		$data = Array(
			'site_name'		=> safe_str($_POST['site_name']),
			'site_index'		=> safe_str($_POST['site_index']),
			'site_domain'		=> safe_str($_POST['site_domain']),
			'site_icp'			=> safe_str($_POST['site_icp']),
			'metakeywords'		=> safe_str($_POST['metakeywords']),
			'metadescription'	=> safe_str($_POST['metadescription']),			
		);
		update_config($data, 'system');
		$this->success('参数修改成功！');
	}

	// 保存数据库设置信息
	public function update_db(){
		$data = Array(
			'DB_TYPE'               => safe_str($_POST['DB_TYPE']),
			'DB_CHARSET'            => safe_str($_POST['DB_CHARSET']),
			'DB_PREFIX'             => safe_str($_POST['DB_PREFIX']),
			'DB_HOST'               => safe_str($_POST['DB_HOST']),
			'DB_PORT'               => safe_str($_POST['DB_PORT']),
			'DB_NAME'               => safe_str($_POST['DB_NAME']),
			'DB_USER'               => safe_str($_POST['DB_USER']),
			'DB_PWD'                => safe_str($_POST['DB_PWD']),
		);
		update_config($data, '');
		$this->success('参数修改成功！');
	}

	// 控制显示
	public function update_view() {
            $data = Array(
				'site_open'                  => (int)($_POST['site_open']),
				'site_close_inform'          => safe_str($_POST['site_close_inform']),
				'site_allow_reg'             => (int)($_POST['site_allow_reg']),
				'cookie_time'                => (int)($_POST['cookie_time']),
				'cookies_domain'             => safe_str($_POST['cookies_domain']),
				'max_pagenum'                => (int)($_POST['max_pagenum']),
            );
            update_config($data, 'system');
            $this->success('参数修改成功！');
        }
}
?>
