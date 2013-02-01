<?php
/**
 * 书籍管理模块(已验证)
 *
 * @author delphi
 */
class LinksAction extends BaseAction {
	// 友情链接管理
    private $per_page = 20;

    public function index(){
            import("ORG.Util.Page");			// 导入分页类
            $db	= M('Links');
            $page_no	= empty($_GET['page']) ? "1" : (int)$_GET['page'];
            $list		= $db->order('id desc')->page($page_no.','.$this->per_page)->select();
            $this->assign('list', $list);		// 赋值数据集

            $total      = $db->count();	// 查询满足要求的总记录数
            $page_obj	= new Page($total, $this->per_page);	// 实例化分页类传入总记录数和每页显示的记录数
            $page_show	= $page_obj->show();	// 分页显示输出
            $this->assign('page', $page_show);	// 赋值分页输出
            $this->assign('title','友情链接列表');
            $this->display('./Public/admin/links.html');
	}

	// 添加友情链接
	public function add(){
		$this->assign('title', '添加友情链接');
		$this->assign('action', 'do_add');
		$this->display('./Public/admin/links_add.html');
	}

	// 执行添加动作
	public function do_add(){
		$data		= array(
                    "sitename"  => safe_str($_POST['sitename']),
                    "siteurl"  => safe_str($_POST['siteurl']),
                    "siteinstro"  => safe_str($_POST['siteinstro']),
                    "orderid"  => (int)$_POST['orderid'],
                    "status"  => (int)$_POST['status'],
                    "adminid"	=> (int)Cookie::get('admin_id'),
                    "posttime" => time(),
                );
		$result		=  M('Links') ->add($data);
		$this->show_result_msg($result, '添加友情链接成功', '添加友情链接失败！', '?s=admin/links');
	}

	// 修改链接
	public function update(){
		$id              = (int)$_GET['id'];
		$links		 = M('Links')->find($id);

		$this->assign('links', $links);
		$this->assign('title','修改链接信息');
		$this->assign('action', 'do_update');
		$this->display('./Public/admin/links_add.html');
	}

	// 执行修改动作
	public function do_update(){
		$id             = (int)$_POST['id'];
		$data		= array(
                        "sitename"      => safe_str($_POST['sitename']),
                        "siteurl"       => safe_str($_POST['siteurl']),
                        "siteinstro"    => safe_str($_POST['siteinstro']),
                        "orderid"       => (int)$_POST['orderid'],
                        "status"        => (int)$_POST['status'],
                );
                $data['id']     = $id;
                $links_obj      = M('Links');
		$result		= $links_obj->save($data);
		$this->show_result_msg($result, '链接信息修改成功', '链接信息修改失败！');
	}

	/*
	 *  删除链接
	 */
	public function delete(){
		$id             = (int)$_GET['id'];
		$links_obj	= M('Links');
		$result	= $links_obj->delete($id);

		$this->show_result_msg(true, "删除链接成功", '删除链接失败', '?s=admin/links');
	}


}
?>
