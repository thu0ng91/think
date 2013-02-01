<?php
require_once("alipay_notify.php");
require_once("../config.php");
$rt	= mysql_query('select `pay_config` from `order_payment` where `pay_tid`=1', $link);
$row = mysql_fetch_array($rt);
$config = (array)json_decode($row['pay_config']);
$alipay = new alipay_notify($config['partner'], $config['key'], 'MD5', 'utf-8', 'http');    //构造通知函数信息
$verify_result = $alipay->notify_verify();  //计算得出通知验证结果

if($verify_result){	//验证成功
    //获取支付宝的通知返回参数
    if($_POST['trade_status'] == 'TRADE_FINISHED' ||$_POST['trade_status'] == 'TRADE_SUCCESS'){
		$oid	= $_POST['out_trade_no'];	//获取支付宝传递过来的订单号
        $rt		= mysql_query('select * from `order_list` where `order_id`='.$oid, $link);
        if(mysql_num_rows($rt) > 0){
        	$order	= mysql_fetch_array($rt);
        	if($order['order_status'] == 1){
        		mysql_query('update `order_list` set `order_status`=2, `pay_time`='.time().' where `order_id`='.$oid);
        		$money = (float)$_POST['total_fee'];
        		$total = $money * $site_config['book']['vip_convert'];
        		mysql_query('update `user_info` set `vip_money`=`vip_money`+'.$total.' where `id`='.$order['user_id']);
        	}
        }
		echo "success";		//请不要修改或删除
    }else{
        echo "success";		//其他状态判断。普通即时到帐中，其他状态不用判断，直接打印success。
    }
}else{	//验证失败
    echo "fail";
}
?>