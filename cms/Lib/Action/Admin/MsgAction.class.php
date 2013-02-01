<?php

/**
  +------------------------------------------------------------------------------
 * 后台管理用户发送消息
  +------------------------------------------------------------------------------
 * @author abtest
 */
class MsgAction extends BaseAction {

    /**
     * @var int per_page 定义每一页显示的列表行数
     */
    private $per_page = 20;

    /**
      +------------------------------------------------------------------------------
     * 后台管理用户发送消息
      +------------------------------------------------------------------------------
     */
    public function index() {
        $this->assign('title', '管理员信息系统');
        redirect('?s=admin/msg/show');
    }

    /**
      +------------------------------------------------------------------------------
     * 新建信息
     * TODO:下一阶段将在发送个人信息时可选多个用户ID
      +------------------------------------------------------------------------------
     */
    public function add() {
        $group_db = M('User_group');
        $group = $group_db->select();
        //去除在后台添加用户时，用户组中包含游客一组
        $count = count($group);
        for ($i = 1; $i < $count; $i++) {
            $grouplist[$i] = $group[$i];
        }
        $this->assign('grouplist', $grouplist);

        $this->assign('title', '新建信息');
        $this->display('./Public/admin/msg_new.html');
    }

    public function do_add_private() {
        $msg_title      = $_POST['msg_title'];
        $msg_content    = $_POST['msg_content'];
        $msg_to         = $_POST['msg_to'];
        $msg_from       = Cookie::get('admin_id');

        $db = M('Admin');
        $array = $db->where("`id`=" . $msg_from)->select();
        $msg_from_name = $array[0]['admin_name'];

        $db = M('Admin_msg');

        // 组合信息
        $msg = array (
            'msg_from' => $msg_from,
            'msg_from_name' => $msg_from_name,
            'msg_to' => $msg_to,
            'msg_title' => $msg_title,
            'msg_content' => $msg_content,
            'is_read' => '0',
            'msg_time' => time()
        );

        $result = $db->add($msg);
        $this->show_result_msg($result, '发送信息成功', '发送信息失败！', '?s=admin/msg/show');
    }

    public function do_add_global() {
        $msg_title = $_POST['msg_title'];
        $msg_content = $_POST['msg_content'];
        $msg_from = '0';
        $msg_from_name = '系统消息';
        $group_id = $_POST['group_id'];
        $group_count = count($group_id);

        // 组合消息
        $msg = array (
            'msg_from' => $msg_from,
            'msg_from_name' => $msg_from_name,
            'msg_title' => $msg_title,
            'msg_content' => $msg_content,
            'is_read' => '0',
            'msg_time'=> time()
        );

        $user_db = M('User');
        $user_msg_db = M('User_msg');
        for($i = 0; $i < $group_count; $i++){
            $user_array = $user_db->where("`group_id`=".$group_id[$i])->select();
            $user_count = count($user_array);
            for($j = 0; $j < $user_count; $j++){
                $msg['msg_to'] = $user_array[$j]['id'];
                $msg['msg_to_name'] = $user_array[$j]['user_name'];
                $user_msg_db->add($msg);
            }
        }

        $this->index();
    }

    /**
      +------------------------------------------------------------------------------
     * 标记为已读信息
      +------------------------------------------------------------------------------
     */
    public function mark_read() {
        $db   = M('Admin_msg');
        $data = array(
            'id'      => $_POST['id'],
            'is_read' => '1'
        );
        $db->save($data);
    }

    /**
      +------------------------------------------------------------------------------
     * 回复短信
      +------------------------------------------------------------------------------
     */
    public function reply() {
        $msg = array(
            'msg_title'     => '回复：' . $_REQUEST['msg_title'],
            'msg_to'        => $_REQUEST['msg_to'],
            'msg_from'      => $_REQUEST['msg_from'],
            'msg_content'   => $_REQUEST['msg_content'],
            'is_sys_msg'    => $_REQUEST['is_sys_msg']
        );

        $this->assign('title', '新信息');
        $this->assign('action', 'do_reply');
        $this->assign('msg', $msg);
        $this->display('./Public/admin/msg_reply.html');
    }

    public function do_reply() {
        $db   = M('User_msg');
        $data = $this->get_form_attr($_POST, "reply");

        if ($data == FALSE) {
            $this->error('信息错误！');
        }

        $msg = $data[0];
        $is_sys_msg = $data[1];
        $result = $db->add($msg);
        $this->show_result_msg($result, '回复信息成功', '回复信息失败！', '?s=admin/msg/show');
    }

    /**
      +------------------------------------------------------------------------------
     * 读信息
      +------------------------------------------------------------------------------
     */
    public function read() {
        $db = M('Admin_msg');
        $id = (int) $_GET['id'];
        $data = array(
            'id' => $id,
            'is_read' => '1'
        );
        $db->save($data);

        $msg = $db->where("`id`='" . $id . "'")->select();
        $this->assign('msg', $msg['0']);
        $this->assign('title', '信息');
        $this->display('./Public/admin/msg_read.html');
    }

    /**
      +------------------------------------------------------------------------------
     * 显示信息
      +------------------------------------------------------------------------------
     */
    public function show() {
        import("ORG.Util.Page");   // 导入分页类
        $db = M('Admin_msg');
        $id = Cookie::get('admin_id');
        $list = $db->where("`msg_to`='" . $id . "'")->order('id desc')->page($page_no . ',' . $this->per_page)->select();
        $this->assign('list', $list);  // 赋值数据集

        $total = $db->count();     // 查询满足要求的总记录数
        $page_obj = new Page($total, $this->per_page); // 实例化分页类传入总记录数和每页显示的记录数
        $page_show = $page_obj->show(); // 分页显示输出
        $this->assign('page', $page_show); // 赋值分页输出
        $this->assign('title', '信息列表');
        $this->display('./Public/admin/msg_show.html');
    }

    /**
      +------------------------------------------------------------------------------
     *  删除指定信息
      +------------------------------------------------------------------------------
     */
    public function delete() {
        $db = M('Admin_msg');
        $id = $_REQUEST['id'];
        $result = $db->delete($id);
        $this->show_result_msg($result, '删除信息成功', '删除信息失败！', '?s=admin/msg/show');
    }

    /**
      +------------------------------------------------------------------------------
     * 添加或回复用户信息时，从表单中提取并检测需要的字段信息
     * 返回一个数组
      +------------------------------------------------------------------------------
     */
    protected function get_form_attr($array, $type="add") {
        // 判断输入的用户名和密码是否为空，为空返回FALSE
//        dump($array);
//        exit;
        $msg_content = safe_str($array['msg_content']);
        $msg = array(
            'msg_title' => $array['msg_title'],
            'msg_to' => $array['msg_to'],
            'msg_from' => $array['msg_from'],
            'msg_content' => $msg_content,
            'is_read' => '0',
            'msg_time' => time()
        );
        $is_sys_msg = $array['is_sys_msg'];

        $data = array($msg, $is_sys_msg);
        return $data;
    }

    private function IsNum($in) {
        return ereg("^[1-9]{1,10}$", $in) ? $in : FALSE;
    }

}

?>
