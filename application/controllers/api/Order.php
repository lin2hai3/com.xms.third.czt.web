<?php

use App\Utils\Utils;

defined('BASEPATH') or exit('No direct script access allowed');


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

//	public $pay_url = 'https://aipay.fuioupay.com/aggregatePay/wxPreCreate';
	public $pay_url = 'https://aipay-cloud.fuioupay.com/aggregatePay/wxPreCreate';

//	public $refund_url = 'https://aipay.fuioupay.com/aggregatePay/commonRefund';
	public $refund_url = 'https://aipay-cloud.fuioupay.com/aggregatePay/commonRefund';

	protected $default_inventory = 80;

	private function getTicketRule($id)
	{
		$data = array();
		$data['method'] = 'tickets.ticket.get';
		$data['fields'] = '*';
		$data['id'] = $id;
		$data['extend'] = 1;

		$result = EtaApp_helper::load($data);
		$result = json_decode($result, true);

		$extend = '';
		if (isset($result['result']['extend'])) {
			$extend = $result['result']['extend'];
		}

		// $items = explode('\r\n', $extend);
		$rows = explode("\n", $extend);

		$default_rule = array();
		$week_rules = array();
		$date_rules = array();
		$rules = array();

		foreach ($rows as $row) {
			if ($this->start_with($row, 'default:')) {
				$type = 1;
				$time_span = str_replace('default:', '', $row);
				$date = 'default';
			} elseif ($this->start_with_week($row)) {
				$type = 2;
				$time_span = substr($row, 2);
				$date = substr($row, 0, 1);
			} else {
				$type = 3;
				$time_span = substr($row, 9);
				$date = substr($row, 0, 8);
				$date = date('Y-m-d', strtotime($date));
			}

			$time_span = trim($time_span);
			$time_array = explode(",", $time_span);

			$_time_array = array();
			foreach ($time_array as $time_item) {
				if (strpos($time_item, "|") > -1) {
					$time_items = explode("|", $time_item);
					$_time_item = $time_items[0];
					$inventory = $time_items[1];
				}
				else {
					$_time_item = $time_item;
					$inventory = $this->default_inventory;
				}

				$_time_array[] = array(
					'time_span' => $_time_item,
					'total_inventory' => $inventory,
					'sale_count' => 0,
					'inventory' => $inventory,
					'show_inventory' => true,
					'status' => 1,
				);
			}

			if ($type == 1) {
				$default_rule = $_time_array;
			}

			if ($type == 2) {
				$week_rules[$date] = $_time_array;
			}

			if ($type == 3) {
				$date_rules[$date] = $_time_array;
			}
		}

		for ($idx = 0; $idx < 7; $idx++) {
			$date = date('Y-m-d', strtotime('+ ' . $idx . ' days'));
			$week = date('w', strtotime($date));

			// $time_spans = $default_rule;
			$time_spans = '';

			if (isset($week_rules[$week])) {
				$time_spans = $week_rules[$week];
			}

			if (isset($date_rules[$date])) {
				$time_spans = $date_rules[$date];
			}

			if (!empty($time_spans)) {
				$rules[$date] = $time_spans;
			}
		}

		$_rules = array();
		foreach ($rules as $date => $rule) {


			$_rule['date'] = $date;
			$_rule['weekdate'] = $this->getWeekdate($date);

			$_items = array();

			foreach ($rule as &$item) {

				$time_items = explode('-', $item['time_span']);
				$start_time = $date . ' ' . $time_items[0];
				$end_time = $date . ' ' . $time_items[1];

				$data = array();
				$data['method'] = 'tickets.receipts.count.get';
				$data['fields'] = '*';
				$data['ticket_id'] = $id;
				$data['stime'] = $start_time;
				$data['etime'] = $end_time;
				$data['page_size'] = '20';

				$count_result = EtaApp_helper::load($data);

				$count_result = json_decode($count_result, true);

				$inventory = $item['inventory'] - $count_result['result']['count'];

				$item['inventory'] = $inventory;
				$item['sale_count'] = $count_result['result']['count'];

				$_items[$item['time_span']] = $item;
			}

			unset($item);

			$_rule['items'] = $_items;
			$_rules[$date] = $_rule;
		}

		return $_rules;
	}


	function start_with_week($string)
	{
		return $this->start_with($string, '0:')
			|| $this->start_with($string, '1:')
			|| $this->start_with($string, '2:')
			|| $this->start_with($string, '3:')
			|| $this->start_with($string, '4:')
			|| $this->start_with($string, '5:')
			|| $this->start_with($string, '6:');
	}

	function getWeekdate($date)
	{
		$w = date('w', strtotime($date));

		$data = array(
			0 => '周日',
			1 => '周一',
			2 => '周二',
			3 => '周三',
			4 => '周四',
			5 => '周五',
			6 => '周六',
		);

		return $data[$w];
	}

	function start_with($string, $startString)
	{
		return strncmp($string, $startString, strlen($startString)) === 0;
	}

	public function checkout()
	{
		$config = array(
			'api_host' => 'http://etr.666os.com/',
			'api_app_id' => 'cticket',
			'api_app_key' => 'qZcKiQmN',
		);


		$date = $this->input->get_post('date');
		$ticket_id = $this->input->get_post('ticket_id');
		$timerange = $this->input->get_post('timerange');
		$qty = $this->input->get_post('qty');



		if (empty($qty)) {
			$qty = 1;
		}

		$time_ranges = explode('-', $timerange);

		if (empty($date)) {
			$start_time = date('Y-m-d H:i:s', strtotime($time_ranges[0]));
			$end_time = date('Y-m-d H:i:s', strtotime($time_ranges[1]));
		}
		else {
			$start_time =  date('Y-m-d H:i:s', strtotime($date . ' ' . date('H:i:s', strtotime($time_ranges[0]))));
			$end_time = date('Y-m-d H:i:s', strtotime($date . ' ' . date('H:i:s', strtotime($time_ranges[1]))));
		}


		$data = array();
		$data['method'] = 'tickets.receipts.count.get';
		$data['fields'] = '*';
		$data['ticket_id'] = $ticket_id;
		$data['stime'] = $start_time;
		$data['etime'] = $end_time;
		$data['page_size'] = '20';

		$result = Client_helper::loadWithConfig($config, $data);

		$result = json_decode($result, true);

		$result['stime'] = $start_time;
		$result['etime'] = $end_time;

		$rules = $this->getTicketRule($ticket_id);

		$can_add_receipt = false;
		if (isset($rules[$date])) {
			if (isset($rules[$date]['items'][$timerange])) {
				if ($rules[$date]['items'][$timerange]['inventory'] > $qty) {
					$can_add_receipt = true;
				}
			}
		}

		$result['can_add_receipt'] = $can_add_receipt ? 1 : 0;

		// $result['can_add_receipt'] = ($result['result']['count'] > $max_count ? 0 : 1);

		if ($result['can_add_receipt'] == 0) {
			$result['msg'] = '该场次库存不足';
		}

		$result['other'] = $rules;

		die(json_encode($result, JSON_UNESCAPED_UNICODE));
	}

	public function prepay()
	{
		$order_amount = $this->input->get_post('amount');
		$order_number = $this->input->get_post('order_number');
		$member_id = $this->input->get_post('member_id');
		$wx_open_id = $this->input->get_post('openId');
		$sid = $this->input->get_post('sid');

		if (empty($order_amount) || empty($order_number) || empty($member_id) || empty($wx_open_id) || empty($sid)) {
			die(json_encode(array(
				'return_code' => 100003,
				'return_msg' => '参数不能为空'
			)));
		}

		$notify_url = 'https://linhai.666os.com/v2/index.php/eticket/order/pay_result/' . $order_number;

		$trade_no = $this->pay_config['mchnt_code'] . date('Ymd') . $order_number; // 商户订单号

		$this->load->model('EtaPayLog_model', 'pay_log');

		$pay_log = array(
			'member_id' => $member_id,
			'order_type' => 'RECEIPT',
			'order_sn' => $order_number,
			'trade_no' => $trade_no,
			'times' => 0,
			'amount' => $order_amount / 100,
			'payment_type' => 'FUIOUPAY',
			'out_trade_no' => '',
			'refund_no' => '',
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		);

		$res = $this->pay_log->create($pay_log);

		$params = array(
			'version' => '1.0', // 版本号,必填
			'mchnt_cd' => $this->pay_config['mchnt_cd'], // 富友分配的商户号 例：0002900F0313432
			'random_str' => md5(date('Y-m-d H:i:s') . rand(1000, 9999)), // 随机字符串
			'order_amt' => $order_amount, // 订单总金额,以分为单位
			'mchnt_order_no' => $trade_no,
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

		log_message('DEBUG', $this->pay_url);
		log_message('DEBUG', json_encode($params, JSON_UNESCAPED_UNICODE));
		$result = Request_helper::api_request($this->pay_url, json_encode($params), 'POST', false);
		log_message('DEBUG', 'prepay request result');
		log_message('DEBUG', $result);

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


	public function cancel()
	{
		$order_number = $this->input->get_post('order_number');


		$this->load->model('EtaPayLog_model', 'pay_log');
		$last = $this->pay_log->get_last($order_number);

		$trade_no =  $last->trade_no;
		$refund_no =  $last->trade_no . 'R';
		$order_amount = $last->amount * 100;
		$refund_amount = $last->amount * 100;

		$this->pay_log->update_refund_no($trade_no, $refund_no, $refund_amount);

		$params = array(
			'version' => '1.0',
			'mchnt_cd' => $this->pay_config['mchnt_cd'], // 富友分配的商户号 例：0002900F0313432
			'term_id' => '88888888', // 终端号,随机 8 字节数字字母组合
			'random_str' => md5(date('Y-m-d H:i:s') . rand(1000, 9999)), // 随机字符串
			'mchnt_order_no' => $trade_no, // 商户订单号
			'refund_order_no' => $refund_no,
			'order_type' => 'WECHAT',
			'total_amt' => $order_amount,
			'refund_amt' => $refund_amount,
		);

		$sign = md5($params['mchnt_cd'] . '|' . $params['order_type'] . '|' . $params['mchnt_order_no'] . '|' . $params['refund_order_no'] . '|' . $params['total_amt'] . '|' . $params['refund_amt'] . '|' . $params['term_id'] . '|' . $params['random_str'] . '|' . $params['version'] . '|' . $this->pay_config['mchnt_key']);

		$params['sign'] = $sign;
		$result = Request_helper::api_request($this->refund_url, json_encode($params), 'POST', false);

		$data = array(
			'result' => json_decode($result, true),
			'return' => '[]',
			'code' => 0,
			'msg' => 'success',
		);

		die(json_encode($data, true));
	}
}
