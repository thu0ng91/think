<?php
/**
 * 系统后台管理首页
 *
 * @author flashfxp
 */
class IndexAction extends BaseAction {
    public function index(){
		if(Cookie::is_set('admin_id')){
			$user_js = 'user/'.Cookie::get('admin_group_id').'.js';
			$this->assign('user_js', $user_js);
			$this->assign('admin_name', Cookie::get('admin_name'));
			$this->display('./Public/admin/index.html');
		}else{
			$this->display('./Public/admin/login.html');
		}
	}
}
?>
