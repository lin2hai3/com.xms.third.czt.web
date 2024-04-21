<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/13
 * Time: 21:54
 */

require_once 'application/libraries/RequestUtil.php';
require_once 'application/libraries/BankPay.php';
require_once 'application/libraries/WxPay.php';

class Recharge extends CI_Controller
{
	public function prepay1()
	{
		$id = isset($_POST['id']) ? $_POST['id'] : 0;
		$mid = isset($_POST['mid']) ? $_POST['mid'] : 0;
		$tid = isset($_POST['tid']) ? $_POST['tid'] : 0;
		$sid = isset($_POST['sid']) ? $_POST['sid'] : 0;
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : 0;
		$merOrderId = isset($_POST['order_number']) ? $_POST['order_number'] : 0;
		// $totalAmount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
		$totalAmount = isset($_POST['final_amount']) ? intval($_POST['final_amount']) : 0;
		$openId = isset($_POST['openId']) ? $_POST['openId'] : 0;
		$type = isset($_POST['type']) ? $_POST['type'] : 'room';

		$shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : 0;
		$sid = $shop_id;
		$order_name = isset($_POST['order_name']) ? $_POST['order_name'] : '';

		$return_data = array();

		// 白马荟 林海open_id
		// $openId = 'oV_0Q5RXDR0yQfzAYCDy5nX5ddes';

		$flag = empty($sid) || empty($merOrderId) || empty($totalAmount) || empty($openId) || $totalAmount < 0;

		if($flag) {
			$return_data = array(
				'code' => '9999',
				'msg' => '非法请求，请联系管理员',
			);
			die(json_encode($return_data));
		}

		$_merOrderId = $merOrderId;
		$this->load->model('Paylog_model', 'pay_log');
		$last = $this->pay_log->get_last($_merOrderId);

		$times = 0;
		if (!empty($last)) {
			$times = $last->times + 1;
			$merOrderId .= $times;
		}

		$pay_log = array(
			'member_id' => $member_id,
			'order_type' => 'ROOM',
			'order_sn' => $_merOrderId,
			'trade_no' =>  $merOrderId,
			'times' => $times,
			'amount' => $totalAmount,
			'payment_type' => 'UNIONPAY',
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		);
		$res = $this->pay_log->create($pay_log);

		$subMid = $mid;
		$subTid = $tid;
		/// $mid = '898445173110048';
		/// $tid = '67123758';
		$mid = '898445173110047';
		$tid = '67123274';

		if($shop_id == '439') {
			$mid = '898445173110049';
			$tid = '67123759';
			$subMid = '898445186610001';
			$subTid = '67124258';
		}

		$subAppId = 'wxa9dd96c791e01f15';

		if((empty($subMid) || empty($subTid)) || ($subMid == 'undefined' || $subTid == 'undefined')) {
			//默认商户
			$data = array(
				'merOrderId' => $merOrderId,
				'totalAmount' => $totalAmount,
				'subAppId' => $subAppId,
				'subOpenId' => $openId,
				'mid' => $mid,
				'tid' => $tid,
			);
		}
		else {
			//子商户

			$subAppId = 'wxa9dd96c791e01f15';

			$platformAmount = intval($totalAmount * 0.006 + 0.5);

			$data = array(
				'merOrderId' => $merOrderId,
				'totalAmount' => $totalAmount,
				'subAppId' => $subAppId,
				'subOpenId' => $openId,
				'mid' => $mid,
				'tid' => $tid,
				'divisionFlag' => 'true',
				'platformAmount' => $platformAmount,
				'subOrders' => array(
					array(
						'mid' => $subMid,
						'totalAmount' => $totalAmount - $platformAmount,//$totalAmount * 0.994,
					)
				)
			);
		}

		$data['orderDesc'] = $order_name;

		$res = BankPay::pay($data);
		$res = json_decode($res, true);

		if ($res['errCode'] == 'SUCCESS') {
			if(isset($res['miniPayRequest'])) {

				$this->pay_log->update_status($merOrderId);

				$return_data = array(
					'code' => '0',
					'msg' => '请支付',
					'result' => $res['miniPayRequest'],
					'return' => json_encode($res),
				);

			}
			else {
				$return_data = array(
					'code' => '9998',
					'msg' => '测试环境，无法支付',
					'return' => json_encode($res),
				);
			}
		}
		else {
			$return_data = array(
				'code' => '9999',
				'msg' => $res['errMsg'],
				'return' => json_encode($res),
			);
		}

		die(json_encode($return_data));

		//appId: "wxa9dd96c791e01f15"
		//nonceStr: "a2a0a2c870b84e29ae8e6a7746ff6dc3"
		//package: "prepay_id=wx26154102458113216f797ebdbf9e2e0000"
		//paySign: "jr+kJP3L7jfStcKNllHuZ0gNLRdiuATmyMMnsrw/8H99kd08OOyjv/8S9cNdIGpTjki+Axo/uu5isG8Drnq0WpRQl2hzaE5T78Q3rZr1s+JtSwPci/+9EZ6w4kImmsG5n7XDCremf57ZFL6PZzKMA3qnfXLkFlGxnfPoNM71Krt1ZQpN08OT5cZKtFVd8X8jwww44EClZ91gYGGTxpj88Kb3Q4kzFDEEdIGulu1vDePo83JWPrKLRz81OIkU0ik+n79gB/DY17VjFxMVictu5USvS+yMDEnFwPV2rLiwuUe2Z6kOs0E/9JzljV1KTvonR5Cs5zFkODdHwq+s+8ODUw=="
		//signType: "RSA"
		//timeStamp: "1637912462"
	}

