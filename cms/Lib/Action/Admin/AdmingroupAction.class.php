<?php
/**
 +------------------------------------------------------------------------------
 * AdminGroupAction 模块
 +------------------------------------------------------------------------------
 *
 * @author abtest
 */
class AdmingroupAction extends BaseAction {
    /**
     * @var int per_page 定义每一页显示的列表行数
     */
    private $per_page = 20;

    /**
     +------------------------------------------------------------------------------
     * 管理员组 index
     +------------------------------------------------------------------------------
     */
    public function index(){
        redirect('?s=admin/admingroup/show');
    }

    /**
     +------------------------------------------------------------------------------
     * 显示添加(新建)管理员组
     +------------------------------------------------------------------------------
     */
    public function add() {
        $this->assign('title', '添加管理员组');
        $this->assign('action', 'do_add');
        $this->display('./Public/admin/admingroup_add.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 添加管理员组
     +------------------------------------------------------------------------------
     */
    public function do_add(){
        $db     = M('Admin_group');
        $data   = $this->get_form_attr($_POST, "add");
        $result = $db->add($data);
        $group_id = $db->getLastInsID();
        write_group_menu_js($group_id ,$data['authority']);
        $this->show_result_msg($result, '添加管理员组成功', '添加管理员组失败！', '?s=admin/admingroup/group_list');
    }

    /**
     +------------------------------------------------------------------------------
     * 更新管理员用户设置相关参数
     +------------------------------------------------------------------------------
     */
    public function update() {
        $id         = (int)$_GET['id'];
        $db         = M('Admin_group');
        $group      = $db->find($id);
        $group_auth = explode(',', $group['authority']);
        
        $config     = str_split($group_auth[0], 1);
        $book       = str_split($group_auth[1], 1);
        $user       = str_split($group_auth[2], 1);
        $collector  = str_split($group_auth[3], 1);
        $order      = str_split($group_auth[4], 1);
        $themes     = str_split($group_auth[5], 1);
        $database   = str_split($group_auth[6], 1);

        $this->assign('group', $group);
        $this->assign('config', $config);
        $this->assign('book', $book);
        $this->assign('user', $user);
        $this->assign('collector', $collector);
        $this->assign('order', $order);
        $this->assign('themes', $themes);
        $this->assign('database', $database);
        $this->assign('title', '修改管理员组信息');
        $this->assign('action', 'do_update');
        $this->display('./Public/admin/admingroup_add.html');
    }

    public function do_update(){
        $group_id	= (int)$_POST['id'];
        $group_db	= M('Admin_group');
        $data		= $this->get_form_attr($_POST, "update");
        $result		= $group_db->save($data);
        write_group_menu_js($group_id ,$data['authority']);
        $this->show_result_msg($result, '管理员组修改成功', '管理员组修改失败！', '?s=admin/admingroup/group_list');
    }

    /**
     +------------------------------------------------------------------------------
     * 删除管理员用户组
     +------------------------------------------------------------------------------
     * @param int $id 管理员用户组ID
     */
    public function delete() {
        $id         = $_REQUEST['id'];
        $db         = M('Admin_group');
        $result     = $db->delete($id);
        $this->show_result_msg($result, '删除管理员组成功', '删除管理员组失败！', '?s=admin/admingroup/group_list');
    }

    /**
     +------------------------------------------------------------------------------
     * 返回所有管理员组列表
     +------------------------------------------------------------------------------
     */
    public function group_list() {
        import("ORG.Util.Page");			// 导入分页类
        $db	= M('Admin_group');
        $page_no	= empty($_GET['page']) ? "1" : (int)$_GET['page'];
        $list		= $db->order('id desc')->page($page_no.','.$this->per_page)->select();
        $this->assign('list', $list);		// 赋值数据集

        $total      = $db->count();	// 查询满足要求的总记录数
        $page_obj	= new Page($total, $this->per_page);	// 实例化分页类传入总记录数和每页显示的记录数
        $page_show	= $page_obj->show();	// 分页显示输出
        $this->assign('page', $page_show);	// 赋值分页输出
        $this->assign('title','管理员组列表');
        $this->display('./Public/admin/admingroup_show.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 添加或修改管理员组信息时，从表单中提取并检测需要的字段信息
     * 返回一个数组
     +------------------------------------------------------------------------------
     */
    protected function get_form_attr($array, $type="add"){
        $authoritygroup = 7;
        for($i = 0; $i < $authoritygroup; $i++){
            $arr[] = (int)$array['authority'.$i];
            for($j = 0; $j < 12; $j++){
                if(isset($array['authority'.$i.'_'.$j])){
                    $arr[] = (int)$array['authority'.$i.'_'.$j];
                }
            }
            // 补充一个分隔符
            if($i < $authoritygroup-1){
                $arr[] = ',';
            }
        }

        $authority = implode('',$arr);

		$data = array(
			'group_name'	=> safe_str($array['group_name']),
			'authority'     => $authority
		);

		if($type != "add"){
			$data['id']     = (int)$array['id'];
		}
		return $data;
	}
}

?>
