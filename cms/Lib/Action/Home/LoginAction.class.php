<?php
/**
 +------------------------------------------------------------------------------
 * LoginAction 一般用户登录模块，通过user_name来查到
 +------------------------------------------------------------------------------
 * @author abtest
 */

class LoginAction extends MainAction {
    /**
     +------------------------------------------------------------------------------
     * 用户登录
     +------------------------------------------------------------------------------
     */
    public function index(){
        $this->assign('title', '用户登录');
        $this->display(TEMPLATE_PATH.'/home/login.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 登录操作，1、通过user_name查找是否存在此用户
     *          2、匹配密码是否相同
     +------------------------------------------------------------------------------
     */
    public function login(){
        $check_code = MD5($_REQUEST['check_code']);
		$ajax = isset($_REQUEST['ajax']) ? true : false;
        if($check_code != $_SESSION['verify']) $this->error('验证码错误!', $ajax);

        $user_name = safe_str($_REQUEST['user_name']);
        $user_db = M('User');
        $user = $user_db->where("user_name='".$user_name."'")->find();
        if(empty($user)) $this->error('用户名不存在', $ajax);

        $user_pwd = MD5($_REQUEST['user_pwd']);
        if( $user['user_pwd'] != $user_pwd){
            $this->error('用户名密码错误！', $ajax);
        } else {
            // 匹配相同，登录成功，跳转
            $this->info_to_cookie($user['id'], $_REQUEST['cookie_time']);
            // 最新登陆时间和IP，写入数据库
            $user['last_login'] = time();
            $user['last_login_ip'] = get_client_ip();
            $user_db->save($user);
			if($ajax){
				$this->success('登录成功！', true);
			}else{
				$url	= Cookie::is_set('last_url') ? Cookie::get('last_url') : '?s=user/';
				setcookie('last_url', "", time(), '/');
				redirect($url);
			}
        }
    }

    /**
     +------------------------------------------------------------------------------
     * 用户信息Cookie写入
     +------------------------------------------------------------------------------
     * @param int $id
     */
    public function info_to_cookie($id, $time){
        if($id){
            $user_db = M('User');
            $user_group_db = M('User_group');
            $user = $user_db->find($id);
            $user_group_auth = $user_group_db->find($user['group_id']);
            Cookie::set('user_id', $user['id'], $time);                             // user id
            Cookie::set('user_name', $user['user_name'], $time);                    // user name
            Cookie::set('user_group_auth', $user_group_auth['authority'], $time);   // user group authority
        }
    }

    /**
     +------------------------------------------------------------------------------
     * 登出操作，1、删除 user_id, user_name, group_id, user_group_auth
     +------------------------------------------------------------------------------
     */
    public function logout(){
        setcookie('user_id', "", time(), '/');
        setcookie('user_name', "", time(), '/');
        setcookie('user_group_auth', "", time(), '/');
        redirect(__ROOT__);
    }

    /**
     +------------------------------------------------------------------------------
     * 生成验证码
     +------------------------------------------------------------------------------
     */
    public function check_code(){
        verify();
    }
}

?>
