<?php
/**
 +------------------------------------------------------------------------------
 * UserGroupAction 模块
 +------------------------------------------------------------------------------
 *
 * @author abtest
 */
class UsergroupAction extends BaseAction {
    /**
     * @var int per_page 定义每一页显示的列表行数
     */
    private $per_page = 20;


    /**
     +------------------------------------------------------------------------------
     * 用户组管理
     +------------------------------------------------------------------------------
     */
    public function index(){
        $this->assign('title','用户组管理');
        redirect('?s=admin/usergroup/show');
    }

    /**
     +------------------------------------------------------------------------------
     * 显示添加(新建)管理员组
     +------------------------------------------------------------------------------
     */
    public function add() {
        $group_db   = M('User_group');
        $grouplist  = $group_db->select();
        $this->assign('grouplist', $grouplist);
        $this->assign('title', '添加用户组');
        $this->assign('action', 'do_add');
        $this->display('./Public/admin/usergroup_add.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 添加管理员用户
     +------------------------------------------------------------------------------
     */
    public function do_add(){
        $db     = M('User_group');
        $data   = $this->get_form_attr($_POST, "add");
        $result = $db->add($data);
        $this->show_result_msg($result, '添加用户组成功', '添加用户员组失败！', '?s=admin/usergroup/group_list');
    }

    /**
     +------------------------------------------------------------------------------
     * 更新管理员用户设置相关参数
     +------------------------------------------------------------------------------
     */
    public function update() {
        $id         = (int)$_GET['id'];
        $db         = M('User_group');
        $group      = $db->find($id);
        $group_auth = str_split($group['authority'], 1);
        $this->assign('group', $group);
        $this->assign('group_auth', $group_auth);
        $this->assign('title', '修改用户组信息');
        $this->assign('action', 'do_update');
        $this->display('./Public/admin/usergroup_add.html');
    }

    public function do_update(){
        $id     = (int)$_POST['id'];
        $db     = M('User_group');
        $data   = $this->get_form_attr($_POST, "update");
        $result = $db->save($data);
        $this->show_result_msg($result, '用户组修改成功', '用户员组修改失败！', '?s=admin/usergroup/group_list');
    }

    /**
     +------------------------------------------------------------------------------
     * 删除用户组
     +------------------------------------------------------------------------------
     */
    public function delete() {
        $id     = $_REQUEST['id'];
        $db     = M('User_group');
        $result = $db->delete($id);
        $this->show_result_msg($result, '删除用户组成功', '删除用户组失败！', '?s=admin/usergroup/group_list');
    }

    /**
     +------------------------------------------------------------------------------
     * 返回所有用户组列表
     +------------------------------------------------------------------------------
     */
    public function group_list() {
        import("ORG.Util.Page");			// 导入分页类
        $user	= M('User_group');
        $page_no	= empty($_GET['page']) ? "1" : (int)$_GET['page'];
        $list		= $user->order('id desc')->page($page_no.','.$this->per_page)->select();
        $this->assign('list', $list);		// 赋值数据集

        $total          = $user->count();	// 查询满足要求的总记录数
        $page_obj	= new Page($total, $this->per_page);	// 实例化分页类传入总记录数和每页显示的记录数
        $page_show	= $page_obj->show();	// 分页显示输出
        $this->assign('page', $page_show);	// 赋值分页输出
        $this->assign('title','用户组列表');
        $this->display('./Public/admin/usergroup_show.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 添加或修改用户组信息时，从表单中提取并检测需要的字段信息
     * 返回一个数组
     +------------------------------------------------------------------------------
     */
    protected function get_form_attr($array, $type="add"){
        for($i = 0; $i <= 11; $i++){
            $arr[]=(string)$array['authority_'.$i];
        }
        $authority = implode('',$arr);

        $data = array(
                'group_name'	=> safe_str($array['group_name']),
                'authority'     => (string)$authority
        );

        if($type != "add"){
                $data['id']     = (int)$array['id'];
        }
        return $data;
    }
}

?>
