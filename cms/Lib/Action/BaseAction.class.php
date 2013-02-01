<?php
/*
 * 自定义基类，所有Admin分组action的父类
 *
 * @author flashfxp
 */

class BaseAction extends Action {

    /**
     +------------------------------------------------------------------------------
     * ACTION NAME 数组定义
     +------------------------------------------------------------------------------
     */
    protected $MENU_ARRAY = array(
        'config' => 0, 'admin' => 0, 'admingroup' => 0, 'links' => 0, 'ads' => 0, 'msg' => 0,
        'serial' => 1, 'book' => 1, 'booksort' => 1, 'recommend' => 1, 'review' => 1, 'vote' => 1, 'work' => 1, 'ebook' => 1, 'html' => 1,'search' => 1, 'chapter' => 1,
        'user' => 2, 'usergroup' => 2,
        'collector' => 3, 'temp' => 3, 'resource' => 3,
        'order' => 4, 'payment' => 4,
        'style' => 5, 'tpl' => 5,
        'backup' => 6,'restore'=>6
        );

    protected $GROUP_AUTH = array(
        array('config' =>1, 'admin' =>2, 'admingroup' =>3, 'links' =>4, 'ads' => 5, 'msg' =>6),
        array('serial' => 1, 'book' => 2, 'booksort' => 3, 'recommend' => 4, 'review' => 5, 'vote' => 6, 'work' => 7, 'ebook' => 8, 'html' => 9, 'search' => 10, 'chapter' => 11),
        array('user' => 1, 'usergroup' => 2),
        array('collector' => 1, 'temp' => 2, 'resource' => 3), //,
        array('order' => 1, 'payment' => 2), //,
        array('style' => 1, 'tpl' => 1),
        array('backup' => 1, 'restore' => 2), //,
    );

    public function  __construct() {
		parent::__construct();
		$this->check_admin();
	}
	
	// 后台登陆用户权限判断
	protected function check_admin(){
		$module_name	= strtolower(MODULE_NAME);
		$action_name	= strtolower(ACTION_NAME);
        if(!in_array($module_name, explode(',',C('NOT_AUTH_MODULE')))){	//不需要认证的模块除外
			if(!in_array($action_name, explode(',',C('NOT_AUTH_ACTION')))) {//不需要认证的操作除外
                $menu_auth_id = $this->MENU_ARRAY[$module_name];
                $sub_auth_array = $this->GROUP_AUTH[$menu_auth_id];
                $sub_menu_auth_id = $sub_auth_array[$module_name];
				//检查登录
				if(!Cookie::is_set('admin_id')){
					$this->assign("jumpUrl","index.php?s=admin/login");
					$this->error('对不起,您还没有登录！');
				}

                if($module_name != 'index'){
                    $auth_array = explode(',', Cookie::get('admin_group_auth'));
                    $auth = str_split($auth_array[$menu_auth_id]);
                    if($auth[0] != 1){
                        $this->error('非法操作！');
                    }elseif($auth[$sub_menu_auth_id] != 1){
                        $this->error('非法操作！');
                    }
                }
			}
		}
    }

	/**
     +-----------------------------------------------------------------------------
	 * 根据结果进行页面跳转和信息显示
     +-----------------------------------------------------------------------------
	 */
	protected function show_result_msg($result, $success_msg, $error_msg, $success_url='', $error_url=''){
		$ajax = isset($_REQUEST['ajax']) ? true : false;

		if($result){
			!$ajax && $this->assign('jumpUrl', $success_url);
			$this->success($success_msg, $ajax);
		}else{
			!$ajax && $this->assign('jumpUrl', $error_url);
			$this->error($error_msg, $ajax);
		}
	}

    /**
     +-----------------------------------------------------------------------------
     * 根据主键修改指定字段值
     +-----------------------------------------------------------------------------
     */
	protected function set_value($model, $id_name, $id_array, $key, $value){
		$array		= (array)$id_array;
		$model_obj	= M($model);
		foreach($array as $id){
			$id = (int)$id;
			if($id < 1){ continue; }
			$model_obj->where($id_name.'='.$id)->setField($key, $value);
		}
		$this->show_result_msg(true, '操作成功！', '');
	}
}
?>