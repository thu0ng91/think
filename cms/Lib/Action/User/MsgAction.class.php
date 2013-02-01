<?php

/**
  +------------------------------------------------------------------------------
 * 前台用户消息管理
  +------------------------------------------------------------------------------
 * @author abtest
 */
class MsgAction extends MainAction {

    // 用户个人信息列表
    public function index() {
        $user_msg_db = M('User_msg');
        $user_id = $this->check_has_login();
        $list = $user_msg_db->where("`msg_to`=" . $user_id)->order("id desc")->select();

        $this->assign('list', $list);
        $this->assign('title', '用户个人消息');
        $this->assign('msg_unread',msg_unread_count());
        $this->display(TEMPLATE_PATH . '/user/msg.html');
    }

    /**
      +------------------------------------------------------------------------------
     * 读信息
      +------------------------------------------------------------------------------
     */
    public function read() {
        $db = M('User_msg');
        $id = (int) $_GET['id'];

        // 标记信息为已读
        $data = array(
            'id' => $id,
            'is_read' => '1'
        );
        $db->save($data);

        $msg = $db->where("`id`='" . $id . "'")->select();
        $this->assign('msg', $msg['0']);
        $this->assign('title', '信息');
        $this->assign('msg_unread',msg_unread_count());
        $this->display(TEMPLATE_PATH . './user/msg_read.html');
    }

    /**
      +------------------------------------------------------------------------------
     * 用户信息管理时，读发件箱信息时，不设置信息为读取
      +------------------------------------------------------------------------------
     */
    public function from_read() {
        $db = M('User_msg');
        $id = (int) $_GET['id'];

        $msg = $db->where("`id`='" . $id . "'")->select();
        $this->assign('msg', $msg['0']);
        $this->assign('title', '信息');
        $this->assign('msg_unread',msg_unread_count());
        $this->display(TEMPLATE_PATH . './user/msg_read.html');
    }

    /**
      +------------------------------------------------------------------------------
     * 标记为已读信息
      +------------------------------------------------------------------------------
     */
    public function mark_read() {
        $db = M('User_msg');
        $data = array(
            'id'      => (int) $_GET['id'],
            'is_read' => '1'
        );
        $db->save($data);
    }

    /**
      +------------------------------------------------------------------------------
     * 新建信息
     * TODO:下一阶段将在发送个人信息时可选多个用户ID
     *      或者以用户名来发送信息
      +------------------------------------------------------------------------------
     */
    public function add() {
        $this->assign('title', '新建信息');
        $this->assign('msg_unread',msg_unread_count());
        $this->display(TEMPLATE_PATH . './user/msg_new.html');
    }

    public function do_add() {
        $msg_title      = $_POST['msg_title'];
        $msg_content    = $_POST['msg_content'];
        $msg_to         = $_POST['msg_to'];
        $msg_from       = Cookie::get('user_id');
        $msg_from_name  = Cookie::get('user_name');

        $db = M('User_msg');
        $user_db = M('User');

        $user = $user_db->where("`id`='".$msg_to."'")->select();
        $msg_to_name = $user['0']['user_name'];

        // 组合信息
        $msg = array(
            'msg_from'      => $msg_from,
            'msg_from_name' => $msg_from_name,
            'msg_to'        => $msg_to,
            'msg_to_name'   => $msg_to_name,
            'msg_title'     => $msg_title,
            'msg_content'   => $msg_content,
            'is_read'       => '0',    // is_read 默认是 0
            'msg_time'      => time()
        );

        $result = $db->add($msg);
        $this->show_result_msg($result, '发送信息成功', '发送信息失败！', '?s=user/msg');
    }

    /**
      +------------------------------------------------------------------------------
     * 回复短信
      +------------------------------------------------------------------------------
     */
    public function reply() {
        $msg = array(
            'msg_title'     => '回复：' . $_POST['msg_title'],
            'msg_to'        => $_POST['msg_from'],
            'msg_from'      => $_POST['msg_to'],
            'msg_from_name' => $_POST['msg_from_name']
        );

        $this->assign('title', '新信息');
        $this->assign('msg', $msg);
        $this->assign('msg_unread',msg_unread_count());
        $this->display(TEMPLATE_PATH . '/user/msg_reply.html');
    }

    public function do_reply() {
        $db = M('User_msg');
        $msg = array(
            'msg_title'     => $_POST['msg_title'],
            'msg_to'        => $_POST['msg_to'],
            'msg_from'      => $_POST['msg_from'],
            'msg_from_name' => Cookie::get('user_name'),
            'msg_content'   => $_POST['msg_content'],
            'is_read'       => '0',
            'msg_time'      => time()
        );

        $result = $db->add($msg);
        $this->show_result_msg($result, '回复信息成功', '回复信息失败！', '?s=user/msg');
    }

    /**
      +------------------------------------------------------------------------------
     *  删除指定信息
      +------------------------------------------------------------------------------
     */
    public function delete() {
        $db = M('User_msg');
        $id = $_REQUEST['id'];
        $result = $db->delete($id);
        $this->index();
    }

    /**
      +------------------------------------------------------------------------------
     * 发件箱
      +------------------------------------------------------------------------------
     */
    public function msg_from() {
        $db = M('User_msg');
        $id = Cookie::get('user_id');

        $msg_list = $db->where("`msg_from`='" . $id . "'")->order("id desc")->select();

        $this->assign('list', $msg_list);
        $this->assign('title', '信息');
        $this->assign('msg_unread',msg_unread_count());
        $this->display(TEMPLATE_PATH . './user/msg_from.html');
    }

    /**
      +------------------------------------------------------------------------------
     * 收件箱
      +------------------------------------------------------------------------------
     */
    public function msg_to() {
        $db = M('User_msg');
        $id = Cookie::get('user_id');

        $msg_list = $db->where("`msg_to`='" . $id . "'")->order("id desc")->select();

        $this->assign('list', $msg_list);
        $this->assign('title', '信息');
        $this->assign('msg_unread',msg_unread_count());
        $this->display(TEMPLATE_PATH . './user/msg_to.html');
    }

    /**
      +------------------------------------------------------------------------------
     * 未读消息
      +------------------------------------------------------------------------------
     */
    public function msg_unread() {
        $db = M('User_msg');
        $id = Cookie::get('user_id');
        $where = array (
            'msg_to' => $id,
            'is_read' => '0'
        );

        $msg_list = $db->where( $where )->order("id desc")->select();

        $this->assign('list', $msg_list);
        $this->assign('title', '未读信息');
        $this->assign('msg_unread',msg_unread_count());
        $this->display(TEMPLATE_PATH . './user/msg_unread.html');
    }

}

?>
