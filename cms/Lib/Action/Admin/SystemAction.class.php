<?php
/**
 * 系统管理模块
 *
 * @author flashfxp
 */
class SystemAction extends BaseAction {
    public function index(){
        $this->assign('title', '系统管理');
        $this->display();
    }
}
?>
