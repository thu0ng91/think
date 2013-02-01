<?php
/**
 * 个人文集后台管理员操作模块
 *
 * @author flashfxp
 */
class WorkAction extends BaseAction {
	// 文集管理首页
    public function index(){
		import("ORG.Util.Page");			// 导入分页类
		$works_obj	= M('Book_works');
		$page_no	= empty($_GET['page']) ? "1" : (int)$_GET['page'];
		$perpage	= 20;
		$list		= $works_obj->order('work_id desc')->page($page_no.','.$perpage)->select();
		$this->assign('list', $list);		// 赋值数据集
		
		$total      = $works_obj->count();	// 查询满足要求的总记录数
		$page_obj	= new Page($total, $perpage);	// 实例化分页类传入总记录数和每页显示的记录数
		$page_show	= $page_obj->show();	// 分页显示输出
		$this->assign('page', $page_show);	// 赋值分页输出
		$this->assign('title','文集管理');
		$this->display('./Public/admin/work.html');
	}
	
	// 设置或取消审核
	public function check(){
		$this->set_value('Book_works', 'work_id', $_REQUEST['work_id'], 'if_check', $_REQUEST['value']);
	}
	
	// 设置或取消推荐
	public function recommend(){
		$this->set_value('Book_works', 'work_id', $_REQUEST['work_id'], 'if_recommend', $_REQUEST['value']);
	}

	// 删除文集
	public function delete(){
		$work_id	= (int)$_REQUEST['work_id'];
		$works_obj	= M('Book_works');
		$works_obj->delete($work_id);
		$this->success('文集删除成功！');
	}
}
?>
