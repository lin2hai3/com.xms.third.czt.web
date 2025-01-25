<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Notify extends CI_Controller
{

	private $key = 'your_mch_api_key';  // 商户API密钥
	private $appid = 'your_appid';      // 微信支付的APPID
	private $mch_id = 'your_mch_id';    // 商户号

	public $pay_config = array(
		'mchnt_cd' => '0005870F5557613',
		'mchnt_code' => '11071',
		'mchnt_key' => '320090108ccf11ed86aa7ea8194a1428',
		'wx_app_id' => 'wxa9dd96c791e01f15',
	);

	public function __construct()
	{
		parent::__construct();
	}

	public function fuiou()
	{
		//{"curr_type":"CNY","full_sign":"57d758529406b3102128337b7b0cb18f","mchnt_cd":"0005870F5557613",
		//"mchnt_order_no":"110712025012453395","order_amt":"1","order_type":"JSAPI",
		//"random_str":"5F42EYJ8EY6REQUYWQZ31I467XD44SX9","reserved_addn_inf":"",
		//"reserved_bank_type":"OTHERS","reserved_buyer_logon_id":"",
		//"reserved_channel_order_id":"110712025012453395","reserved_coupon_fee":"0","reserved_fund_bill_list":"",
		//"reserved_fy_settle_dt":"20250124","reserved_fy_trace_no":"131191998263","reserved_is_credit":"0",
		//"reserved_promotion_detail":"","reserved_settlement_amt":"1",
		//"result_code":"000000","result_msg":"SUCCESS",
		//"settle_order_amt":"1","sign":"cb398eebe524c7e5818de77d2c7e30a1",
		//"term_id":"","transaction_id":"4200002592202501243152922845",
		//"txn_fin_ts":"20250124104305","user_id":"oV_0Q5Tf95STu79XnO673bQ3c9IA"}

		// 获取微信支付回调的POST数据
		$result = file_get_contents('php://input');
		$order_number = $this->uri->segment(4);
		log_message('DEBUG', '富友支付回调');
		log_message('DEBUG', 'ORDER_NUMBER=' . $order_number);
		log_message('DEBUG', $result);

		$result = json_decode($result, true);
		$sign_str = $result['mchnt_cd'] . '|' . $result['mchnt_order_no'] . '|' . $result['settle_order_amt'] . '|' . $result['order_amt']
			. '|' . $result['txn_fin_ts'] . '|' . $result['reserved_fy_settle_dt'] . '|' . $result['random_str'] . '|' . $this->pay_config['mchnt_key'];

		$sign = md5($sign_str);

		if ($sign == $result['sign']) {
			log_message('DEBUG', '验签成功');

			if ($result['result_code'] == '000000' && $result['result_msg'] == 'SUCCESS') {
				$this->pay_receipt($order_number, $result['order_amt']);
				echo 'SUCCESS';
			}
		} else {
			log_message('DEBUG', '验签失败');

			echo 'FAIL';
		}
	}

	public function yipiao()
	{
		// 获取微信支付回调的POST数据
		$result = file_get_contents('php://input');
		$order_number = $this->uri->segment(4);
		log_message('DEBUG', '一票潮州支付回调');
		log_message('DEBUG', 'ORDER_NUMBER=' . $order_number);
		log_message('DEBUG', $result);

		$data = $this->xmlToArray($result);

		log_message('DEBUG', $data);

		// 校验签名
		if ($this->checkSignature($data)) {
			// 签名验证通过，处理支付回调
			if ($data['result_code'] == 'SUCCESS' && $data['return_code'] == 'SUCCESS') {
				// 支付成功
				$order_id = $data['out_trade_no'];
				$this->pay_receipt($order_number, $data['total_fee']);
				echo 'SUCCESS';
			}
		}
		else {
			log_message('DEBUG', '验签失败');

			echo 'FAIL';
		}
	}

	public function pay_receipt($receipt_id, $order_amount)
	{
		$url = 'https://eta.666os.com?act=Wxapp.rec_paid';

		$params = array(
			'id' => $receipt_id,
			'amount' => $order_amount,
		);

		$result = Request_helper::request($url, $params, 'POST', false);

		log_message('DEBUG', '状态修改成功id=' . $receipt_id . '#' . $result);
	}

	public function test()
	{
		$url = 'https://eta.666os.com?act=Wxapp.rec_paid';

		$params = array(
			'id' => '53415',
			'amount' => 1,
		);

		$result = Request_helper::request($url, $params, 'POST', false);

		die($result);
	}

	// 将XML转化为数组
	private function xmlToArray($xml)
	{
		libxml_disable_entity_loader(true);
		$array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $array;
	}

	// 将数组转化为XML
	private function arrayToXml($array)
	{
		$xml = "<xml>";
		foreach ($array as $key => $value) {
			$xml .= "<$key>$value</$key>";
		}
		$xml .= "</xml>";
		return $xml;
	}

	// 校验签名
	private function checkSignature($data)
	{
		// 去除数组中的空值和签名字段
		unset($data['sign']);

		// 按字典顺序排序
		ksort($data);

		// 拼接字符串
		$string = '';
		foreach ($data as $key => $value) {
			$string .= "$key=$value&";
		}
		$string .= "key=" . $this->key;  // 加上商户API密钥

		// 计算MD5签名
		$sign = strtoupper(md5($string));

		// 对比计算的签名和回调数据中的签名
		return $sign == $data['sign'];
	}
}
