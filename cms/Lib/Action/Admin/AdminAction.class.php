<?php
/**
 +------------------------------------------------------------------------------
 * AdminAction 模块
 +------------------------------------------------------------------------------
 *
 * @author abtest
 */
class AdminAction extends BaseAction {
    /**
     * @var int per_page 定义每一页显示的列表行数
     */
    private $per_page = 20;

    /**
     +------------------------------------------------------------------------------
     * 超级用户管理 index
     +------------------------------------------------------------------------------
     */
    public function index(){
        $this->assign('title','超级用户管理');
        redirect('?s=admin/admin/show');
    }

    /**
     +------------------------------------------------------------------------------
     * 显示添加(新建)管理员用户
     +------------------------------------------------------------------------------
     */
    public function add() {
        $group_db   = M('Admin_group');
        $grouplist  = $group_db->select();
        $this->assign('grouplist', $grouplist);
        $this->assign('title', '添加管理员');
        $this->assign('action', 'do_add');
        $this->display('./Public/admin/admin_add.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 添加管理员用户
     +------------------------------------------------------------------------------
     */
    public function do_add(){
        $admin_db	= M('Admin');
        $data		= $this->get_form_attr($_POST, "add");
        $result		= $admin_db->add($data);
        $this->show_result_msg($result, '添加管理员成功', '添加管理员失败！', '?s=admin/admin/admin_list');
    }
    
    /**
     +------------------------------------------------------------------------------
     * 更新管理员用户设置相关参数
     +------------------------------------------------------------------------------
     */
    public function update() {
        $id	= (int)$_GET['id'];
        $db         = M('Admin');
        $admin		= $db->find($id);
        $group_db   = M('Admin_group');
        $grouplist  = $group_db->select();
        $this->assign('grouplist', $grouplist);
        $this->assign('admin', $admin);
        $this->assign('title', '修改管理员信息');
        $this->assign('action', 'do_update');
        $this->display('./Public/admin/admin_add.html');
    }

    public function do_update(){
        $id     = (int)$_POST['id'];
        $db     = M('Admin');
        $data   = $this->get_form_attr($_POST, "update");
        $result = $db->save($data);
        $this->show_result_msg($result, '管理员修改成功', '管理员修改失败！', '?s=admin/admin/admin_list');
    }

    /**
     +------------------------------------------------------------------------------
     * 删除管理员用户
     +------------------------------------------------------------------------------
     * @param int $id 超级用户ID
     */
    public function delete() {
        $id     = $_REQUEST['id'];
        $db     = M('Admin');
        $result	= $db->delete($id);
        $this->show_result_msg($result, '删除管理员组成功', '删除管理员组失败！', '?s=admin/admin/admin_list');
    }

    /**
     +------------------------------------------------------------------------------
     * 返回所有管理员用户列表
     +------------------------------------------------------------------------------
     */
    public function admin_list() {
        import("ORG.Util.Page");			// 导入分页类
        $admin	= M('Admin');
        $page_no	= empty($_GET['page']) ? "1" : (int)$_GET['page'];
        $list		= $admin->order('id desc')->page($page_no.','.$this->per_page)->select();
        $this->assign('list', $list);		// 赋值数据集

        $total          = $admin->count();	// 查询满足要求的总记录数
        $page_obj	= new Page($total, $this->per_page);	// 实例化分页类传入总记录数和每页显示的记录数
        $page_show	= $page_obj->show();	// 分页显示输出
        $this->assign('page', $page_show);	// 赋值分页输出
        $this->assign('title','管理员列表');
        $this->display('./Public/admin/admin_show.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 添加或修改管理员信息时，从表单中提取并检测需要的字段信息
     * 返回一个数组
     +------------------------------------------------------------------------------
     */
    protected function get_form_attr($array, $type="add"){
		$data = array(
			'admin_name'	=> safe_str($array['admin_name']),
			//'admin_pwd'     => $admin_pwd_1,
			'group_id'      => (int)$array['group_id']
		);

        // 两次密码判断
        $admin_pwd_1 = safe_str($array['admin_pwd_1']); //密码
        $admin_pwd_2 = safe_str($array['admin_pwd_2']); //确认密码

        if($admin_pwd_1 != $admin_pwd_2){
            $this->show_result_msg(0, '修改错误!', '修改错误!');
        }

        if($admin_pwd_1 != NULL){
            $data['admin_pwd'] = MD5($admin_pwd_1);
        }

		if($type != "add"){
			$data['id']     = (int)$array['id'];
		}
		return $data;
	}
}

?>
