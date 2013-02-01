<?php
/**
 * 订单管理模块
 *
 * @author flashfxp
 */
class OrderAction extends BaseAction {
	// 管理首页
	public function index(){
		$order_obj	= M('Order_list');
		$list		= $order_obj
					->field('order_list.*, user.user_name, order_payment.pay_tname')
					->join(' user on order_list.user_id=user.id')
					->join(' order_payment on order_list.pay_tid=order_payment.pay_tid')
					->select();
		$status		= array('1'=>'等待支付', '2'=>'支付成功', '0'=>'支付失败');
		$this->assign('list', $list);
		$this->assign('status', $status);
		$this->display('./Public/admin/order.html');
	}
}
?>