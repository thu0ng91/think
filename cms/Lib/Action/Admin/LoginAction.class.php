<?php
/**
 * 后台管理用户登陆
 *
 * @author flashfxp
 */
class LoginAction extends BaseAction {
    /**
     +------------------------------------------------------------------------------
     * 显示后台登陆页面
     +------------------------------------------------------------------------------
     */
    public function index(){
        $this->assign('title', '管理用户登陆');
        $this->display('./Public/admin/login.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 登录操作，1、通过user_name查找是否存在此用户
     *          2、匹配密码是否相同
     +------------------------------------------------------------------------------
     */
    public function login(){
        $check_code = MD5($_POST['check_code']);
        if($check_code != $_SESSION['verify']) $this->error('验证码错误!');


        $admin_name = safe_str($_REQUEST['admin_name']);
        $db = M('Admin');
        $admin = $db->where("admin_name='".$admin_name."'")->find();
        if(empty($admin)) $this->error('无此用户');

        $admin_pwd = MD5($_REQUEST['admin_pwd']);
        if( $admin['admin_pwd'] != $admin_pwd){
            $this->error('用户名密码错误！');
        } else {
            // 匹配相同，登录成功，跳转
            $this->info_to_cookie($admin['id']);
            // 最新登陆时间和IP，写入数据库
            $admin['last_login'] = time();
            $admin['last_login_ip'] = get_client_ip();
            $db->save($admin);
            redirect('?s=admin/');
        }
    }
//      Test code
//		Cookie::set('user_id',1);
//		Cookie::set('user_name','fengyu');
//        cookie::set('user_group_auth','11111,1111100000,111000,111,1100,111');
//		$this->assign('jumpUrl','?s=admin/');
//		$this->success("登陆成功！");

    /**
     +------------------------------------------------------------------------------
     * 用户信息Cookie写入
     +------------------------------------------------------------------------------
     * @param int $id
     */
    public function info_to_cookie($id){
        if($id){
            $db              = M('Admin');
            $group_db        = M('Admin_group');
            $user            = $db->find($id);
            $user_group_auth = $group_db->find($user['group_id']);
            Cookie::set('admin_id'  , $user['id']);                         // admin id
            Cookie::set('admin_name', $user['admin_name']);                 // admin name
            Cookie::set('admin_group_id' , $user['group_id']);              // admin group id
            Cookie::set('admin_group_auth',$user_group_auth['authority']);  // admin authority
        }
    }

    /**
     +------------------------------------------------------------------------------
     * 登出操作，1、删除 admin_id, admin_name, admin_group_id, admin_group_auth
     +------------------------------------------------------------------------------
     */
	public function logout(){
        setcookie('admin_id', "", time(), "/");
        setcookie('admin_name', "", time(), "/");
        setcookie('admin_group_id', "", time(), "/");
        setcookie('admin_group_auth', "", time(), "/");
//        Cookie::delete('user_id');
//        Cookie::delete('user_name');
//        Cookie::delete('group_id');
//        Cookie::delete('user_group_auth');
//        Cookie::clear();
       redirect('?s=admin/');
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
