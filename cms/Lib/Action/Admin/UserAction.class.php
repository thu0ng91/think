<?php
/**
 +------------------------------------------------------------------------------
 * UserAction 模块
 +------------------------------------------------------------------------------
 *
 * @author abtest
 */
class UserAction extends BaseAction {
    /**
     * @var int per_page 定义每一页显示的列表行数
     */
    private $per_page = 20;


    /**
     +------------------------------------------------------------------------------
     * 用户管理
     +------------------------------------------------------------------------------
     */
    public function index(){
        $this->assign('title','用户管理');
        redirect('?s=admin/user/show');
    }

    /**
     +------------------------------------------------------------------------------
     * 用户信息
     +------------------------------------------------------------------------------
     */
    public function info(){
        $id             = (int)$_GET['id'];
        $user_db        = M('User');
        $user_info_db   = M('User_info');
        $user_group_db  = M('User_group');

        $user           = $user_db->find($id);
        $info           = $user_info_db->find($id);
        $group          = $user_group_db->find($user['group_id']);

        $this->assign('user', $user);
        $this->assign('info', $info);
        $this->assign('group', $group);
        $this->assign('title', '用户信息');
        $this->display('./Public/admin/user_info.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 显示添加(新建)用户
     +------------------------------------------------------------------------------
     */
    public function add() {
        $group_db   = M('User_group');
        $group  = $group_db->select();
        //去除在后台添加用户时，用户组中包含游客一组
        $count = count($group);
        for($i = 1; $i < $count; $i++){
            $grouplist[$i] = $group[$i];
        }
        $this->assign('grouplist', $grouplist);
        $this->assign('title', '添加用户');
        $this->assign('action', 'do_add');
        $this->display('./Public/admin/user_add.html');
    }

    /**
     +------------------------------------------------------------------------------
     * 添加用户
     +------------------------------------------------------------------------------
     */
    public function do_add(){
        $user_db	= M('User');
        $data		= $this->get_form_attr($_POST, "add");
//        dump($data);
//        exit;
        if($data == FALSE) {
            $this->error('用户信息错误！');
        }

        $data_user      = $data[0];
        $data_user_info = $data[1];
        $result = $user_db->add($data_user);
        if($result != FALSE) {
            // 在后台增加一个用户时，需要同时在User_Info表添加一个对应ID的信息
            $user_info_db   = M('User_info');
            $get_user_id    = $user_db->getLastInsID();
            $data_user_info['id'] = $get_user_id;
            $result = $user_info_db->add($data_user_info);
            if($result == FALSE){
                $user_db->delete($get_user_id);
            }
        }        
        $this->show_result_msg($result, '添加用户成功', '添加用户失败！', '?s=admin/user/user_list');
    }

    /**
     +------------------------------------------------------------------------------
     * 更新用户设置相关参数
     +------------------------------------------------------------------------------
     */
    public function update() {
        $id             = (int)$_POST['id'];
        $db             = M('User');
        $user_info_db   = M('User_info');
        $user           = $db->find($id);
        $info           = $user_info_db->find($id);

        $group_db       = M('User_group');
        $group          = $group_db->select();
        //去除在后台编辑用户信息时，用户组中包含游客一组
        $count = count($group);
        for($i = 1; $i < $count; $i++){
            $grouplist[$i] = $group[$i];
        }
        $this->assign('grouplist', $grouplist);
        $this->assign('user', $user);
        $this->assign('info', $info);
        $this->assign('title', '修改用户信息');
        $this->display('./Public/admin/user_edit.html');
    }

    public function do_update(){
        $id     = (int)$_POST['id'];
        $user_db    = M('User');
        $info_db    = M('User_info');
        $data	= $this->get_form_attr($_POST, "update");
        $user = $data[0];
        $info = $data[1];
        $result	= $user_db->save($user);
        if($result) {
            $result	= $info_db->save($info);
        }
        $this->show_result_msg($result, '用户修改成功', '用户修改失败！', "?s=admin/user/info/id/$id");
    }

    /**
     +------------------------------------------------------------------------------
     * 返回所有用户列表
     +------------------------------------------------------------------------------
     */
    public function user_list() {
        import("ORG.Util.Page");			// 导入分页类
        $user_db        = M('User');
        $group_db       = M('User_group');
        $page_no        = empty($_GET['page']) ? "1" : (int)$_GET['page'];
        //$list         = $user_db->order('id desc')->page($page_no.','.$this->per_page)->select();
        $list           =  $user_db ->field('user.*, user_group.group_name')
                                    ->join('user_group on user.group_id=user_group.id')
                                    ->order('user.id desc')
                                    ->page($page_no.','.$this->per_page)
                                    ->select();
        $this->assign('list', $list);		// 赋值数据集

        $total          = $user_db->count();	// 查询满足要求的总记录数
        $page_obj	= new Page($total, $this->per_page);	// 实例化分页类传入总记录数和每页显示的记录数
        $page_show	= $page_obj->show();	// 分页显示输出
        $this->assign('page', $page_show);	// 赋值分页输出
        $this->assign('title','用户列表');
        $this->display('./Public/admin/user_show.html');
    }

    private function get_group_name($group_id) {
        $group_db       = M('User_group');
        $group_name_array = $group_db->where("`group_id`=".$group_id)->select();
        return $group_name_array[0];
    }

    /**
     +------------------------------------------------------------------------------
     * 删除一般用户
     +------------------------------------------------------------------------------
     * @param int $id 用户ID
     */
    public function delete(){
        $id = $_REQUEST['id'];
        if($id == NULL) $this->error("请输入需要删除的id");

        $user = M('User');
        $result = $user->delete($id);
        if($result != TRUE){
            echo '$result='.$result;
            echo '<br/>';
            echo '$user->getLastSql();='.$user->getLastSql();
            exit;
        }
        if($result != FALSE) {  // 删除用户成功才能继续删除用户信息
            $user_info = M('User_info');
            $result = $user_info->delete($id);
        }
        $this->show_result_msg($result, '删除用户成功', '删除用户失败！', '?s=admin/user/user_list', '?s=admin/user/user_list');
    }

     /**
     +------------------------------------------------------------------------------
     * 添加或修改用户信息时，从表单中提取并检测需要的字段信息
     * 返回一个数组
     +------------------------------------------------------------------------------
     */
    protected function get_form_attr($array, $type="add"){
        // 判断输入的用户名和密码是否为空，为空返回FALSE
        $user_name = safe_str($array['user_name']);
        if( !$user_name/* || !$user_pwd */)
            return FALSE;

        //默认 group_id 为普通用户
        $group_id = (int)$array['group_id'];
        if($group_id == NULL) {
            $group_id = 2;
        }

        $user_data = array(
            'user_name'	=> $user_name,
            'group_id'	=> $group_id,
            'email'     => safe_str($array['email']),
            'nickname'	=> safe_str($array['nickname'])
        );

        $user_pwd  = safe_str($array['user_pwd']);
        $user_dpwd = safe_str($array['user_dpwd']);
        if($user_pwd != $user_dpwd)
            return FALSE;

        if($user_pwd != NULL){
            $user_data['user_pwd'] = MD5($user_pwd);
        }

        $is_activation = $array['is_activation'];
        if($is_action == '0'){
            $user_data['is_activation'] = (int)($is_activation);
            $user_data['active_code']   = randomkeys();
        }else{
            $user_data['is_activation'] = (int)($is_activation);
        }

        $info_data = array(
            'pwd_quiz'          => safe_str($array['pwd_quiz']),
            'pwd_answer'        => safe_str($array['pwd_answer']),
            'pwd_protect_quiz'  => safe_str($array['pwd_protect_quiz']),
            'pwd_protect_answer'=> safe_str($array['pwd_protect_answer']),
            'real_name'         => safe_str($array['real_name']),
            'email'             => safe_str($array['email']),
            'sex'               => safe_str($array['sex']),
            'msn'               => safe_str($array['msn']),
            'qq'                => safe_str($array['qq']),
            'birthday'          => safe_str($array['birthday']),
            'address'           => safe_str($array['address']),
            'accumulate'        => safe_str($array['accumulate']),
            'vip_money'         => safe_str($array['vip_money']),
            'post_nums'         => safe_str($array['post_nums']),
            'collecte_nums'     => safe_str($array['collecte_nums']),
            'register_time'     => safe_str($array['register_time']),
            'come_from'         => safe_str($array['come_from'])
        );

        $user_pwd = safe_str($array['user_pwd']);
        $user_dpwd = safe_str($array['user_dpwd']);
        if($user_pwd != $user_dpwd){
            return FALSE;
        }

        if($user_pwd != NULL){
            $user_data['user_pwd'] = MD5($user_pwd);
        }

        if($type != "add"){
            //update
            $user_data['id'] = (int)$array['id'];
            $info_data['id'] = (int)$array['id'];
        }

        $data = array( $user_data, $info_data );

        return $data;
    }   
}

?>
