<?php

use App\Utils\Utils;

defined('BASEPATH') OR exit('No direct script access allowed');


class Order extends CI_Controller
{
	//商户号: 0005870F5557613
	//商户名: 潮州市白马荟文化传播有限公司
	//商户前置系统生产交易秘钥: 320090108ccf11ed86aa7ea8194a1428
	//订单前缀: 11071（生产订单号mchnt_order_no字段前面需加上富友分配的5位订单前缀）

	public $pay_config = array(
		'mchnt_cd' => '0005870F5557613',
		'mchnt_code' => '11071',
		'mchnt_key' => '320090108ccf11ed86aa7ea8194a1428',
		'wx_app_id' => 'wxa9dd96c791e01f15',
	);

	public $pay_config2 = array(
		'mchnt_cd' => '0002900F1503036',
		'mchnt_code' => '1066',
		'mchnt_key' => 'f00dac5077ea11e754e14c9541bc0170',
		'wx_app_id' => 'wxa9dd96c791e01f15',
	);

	// public $pay_url = 'https://aipay-fzg.fuioupay.com/aggregatePay/wxPreCreate';
	// public $pay_url = 'https://aipaytest.fuioupay.com/aggregatePay/wxPreCreate';
	public $pay_url = 'https://aipay.fuioupay.com/aggregatePay/wxPreCreate';

	public function prepay()
	{
		$order_amount = $no = $this->input->get_post('amount');
		$order_number = $no = $this->input->get_post('order_number');
		$member_id = $no = $this->input->get_post('member_id');
		$wx_open_id = $no = $this->input->get_post('openId');
		$sid = $no = $this->input->get_post('sid');

		if (empty($order_amount) || empty($order_number) || empty($member_id) || empty($wx_open_id) || empty($sid)) {
			die(json_encode(array(
				'return_code' => 100003,
				'return_msg' => '参数不能为空'
			)));
		}

		$notify_url = 'https://linhai.666os.com/v2/index.php/eticket/order/pay_result/' . $order_number;

		$params = array(
			'version' => '1.0', // 版本号,必填
			'mchnt_cd' => $this->pay_config['mchnt_cd'], // 富友分配的商户号 例：0002900F0313432
			'random_str' => md5(date('Y-m-d H:i:s') . rand(1000, 9999)), // 随机字符串
			'order_amt' => $order_amount, // 订单总金额,以分为单位
			'mchnt_order_no' => $this->pay_config['mchnt_code'] . date('Ymd') . $order_number, // 商户订单号
			'txn_begin_ts' => date('YmdHis', time()), // 交易起始时间
			'goods_des' => '白马荟订单', // 商品描述
			'term_id' => '88888888', // 终端号,随机 8 字节数字字母组合
			'term_ip' => $this->getRealIp(), //
			'notify_url' => $notify_url, // 接收富友异步通知回调地址，通知url必须为直接可访问的url，不能携带参数主扫时必填
			'trade_type' => 'JSAPI', // 交易类型 JSAPI--公众号线下支付,LETPAY-小程序,LPXS--小程序线上
			'sub_appid' => $this->pay_config['wx_app_id'], // 子商户公众号id sub_appid 填商户或者是服务商的 appid,微信交易为商户的appid（小程序，公众号必填）
			'sub_openid' => $wx_open_id, // 子商户用户标识,微信公众号为用户的openid（小程序，公众号，服务窗必填）
			// 'sign' => '', // 签名md5(mchnt_cd+"|"+ trade_type +"|"+ order_amt +"|"+ mchnt_order_no+"|"+ txn_begin_ts+"|"+ goods_des +"|"+ term_id +"|"+ term_ip +"|"+ notify_url +"|"+ random_str +"|"+ version + "|"+ mchnt_key)
		);

		$sign = md5($params['mchnt_cd'] . '|' . $params['trade_type'] . '|' . $params['order_amt'] . '|' . $params['mchnt_order_no'] . '|' . $params['txn_begin_ts'] . '|' . $params['goods_des'] . '|' . $params['term_id'] . '|' . $params['term_ip'] . '|' . $params['notify_url'] . '|' . $params['random_str'] . '|' . $params['version'] . '|' . $this->pay_config['mchnt_key']);

		$params['sign'] = $sign;
		$result = Request_helper::api_request($this->pay_url, json_encode($params), 'POST', false);

		$result = json_decode($result, true);

		$data = array(
			'result' => json_decode($result['reserved_pay_info'], true),
			'return' => '[]',
			'code' => 0,
			'msg' => 'success',
		);

		die(json_encode($data, true));
	}

	public function pay_result()
	{

	}

	function getRealIp()
	{
		$ip = false;
		if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
			if ($ip) {
				array_unshift($ips, $ip);
				$ip = FALSE;
			}
			for ($i = 0; $i < count($ips); $i++) {
				if (!eregi("^(10│172.16│192.168).", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}

	//通过curl模拟post的请求；
	public function SendDataByCurl($url, $data, $header)
	{
		// $header = array(
		//	"Content-Type: application/json",
		// );

		// $result = $this->SendDataByCurl($this->pay_url, json_encode($params), $header);

		//对空格进行转义
		$url = str_replace(' ', '+', $url);
		$ch = curl_init();
		//设置选项，包括URL
		curl_setopt($ch, CURLOPT_URL, "$url");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3); //定义超时3秒钟
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);  //设置头信息的地方
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// POST数据
		curl_setopt($ch, CURLOPT_POST, 1);
		// 把post的变量加上
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //所需传的数组用http_bulid_query()函数处理一下，就ok了
		//执行并获取url地址的内容
		$output = curl_exec($ch);
		$errorCode = curl_errno($ch);
		//释放curl句柄
		curl_close($ch);
		if (0 !== $errorCode) {
			return $errorCode;
		}
		return $output;
	}

	/**
	 * 生成随机数
	 * @param int $length
	 * @return string
	 */
	public function random_keys($length)
	{
		$pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		$key = '';
		for ($i = 0; $i < $length; $i++) {
			$key .= $pattern[mt_rand(0, 35)];    //生成php随机数
		}
		return $key;
	}
}
