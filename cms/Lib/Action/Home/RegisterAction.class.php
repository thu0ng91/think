<?php
/**
 +------------------------------------------------------------------------------
 * RegestAction 一般用户注册模块
 +------------------------------------------------------------------------------
 *
 * @author abtest
 */
class RegisterAction extends MainAction {
    /**
     +------------------------------------------------------------------------------
     * 用户注册
     +------------------------------------------------------------------------------
     */
    public function index() {
        $this->assign('title', '用户注册');
        $this->display(TEMPLATE_PATH.'/home/register.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 登录操作，通过user_name查找是否已经存在此用户名
     +------------------------------------------------------------------------------
     */
    public function register() {
        $check_code = MD5($_POST['check_code']);
        if($check_code != $_SESSION['verify']) $this->error('验证码错误!');

        $user_db = M('User');
        
        $registe_name = $this->IsName(safe_str($_POST['registe_name']));
        if($registe_name == FALSE) $this->error('用户名错误');
        
        $registe_email = $this->IsMail(safe_str($_POST['registe_email']));
        if($registe_email == FALSE) $this->error('注册邮箱错误');

        $registe_pwd = safe_str($_POST['registe_pwd']);
        $registe_verify_pwd = safe_str($_POST['registe_verify_pwd']);
        if(($registe_pwd == $registe_verify_pwd) && !empty($registe_pwd)) {
            // 用户名监测
            $user_name = $user_db->where("user_name='".$registe_name."'")->find();
            if(empty($user_name)) { // 此用户名未被使用，注册继续进行
                $user = array(
                    'user_name' => $registe_name,
                    'user_pwd'  => $registe_pwd,
                    'email'     => $registe_email
                );

                $result = $this->add_user($user);
                if($result){
					$this->assign('jumpUrl', __ROOT__);
					$this->success('注册成功！');
				}else{
					$this->success('注册失败！');
				}
            } else {
                //TODO: 用户名已被占用
                $this->error('用户名已被占用');
            }
        } else {
            //TODO: 两次密码不匹配的后续操作
            $this->error('两次密码不匹配');
        }
    }

    /**
     +------------------------------------------------------------------------------
     * 保存一般用户设置相关参数
     +------------------------------------------------------------------------------
     */
    private function add_user($register_info) {
        $user_db = M('User');
        $user_info_db = M('User_info');
        
        $user = Array(
            //'id'           => (int)$_POST['id'];
            'user_name'      =>      $register_info['user_name'],
            'user_pwd'       =>  MD5($register_info['user_pwd']),
            //'group_id'       => (int)$_POST['group_id'], //默认值为1
            'email'          =>      $register_info['email'],
            //'category'       =>      $_POST['category'], //默认值为0
            //'nickname'       =>      $_POST['nickname'],
            'last_login'     => (int)time(),
            'last_login_ip'  =>      get_client_ip(),
            'active_code'    =>      randomkeys()
        );

        /*
        $data_user_info = Array(
            //'id'                 => (int)$_POST['id'];
            'pwd_quiz'             =>      $_POST['pwd_quiz'],
            'pwd_answer'           =>      $_POST['pwd_answer'],
            'pwd_protect_quiz'     =>      $_POST['pwd_protect_quiz'],
            'pwd_protect_answer'   =>      $_POST['pwd_protect_answer'],
            'real_name'            =>      $_POST['real_name'],
            'address'              =>      $_POST['address'],
            'msn'                  =>      $_POST['msn'],
            'birthday'             =>      $_POST['birthday'],
            'accumulate'           => (int)$_POST['accumulate'],
            'vip_money'            => (int)$_POST['vip_money'],
            'post_nums'            => (int)$_POST['post_nums'],
            'collecte_nums'        => (int)$_POST['collecte_nums'],
            'register_time'        =>      time(),
            'sex'                  =>      $_POST['sex'],
            'qq'                   =>      $_POST['qq'],
            'come_from'            =>      $_POST['come_from']
        );*/

        // 创建新用户
        $result = $user_db->add($user);
		if($result){
			$get_user_id = $user_db->getLastInsID();
			$data_user_info['id'] = $get_user_id;
			$result = $user_info_db->add($data_user_info);
			if(!$result){
				$user_db->delete($get_user_id);
			}
		}
		return $result;
    }
    
    /**
     +------------------------------------------------------------------------------
     * IsUsername函数:检测是否符合用户名格式
     * $RegExp是要进行检测的正则语句
     * 返回值:符合用户名格式返回用户名,不是返回false
     +------------------------------------------------------------------------------
     * @param string $name $name是要检测的用户名参数
     */
    private function IsName($name){
        $RegExp='/^[a-zA-Z0-9u4e00-u9fa5_-]{3,16}$/'; //由大小写字母跟数字组成并且长度在3-16字符直接
        return preg_match($RegExp, $name) ? $name : FALSE;
    }

    /**
     +------------------------------------------------------------------------------
     * IsMail函数:检测是否为正确的邮件格式
     * 返回值:是正确的邮件格式返回邮件,不是返回false
     +------------------------------------------------------------------------------
     * @param string $mail $mail是要检测的注册邮箱参数
     */
    private function IsMail($mail){
        $RegExp='^[_\.0-9a-z]+@([0-9a-z][0-9a-z]+\.)+[a-z]{2,3}$';
        return eregi($RegExp, $mail) ? $mail : false;
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
