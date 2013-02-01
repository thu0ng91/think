<?php

/**
 * 用户后台首页
 *
 * @author flashfxp
 */
class IndexAction extends MainAction {

    public function index() {
        if (Cookie::is_set('user_id')) {
            $module = empty($_GET['t']) ? 'user' : $_GET['t'];
            $this->assign('module', $module);

            $user_db = M('User');
            $user_info_db = M('User_info');
            $id = (int) Cookie::get('user_id');
            $user = $user_db->find($id);
            $info = $user_info_db->find($id);
            $this->assign('user', $user);
            $this->assign('info', $info);

            $this->assign('msg_unread', msg_unread_count());
            $this->display(TEMPLATE_PATH . '/user/index.html');
        } else {
            redirect('index.php?s=login/');
        }
    }

}

?>
