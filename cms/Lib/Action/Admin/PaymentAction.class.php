<?php
/**
 * 支付方式管理模块
 *
 * @author flashfxp
 */
class PaymentAction extends BaseAction {
	// 管理首页
	public function index(){
		$pay_obj= M('Order_payment');
		$list	= $pay_obj->select();
		$this->assign('list', $list);
		$this->display('./Public/admin/payment.html');
	}

	// 开启或关闭某一支付方式
	public function enabled(){
		$this->set_value('Order_payment', 'pay_tid', $_REQUEST['id'], 'is_enabled', $_REQUEST['value']);
	}

	// 显示修改页面
	public function update(){
		$pay_obj= M('Order_payment');
		$tid	= (int)$_GET['id'];
		$pay	= $pay_obj->find($tid);
		if($pay){
			$config	= (array)json_decode($pay['pay_config']);
			$this->assign('payment', $pay);
			$this->assign('config', $config);
			$this->display('./Public/admin/payment_'.$tid.'.html');
		}else{
			$this->error('非法操作！');
		}
	}

	// 修改支付宝支付参数
	public function alipay(){
		$pay_id	= (int)$_POST['id'];
		$data	= array(
			'partner'	=> $_POST['partner'],
			'key'		=> $_POST['key'],
			'email'		=> $_POST['email']
		);
		$this->save_config($pay_id, $data);
	}

	// 保存支付参数
	protected function save_config($tid, $data){
		$pay_obj	= M('Order_payment');
		$config		= json_encode($data);
		$result		= $pay_obj->save(array('pay_tid'=>$tid, 'pay_config'=>$config));
		$this->show_result_msg($result, '参数修改成功！', '参数修改失败！');
	}


	// 保存基本设置相关参数
	public function base(){
		$data = Array(
			'sort_depth'		=> (int)$_POST['sort_depth'],
			'chapter_in_db'		=> (int)$_POST['chapter_in_db'],
			'chapter_dir'		=> safe_str($_POST['chapter_dir']),
			'chapter_auto_check'=> (int)$_POST['chapter_auto_check'],
			'perpage_top'		=> (int)$_POST['perpage_top'],
			'perpage_book'		=> (int)$_POST['perpage_book'],
			'read_volume'		=> (int)$_POST['read_volume'],
			'read_full'			=> (int)$_POST['read_full'],
			'search_history'	=> (int)$_POST['search_history'],
			'has_channel'		=> (int)$_POST['has_channel'],
		);
		update_config($data, 'book');

		$this->assign('jumpUrl', '?s=admin/serial/index/t/base');
		$this->success('参数修改成功！');
	}

	// 显示支付方式参数设置页面
	public function pay(){

	}
}
?>