	public function prepay2()
	{
		$app_id = $this->input->get_post('app_id');
		$sid = $this->input->get_post('sid');
		$member_id = $this->input->get_post('member_id');
		$order_number = $this->input->get_post('order_number');
		$total_amount = $this->input->get_post('total_amount');
		$identity = $this->input->get_post('openId');
		$type = $this->input->get_post('type', 'room');

		$app_id = 'wx1b566be082e9f0d9';

		if (empty($app_id)) {
			return Util_helper::result(null, '参数不能为空', -1);
		}

		$pay_no = $order_number;
		$this->load->model('Paylog_model', 'pay_log');
		$last = $this->pay_log->get_last($order_number);

		$times = 0;
		if (!empty($last)) {
			$times = $last->times + 1;
			$pay_no .= $times;
		}

		$pay_log = array(
			'member_id' => $member_id,
			'order_type' => 'ROOM',
			'order_sn' => $order_number,
			'trade_no' => $pay_no,
			'times' => $times,
			'amount' => $total_amount,
			'payment_type' => 'WXPAY',
			'status' => 1,
			'app_id' => $app_id,
			'out_trade_no' => '',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		);
		$res = $this->pay_log->create($pay_log);

		$pay_log = new \stdClass();

		$pay_log->pay_title = '無二SPACE订房订单';
		$pay_log->pay_remark = 'room';
		$pay_log->pay_flag = 'room';
		$pay_log->pay_no = $pay_no;
		$pay_log->pay_amount = $total_amount ;
		$pay_log->pay_ip = $this->input->ip_address();
		$pay_log->identity = $identity;  // open-id

		$return_url = '';
		$notify_url = 'https://linhai.666os.com/v2/index.php/iroom/order/pay_notify/' . $pay_no;


		$wx_apps = $this->config->item('wx_apps');

		$payment = new \stdClass();

		$payment->config = array(
			'app_id' => $app_id, //绑定支付的APPID（必须配置，开户邮件中可查看）
			'mch_id' => $wx_apps[$app_id]['mch_id'], //商户号（必须配置，开户邮件中可查看）
			'app_key' => $wx_apps[$app_id]['app_key'], //商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）, 请妥善保管， 避免密钥泄露
			'sign_type' => 'MD5',
			'trade_type' => 'JSAPI',
			'public_pem' => $wx_apps[$app_id]['public_pem'],
			'private_pem' => $wx_apps[$app_id]['private_pem'],
		);
		$payment->config = json_encode($payment->config);
		$payment->public_pem = '';
		$payment->private_pem = '';


		$wx_pay = new WxPay($payment);
		$result = $wx_pay->prePay($pay_log, $return_url, $notify_url);

		// die (json_encode($result));
		// data: "{"appId":"wx1b566be082e9f0d9","timeStamp":"1640751050","nonceStr":"fbho8vx60zzbx52fu4dgyq5y9dwu3jzk","package":"prepay_id=wx29121050125045291180c96ee3161e0000","signType":"MD5","paySign":"8303639CCD63EADD0C292CD2ACD835B9"}"
		// result_code: "SUCCESS"
		// return_code: "SUCCESS"

		if ($result['return_code'] == 'SUCCESS') {
			$result = json_decode($result['data'], true);

			$return_data = array(
				'code' => '0',
				'msg' => '请支付',
				'result' => $result,
				'return' => json_encode($res),
			);

			die(json_encode($return_data));
		}
		else {
			$return_data = array(
				'code' => '-1',
				'msg' => $result['return_msg'],
			);

			die(json_encode($return_data));
		}
	}


