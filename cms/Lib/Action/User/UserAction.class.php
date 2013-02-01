<?php
/**
 +------------------------------------------------------------------------------
 * UserAction 模块
 +------------------------------------------------------------------------------
 *
 * @author abtest
 */

class UserAction extends MainAction {
    /**
     +------------------------------------------------------------------------------
     * 首页显示用户信息
     +------------------------------------------------------------------------------
     */
    public function index(){
        $id             = $this->check_has_login();
        $user_db        = M('User');
        $user_info_db   = M('User_info');

        $user   = $user_db->find($id);
        $info   = $user_info_db->find($id);
        $this->assign('user', $user);
        $this->assign('info', $info);
		$this->assign('title', '用户信息');
        $this->assign('msg_unread',msg_unread_count());
		$this->display(TEMPLATE_PATH.'/user/user.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 修改用户信息
     +------------------------------------------------------------------------------
     */
    public function update(){
        $id             = $this->check_has_login();
        $user_db        = M('User');
        $user_info_db   = M('User_info');

        $user   = $user_db->find($id);
        $info   = $user_info_db->find($id);
        $this->assign('user', $user);
        $this->assign('info', $info);
        $this->assign('title', '修改用户信息');
        $this->assign('msg_unread',msg_unread_count());
        $this->display(TEMPLATE_PATH.'/user/user_edit.html');
    }

    public function do_update(){
        $id         = $this->check_has_login();
        $user_db    = M('User');
        $info_db    = M('User_info');
        $data   = $this->get_form_attr( $_POST );
        $user = $data[0];
        $info = $data[1];
        $result	= $user_db->save($user);
        if($result) {
            $result	= $info_db->save($info);         
        }
        $this->show_result_msg($result, '用户修改成功', '用户修改失败！', '?s=user/index');        
    }

    /**
     +------------------------------------------------------------------------------
     * 用户激活函数
     * 用户激活url http://localhost/cms/index.php?s=user/user/activation/ui/(user_id)/ac/(active_code MD5加密)
     * TestURL: http://localhost/cms/index.php?s=user/user/activation/uid/18/ac/1862dfe502c6376d46f86d35bd3f5204
     +------------------------------------------------------------------------------
     */
    public function activation(){
        $array       = $_REQUEST;
        $user_db     = M('User');
        $id          = $array['uid']; // uid = user_id
        $active_code = $array['ac'];  // ac = active_code
        $user        = $user_db->find($id);
        if($active_code == (MD5($user['active_code']))){
            $is_activation = $user['is_activation'];
            if($is_activation == '0'){
                $user['is_activation'] = '1';
                $result = $user_db->save($user);
                $this->show_result_msg($result, '用户激活成功!', '用户激活失败！', '?s=login', '');
            }else{
                $this->show_result_msg(1, '用户已激活成功!', '', '?s=login', '?s=login');
            }
        }
    }

    /**
     +------------------------------------------------------------------------------
     * 添加或修改用户信息时，从表单中提取并检测需要的字段信息
     * 返回一个数组
     +------------------------------------------------------------------------------
     */
    protected function get_form_attr( $array ){
        $user_data['nickname']  = safe_str($array['nickname']);

        $info_data = array(
            'real_name'     => safe_str($array['real_name']),
            'email'         => safe_str($array['email']),
            'sex'           => safe_str($array['sex']),
            'msn'           => safe_str($array['msn']),
            'qq'            => safe_str($array['qq']),
            'birthday'      => safe_str($array['birthday']),
            'address'       => safe_str($array['address']),
            'accumulate'    => safe_str($array['accumulate']),
            'vip_money'     => safe_str($array['vip_money']),
            'post_nums'     => safe_str($array['post_nums']),
            'collecte_nums' => safe_str($array['collecte_nums']),
            'register_time' => safe_str($array['register_time']),
            'come_from'     => safe_str($array['come_from'])
        );

        $user_pwd = safe_str($array['user_pwd']);
        $user_dpwd = safe_str($array['user_dpwd']);
        if($user_pwd != $user_dpwd){
            return FALSE;
        }

        if($user_pwd != NULL){
            $user_data['user_pwd'] = MD5($user_pwd);
        }

        $user_data['id'] = (int)$array['id'];
        $info_data['id'] = (int)$array['id'];

        $data = array( $user_data, $info_data );

        return $data;
    }

	/**
     +-----------------------------------------------------------------------------
	 * 根据结果进行页面跳转和信息显示
     +-----------------------------------------------------------------------------
	 */
	protected function show_result_msg($result, $success_msg, $error_msg, $success_url='', $error_url=''){
		if($result){
			$this->assign('jumpUrl', $success_url);
			$this->success($success_msg);
		}else{
			$this->assign('jumpUrl', $error_url);
			$this->error($error_msg);
		}
	}
}
?>
