<?php

/**
 * 用户充值中心
 *
 * @author flashfxp
 */

class ChargeAction extends MainAction {
    public function index() {
        if (Cookie::is_set('user_id')) {
            $user_db = M('User');
            $user_info_db = M('User_info');
            $id = (int) Cookie::get('user_id');
            $user = $user_db->find($id);
            $info = $user_info_db->find($id);
            $this->assign('user', $user);
            $this->assign('info', $info);

            $this->assign('msg_unread', msg_unread_count());
            $this->display(TEMPLATE_PATH . '/user/charge.html');
        } else {
            redirect('index.php?s=login/');
        }
    }

	// 显示充值确认页
	public function confirm(){
		$pay_tid	= (int)$_POST['pay_tid'];
		$payment	= M('Order_payment')->find($pay_tid);
		if(!$payment){
			$this->error('非法操作，请选择正确的支付方式！');
		}
		$config		= (array)json_decode($payment['pay_config']);

		// 添加本地订单
		$money		= round(floatval($_POST['charge_amount']), 2);
		if($money < 0.01){
			$this->error('充值金额不能少于 0.01 元！');
		}
		$data		= array(
			'user_id'	=> (int)Cookie::get('user_id'),
			'subject'	=> '会员充值',
			'money'		=> $money,
			'note'		=> '',
			'order_status' => 1,
			'order_time'=> time(),
			'pay_tid'	=> $pay_tid,
			'pay_time'	=> null
		);
		$order_obj	= M('Order_list');
		$result		= $order_obj->add($data);
		if(!$result){
			$this->error('订单写入错误，请返回重新提交！');
		}
		$data['order_id']	= $order_obj->getLastInsId();
		$data['user_name']	= $_POST['charge_id'];
		$this->assign('order', $data);

		if($pay_tid == 1){
			$this->alipay($data, $config);
		}else{
			$this->error('请选择正确的支付方式！');
		}
		$this->display(TEMPLATE_PATH . '/user/charge_confirm.html');
	}

	// 支付宝提交
	protected function alipay($order, $config){
		require_once("charge/alipay/alipay_service.php");

		//扩展功能参数——默认支付方式
		$pay_mode	  = $_POST['pay_bank'];
		if($pay_mode == "directPay"){
			$paymethod    = "directPay";	//默认支付方式，可选：bankPay(网银); cartoon(卡通); directPay(余额); CASH(网点支付)
			$defaultbank  = "";
		}else{
			$paymethod    = "bankPay";
			$defaultbank  = $pay_mode;		//默认网银代号，代号列表见http://club.alipay.com/read.php?tid=8681379
		}

		//构造要请求的参数数组，无需改动
		$parameter = array(
			"service"			=> "create_direct_pay_by_user",	//接口名称，不需要修改
			"payment_type"		=> "1",               			//交易类型，不需要修改
			//获取配置参数值
			"partner"			=> $config['partner'],
			"seller_email"		=> $config['email'],
			"return_url"		=> '',
			"notify_url"		=> 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/charge/alipay/notify.php',
			"_input_charset"	=> 'utf-8',
			"show_url"			=> '',				//网站商品的展示地址
			//从订单数据中动态获取到的必填参数
			"out_trade_no"		=> $order['order_id'],
			"subject"			=> $order['subject'],
			"body"				=> '',
			"total_fee"			=> $order['money'],
			//扩展功能参数——网银提前
			"paymethod"			=> $paymethod,
			"defaultbank"		=> $defaultbank,
		);

		//构造请求函数
		$alipay		= new alipay_service($parameter, $config['key'], 'MD5');
		$sHtmlText	= $alipay->build_form();

		$this->assign('status', $sHtmlText);
	}
}
?>