	public function prepay()
	{
		$sid = $this->input->get_post('sid');
		$member_id = $this->input->get_post('member_id');
		$order_number = $this->input->get_post('order_number');
		$total_amount = $this->input->get_post('total_amount');

		// $app_id = 'wx1b566be082e9f0d9';
		$app_id = 0;

		$pay_no = Util_helper::getSerialNo();

		$pay_log = array(
			'member_id' => $member_id,
			'order_type' => 'CARD',
			'order_sn' => $pay_no,
			'trade_no' => $pay_no,
			'times' => 1,
			'amount' => $total_amount,
			'payment_type' => 'WXPAY',
			'status' => 1,
			'app_id' => $app_id,
			'out_trade_no' => '',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		);
		$this->load->model('Paylog_model', 'pay_log');
		$res = $this->pay_log->create($pay_log);

		$data['sid'] = $sid;
		$data['amount'] = $total_amount;
		$data['order_number'] = $pay_no;
		$data['notify_url'] = 'https://linhai.666os.com/v2/index.php/iroom/recharge/pay_notify/' . $pay_no;
		$data['method'] = 'weixin.weixin.prepay';


		log_message('DEBUG', 'pay_prepay:' . $data['notify_url']);

		$result = Client_helper::load($data);
		$result = json_decode($result, true);

		if (isset($result['result'])) {
			$return_data = array(
				'code' => '0',
				'msg' => '请支付',
				'result' => $result['result'],
			);
		}
		else {
			$return_data = array(
				'code' => '1',
				'msg' => '系统错误',
				'result' => $result,
			);
		}

		die(json_encode($return_data));
	}

	public function pay_notify()
	{
		$pay_no = $this->uri->segment(4);

		$params = $this->input->get();
		$params += $this->input->post();

		$xml = file_get_contents('php://input');//监听是否有数据传入

//		log_message('DEBUG', $xml);

		$this->load->model('Paylog_model', 'pay_log');
		$pay_log = $this->pay_log->get($pay_no);

		if (empty($pay_log)) {
			log_message('DEBUG', 'pay_notify:' . $pay_no . '#fail-1');
			die('fail-1');
		}

		if ($pay_log->status != 1) {
			log_message('DEBUG', 'pay_notify:' . $pay_no . '#fail-2');
			die('fail-2');
		}

		$app_id = $pay_log->app_id;

		$app_id = 'wx1b566be082e9f0d9';
		$wx_apps = $this->config->item('wx_apps');

		$payment = new \stdClass();

		$payment->config = array(
			'app_id' => $app_id, //绑定支付的APPID（必须配置，开户邮件中可查看）
			'mch_id' => $wx_apps[$app_id]['mch_id'], //商户号（必须配置，开户邮件中可查看）
			'app_key' => $wx_apps[$app_id]['app_key'], //商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）, 请妥善保管， 避免密钥泄露
			'sign_type' => 'MD5',
			'trade_type' => 'JSAPI',
			'public_pem' => $wx_apps[$app_id]['public_pem'],
			'private_pem' => $wx_apps[$app_id]['private_pem'],
		);
		$payment->config = json_encode($payment->config);
		$payment->public_pem = '';
		$payment->private_pem = '';

		$wx_pay = new WxPay($payment);

		$result = $wx_pay->payNotify($xml, $xml);

		if (!$result) {
			log_message('DEBUG', 'pay_notify:' . $pay_no . '#fail-3');
			die('fail-3');
		}

		$this->pay_log->update_status($pay_no, $result['trade_no']);

		$data['merchant_id'] = $this->config->item('merchant_id');
		$data['member_id'] = $pay_log->member_id;
		$data['order_number'] = $pay_log->order_sn;
		$data['pay_amount'] = $pay_log->amount * 100;

		$data['method'] = 'marketing.recharge.recharge';

		$result = Client_helper::load($data);
		log_message('DEBUG', json_encode($result, JSON_UNESCAPED_UNICODE));
		log_message('DEBUG', 'pay_notify:' . $pay_no . '# 充值成功 success');

		die('');

//		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : 0;
//		$amount = isset($_POST['amount']) ? $_POST['amount'] : 0;
//		$type = isset($_POST['type']) ? $_POST['type'] : 0;
//		$memo = isset($_POST['memo']) ? $_POST['memo'] : 0;
//
//		$data['member_id'] = $member_id;
//		$data['amount'] = $amount;
//		$data['type'] = $type;
//		$data['memo'] = $memo;
//		$data['method'] = 'trades.trade.insert';
//
//		$result = Client_helper::load($data);
//
//		die(json_encode($result));
	}
}
