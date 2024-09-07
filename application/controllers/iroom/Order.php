<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/11/14
 * Time: 23:43
 */

require_once 'application/libraries/RequestUtil.php';
require_once 'application/libraries/BankPay.php';
require_once 'application/libraries/WxPay.php';


class Order extends CI_Controller
{
	public $status_list = array(
		'CREATED' => '待支付',
		'PAID' => '已付款',
		'SHIPPED' => '已入住',
		'RECEIVED' => '已退房',
		'SUCCESS' => '交易成功',
		'CLOSED' => '订单关闭',
		'EXCEPTIONAL' => '订单异常'
	);

	public function index()
	{
		$status = $this->input->get_post('status');
		$member_id = $this->input->get_post('member_id');
		$page = $this->input->get_post('page');
		$page = intval($page);
		$page = empty($page) ? 1 : $page;

		if (!empty($status)) {
			$data['status'] = $status;
		}

		if (!empty($member_id)) {
			$data['member_id'] = $member_id;
		}

		$data['is_strict'] = 1;
		$data['order_type'] = 'ROOM';
		$data['method'] = 'orders.orders.get';
		$data['fields'] = '*';
		$data['orderby'] = 'id desc';
		$data['page'] = $page;

		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);

		$result = $result['result'];

		$_shops = array();
		$_rooms = array();

		if (isset($result['rows'])) {
			foreach ($result['rows'] as &$row) {
				$row['order_type'] = 18;

				$order_data = array(
					'method' => 'orders.order.get',
					'fields' => 'id,order_number,order_type,dateline,member_id,total_qty,total_amount,freight,fee,off,discount,final_amount,coupon_id,cuser,ctime,mtime,status,event_status,comment,removed',
					'id' => $row['id']
				);

				$order_result = IRoomApp_helper::load($order_data);
				$order_result = json_decode($order_result, true);
				$row['status_label'] = $this->status_list[$row['status']];

				if (isset($order_result['result']['booking'])) {
					$bookings = $order_result['result']['booking'];

					foreach ($bookings as $bid => $booking) {
						$booking['end'] = date('H:i', strtotime($booking['end']));

						if (!isset($_shops[$booking['shop_id']])) {

							$shop_data = array(
								'method' => 'shops.shop.get',
								'fields' => 'id,merchant_id,fullname,name,keywords,hours,address,location,phone,image,description,linkman,mid,tid,opening_hours,interval_time,cleaning,booking_days,booking_min_times,booking_max_times,checkin_offset,checkout_offset,cuser,ctime,mtime,enabled,removed,published',
								'id' => $booking['shop_id'],
							);

							$shop_result = IRoomApp_helper::load($shop_data);
							$shop_result = json_decode($shop_result, true);
							$_shops[$booking['shop_id']] = $shop_result['result'];
						}
						$booking['shop'] = $_shops[$booking['shop_id']];

						if (!isset($_rooms[$booking['room_id']])) {

							$room_data = array(
								'method' => 'rooms.room.get',
								//'fields' => 'id,merchant_id,shop_id,name,description,area,seats,base_rates,increase_rates,has_wifi,has_automat,has_charger,enabled',
								'fields' => '*',
								'id' => $booking['room_id'],
							);

							$room_result = IRoomApp_helper::load($room_data);
							$room_result = json_decode($room_result, true);
							$_rooms[$booking['room_id']] = $room_result['result'];
						}
						$booking['room'] = $_rooms[$booking['room_id']];

						$row['booking'][$bid] = $booking;
					}

					$start_date = date('Y-m-d', strtotime($row['booking_start']));
					$next_date = date('Y-m-d', strtotime($start_date) + 24 * 60 * 60);
					$end_date = date('Y-m-d', strtotime($row['booking_end']));
					$row['booking_end'] = date('H:i', strtotime($row['booking_end']));

					if ($next_date == $end_date) {
						$row['booking_end'] = '次日' . $row['booking_end'];
					}
				} else {

					$row['booking'] = array();
					if (isset($row['shop_id'])) {

						$shop_data = array(
							'method' => 'shops.shop.get',
							'fields' => 'id,merchant_id,fullname,name,keywords,hours,address,location,phone,image,description,linkman,mid,tid,opening_hours,interval_time,cleaning,booking_days,booking_min_times,booking_max_times,checkin_offset,checkout_offset,cuser,ctime,mtime,enabled,removed,published',
							'id' => $row['shop_id'],
						);

						$shop_result = IRoomApp_helper::load($shop_data);
						$shop_result = json_decode($shop_result, true);
						$_shops[$row['shop_id']] = $shop_result['result'];

						$row['booking'][0]['shop'] = $_shops[$row['shop_id']];
					}

					if (isset($row['room_id'])) {

						$room_data = array(
							'method' => 'rooms.room.get',
							//'fields' => 'id,merchant_id,shop_id,name,description,area,seats,base_rates,increase_rates,has_wifi,has_automat,has_charger,enabled',
							'fields' => '*',
							'id' => $row['room_id'],
						);

						$room_result = IRoomApp_helper::load($room_data);
						$room_result = json_decode($room_result, true);
						$_rooms[$row['room_id']] = $room_result['result'];

						$row['booking'][0]['room'] = $_rooms[$row['room_id']];
					}

					$start_date = date('Y-m-d', strtotime($row['booking_start']));
					$next_date = date('Y-m-d', strtotime($start_date) + 24 * 60 * 60);
					$end_date = date('Y-m-d', strtotime($row['booking_end']));
					$row['booking_end'] = date('H:i', strtotime($row['booking_end']));

					if ($next_date == $end_date) {
						$row['booking_end'] = '次日' . $row['booking_end'];
					}
				}
			}
		}

		return Util_helper::result($result);
	}

	public function index2()
	{
		$member_id = $this->input->get_post('member_id');

		$data['member_id'] = $member_id;
		$data['order_type'] = 'ROOM';
		$data['method'] = 'orders.orders.get';
		$data['fields'] = 'id,order_number,order_type,dateline,member_id,total_qty,total_amount,freight,fee,off,discount,final_amount,coupon_id,cuser,ctime,mtime,status,event_status,comment,removed';
		$data['orderby'] = 'id desc';

		$order_result = IRoomApp_helper::load($data);

		$order_result = json_decode($order_result, true);

		$orders = array();
		$has_order = 0;
		if ($order_result['result']['total_results'] > 0) {
			foreach ($order_result['result']['rows'] as $row) {
				if ($row['status'] == 'PAID' || $row['status'] == 'SHIPPED') {
					if (isset($row['booking'][0]['end']) && time() < strtotime($row['booking'][0]['end'])) {
						$orders[] = $row;
						$has_order++;
					}
				}

				// $current = $row; $has_current = 1;break;
			}
		}

		if ($has_order) {

			foreach ($orders as &$order) {
				$room_data = array(
					'method' => 'rooms.room.get',
					//'fields' => 'id,merchant_id,shop_id,name,description,area,seats,base_rates,increase_rates,has_wifi,has_automat,has_charger,enabled',
					'fields' => '*',
					'id' => $order['booking'][0]['room_id'],
				);

				$room_result = IRoomApp_helper::load($room_data);
				$room_result = json_decode($room_result, true);
				$order['booking'][0]['room'] = $room_result['result'];

				$start_date = date('Y-m-d', strtotime($order['booking'][0]['start']));
				$start_time = date('H:i', strtotime($order['booking'][0]['start']));

				$end_date = date('Y-m-d', strtotime($order['booking'][0]['end']));
				$end_time = date('H:i', strtotime($order['booking'][0]['end']));

				$end_date = ($start_date == $end_date ? ' ' : '次日 ');

				$order['booking'][0]['time_span'] = $start_date . ' ' . $start_time . '-' . $end_date . $end_time;


				$shop_data = array(
					'method' => 'shops.shop.get',
					'fields' => '*',
					'id' => $order['booking'][0]['shop_id'],
				);

				$shop_result = IRoomApp_helper::load($shop_data);
				$shop_result = json_decode($shop_result, true);
				$order['booking'][0]['shop'] = $shop_result['result'];


			}
		}

		return Util_helper::result(array('has_order' => $has_order, 'orders' => $orders));
	}

	public function mark()
	{
		// $a = 32.8;

		// error
		// echo $a *100;
		// echo (int)($a * 100);
		// The result is 3279.

		//
		// $a = sprintf('%0.2f', $a * 100);
		// The result is 3280.

		// $a = bcmul($a, 100, 1);
		// The result is 3280.

	}

	public function create()
	{
		$with_clean = $this->input->get_post('with_clean');
		$data = $this->input->get_post('data');
		$data = json_decode($data, true);

		if (empty($with_clean)) {
			$with_clean = 2;
		}

		// 2022年2月28日22:17:38
		if ($with_clean == 1) {
			$data['need_clean'] = 1;
		}

		log_message('DEBUG', 'order.create#' . json_encode($data));

		$data['method'] = 'rooms.schedule.get';
		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);

		if (isset($result['result'])) {
			$return_data = $result['result'];
			$return_data['range'] = isset($return_data['range']) ? $return_data['range'] : array();
		} else {
			$return_data = array();
			$return_data['range'] = array();
		}

		$now = time();
		if (count($return_data['range']) > 0) {
			$first_item = $return_data['range'][0];
			if ($now > strtotime($first_item['dateline'] . ' ' . $first_item['start'] . ':00')) {
				unset($return_data['range'][0]);
			}
			$return_data['range'] = array_values($return_data['range']);
		}

		// 是否自动补上清洁时间段 1-是 2-否
		// 2022-02-14 之前只预留了订单后半小时，订单前半小时也要空出来
		// 2022-02-28 修改为need_clean=1
		if ($with_clean == 1) {
			$available = 1;
			for ($idx = 0; $idx < count($return_data['range']); $idx++) {
				if ($return_data['range'][$idx]['available'] == 0) {
					// 第一个不能预约时间段
					$available = 0;

					// 已经预约时间段前一个预约时间段，设置为不能预约
					$pur_idx = $idx - 1;
					if ($pur_idx < 0) {
						$pur_idx = 0;
					}
					$return_data['range'][$pur_idx]['available'] = 0;
				} else if ($return_data['range'][$idx]['available'] == 1 && $available == 0) {
					// 已经预约时间段结束后第一个可以预约时间段，设置为不能预约
					// $return_data['range'][$idx]['available'] = 0;
					// $available = 1;
				}
			}
		}

		/**
		 * $next_date = date('Y-m-d', strtotime($data['dateline']) + 24 * 3600);
		 * $data['dateline'] = $next_date;
		 * $next_result = IRoomApp_helper::load($data);
		 * $next_result = json_decode($next_result, true);
		 * $return_data['next_range'] = $next_result['result']['range'];
		 *
		 * // $return_data['range'] = array_merge($return_data['range'], $return_data['next_range']);
		 * $return_data['next_date'] = $next_date;
		 **/
		$return_data['next_date'] = array();


		$room_data['id'] = $data['room_id'];
		$room_data['method'] = 'rooms.room.get';
		$room_data['fields'] = '*';
		//$room_data['fields'] = 'id,merchant_id,shop_id,name,description,area,seats,base_rates,increase_rates,has_wifi,has_automat,has_charger,enabled';

		$room_result = IRoomApp_helper::load($room_data);
		$room_result = json_decode($room_result, true);
		$return_data['room'] = $room_result['result'];
		$return_data['contact'] = '';
		$return_data['mobile'] = '';


//		$data['order_type'] = 'ROOM';
//		$data['method'] = 'orders.orders.get';
//		$data['fields'] = 'id,order_number,order_type,dateline,member_id,total_qty,total_amount,freight,fee,off,discount,final_amount,coupon_id,cuser,ctime,mtime,status,event_status,comment,removed';
//		$data['orderby'] = 'id desc';
//
//		$result = IRoomApp_helper::load($data);
//		$result = json_decode($result, true);
//		$result = $result['result'];
//
//		if ($result['total_results'] > 0) {
//			$order = $this->getRow($result['rows'][0]['id']);
//			$order = $order['data'];
//
//			$return_data['contact'] = $order['receiver']['contact'];
//			$return_data['mobile'] = $order['receiver']['umobile'];
//		}
//		else {
//			$return_data['contact'] = '';
//			$return_data['mobile'] = '';
//		}


		return Util_helper::result($return_data);
	}

	public function renew()
	{
		$data = $this->input->get_post('data');
		$data = json_decode($data, true);

		$data['need_clean'] = 1;
		$data['method'] = 'rooms.schedule.renew.get';
		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);

		log_message('DEBUG', 'order.renew#' . json_encode($data));
		log_message('DEBUG', 'order.renew#' . json_encode($result));

		$return_data = $result['result'];
		$return_data['range'] = isset($return_data['range']) ? $return_data['range'] : array();

		//$next_date = date('Y-m-d', strtotime($data['dateline']) + 24 * 3600);
		//$data['dateline'] = $next_date;
		//$next_result = IRoomApp_helper::load($data);
		//$next_result = json_decode($next_result, true);
		//$return_data['next_range'] = $next_result['result']['range'];

		//$return_data['range'] = array_merge($return_data['range'], $return_data['next_range']);
		//$return_data['next_date'] = $next_date;


		$room_data['id'] = $data['room_id'];
		$room_data['method'] = 'rooms.room.get';
		// $room_data['fields'] = 'id,merchant_id,shop_id,name,description,area,seats,base_rates,increase_rates,has_wifi,has_automat,has_charger,enabled';
		$room_data['fields'] = '*';

		$room_result = IRoomApp_helper::load($room_data);
		$room_result = json_decode($room_result, true);
		$return_data['room'] = $room_result['result'];
		$return_data['contact'] = '';
		$return_data['mobile'] = '';

		return Util_helper::result($return_data);
	}

	public function checkout()
	{
		$data = $this->input->get_post('data');
		$data = json_decode($data, true);
		$data['method'] = 'rooms.schedule.get';

		log_message('DEBUG', 'order.checkout#' . json_encode($data));

		$check_date = $data['dateline'];
		$checkin_time = $data['checkin_time'];
		$checkout_time = $data['checkout_time'];
		$member_id = $data['member_id'];
		$remark = isset($data['remark']) ? $data['remark'] : '';
		$room_id = isset($data['room_id']) ? $data['room_id'] : 0;

		// 处理 2022-02-03 22:00 - 00:00 问题
		// 2022-02-03 22:00 ~ 2022-02-04 00:00
//		$_checkout_time = strtotime($data['checkout_time'] . ':00');
//		if (date('H:i:s', $_checkout_time) == '00:00:00') {
//			$checkout_time = date('Y-m-d H:i', $_checkout_time + 24 * 60 * 60);
//			$data['checkout_time'] = $checkout_time;
//		}
		// 已经在前端处理了

		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);


		$return_data = $result['result'];
		// $_data = array(
		// 	'data' => $data,
		// 	'result' => $result,
		// );

		$order = array(
			'method' => 'orders.order.insert',
			'merchant_id' => 1,
			'shop_id' => 1,
			'member_id' => $member_id,
			'dateline' => $check_date,
			'fee' => 0,
			'off' => 0,
			'freight' => 0,
			'discount' => 0,
			'cuser' => $member_id,
			'comment' => $remark,
			'order_type' => 'ROOM',
			'rel_order_number' => '',
			'coupon_id' => 0,
			'detail' => '',
			'receiver_id' => 0,
			'receiver' => '',
			//'express_code' => '',
			'coin' => 0,
			'pay_by_money' => 0,
			'preview' => 1,
			'room_id' => $room_id,
			'booking_start' => $checkin_time,
			'booking_end' => $checkout_time,
		);
		//die(json_encode($order));
		$order_result = IRoomApp_helper::load($order);
		$order_result = json_decode($order_result, true);

		$return_data['data'] = $data;
		$return_data['order'] = $order;
		$return_data['order_result'] = $order_result;


		$coupon_data = array(
			'method' => 'coupons.usable.get',
			'member_id' => $member_id,
			'order_type' => 'ROOM',
			'fields' => '*',
			'amount_over' => $order_result['result']['final_amount'] * 100,
			'page_size' => 100,
		);

		$coupon_result = IRoomApp_helper::load($coupon_data);
		$coupon_result = json_decode($coupon_result, true);

		if ($coupon_result['code'] == 0 && isset($coupon_result['result']['rows'])) {
			$return_data['order_result']['result']['coupons1'] = $coupon_result['result']['rows'];
		}

		if ($order_result['code'] == 0) {
			return Util_helper::result($return_data);
		} else {
			return Util_helper::result(null, $order_result['msg'], $order_result['code']);
		}
	}

	// 创建订单
	public function store()
	{
		$data = $this->input->get_post('data');
		$data = json_decode($data, true);

		$check_date = $data['dateline'];
		$checkin_time = $data['checkin_time'];
		$checkout_time = $data['checkout_time'];
		$merchant_id = isset($data['merchant_id']) ? $data['merchant_id'] : 1;
		$shop_id = isset($data['shop_id']) ? $data['shop_id'] : 1;
		$member_id = $data['member_id'];
		$remark = isset($data['remark']) ? $data['remark'] : '';
		$room_id = isset($data['room_id']) ? $data['room_id'] : 0;
		$contact = isset($data['contact']) ? $data['contact'] : '';
		$mobile = isset($data['mobile']) ? $data['mobile'] : '';
		$pay_by_money = isset($data['pay_by_money']) ? $data['pay_by_money'] : '';
		$coupon_id = isset($data['coupon_id']) ? $data['coupon_id'] : '';

		$need_contact = false;

		// return Util_helper::result($data);
		if (empty($member_id)) {
			return Util_helper::result(null, '非法请求 member_id', 1);
		}

		if (empty($checkin_time) || empty($checkout_time)) {
			return Util_helper::result(null, '请选择预约时间', 1);
		}

		if ($need_contact) {
//			if (empty($contact) || empty($mobile)) {
//				return Util_helper::result(null, '请填写联系人信息/手机', 1);
//			}

			$address_data = array(
				'method' => 'members.address.insert',
				'member_id' => $member_id,
				'province' => '广东省',
				'city' => '潮州市',
				'county' => '湘桥区',
				'address' => '无人茶舍',
				'contact' => $contact,
				'mobile' => $mobile,
			);

			$address_result = IRoomApp_helper::load($address_data);
			$address_result = json_decode($address_result, true);

			if ($address_result['code'] != 0) {
				return Util_helper::result(null, $address_result['msg'], 1);
			}
			$address_result = $address_result['result'];
			$address_id = $address_result['id'];

			$receiver_id = $address_id;
			$receiver = json_encode($address_data, JSON_UNESCAPED_UNICODE);
		} else {
			$receiver_id = 0;
			$receiver = '';
		}

		$order = array(
			'method' => 'orders.order.insert',
			'member_id' => $member_id,
			'dateline' => $check_date,
			'fee' => 0,
			'off' => 0,
			'freight' => 0,
			'discount' => 0,
			'cuser' => $member_id,
			'comment' => $remark,
			'order_type' => 'ROOM',
			'rel_order_number' => '',
			'coupon_id' => $coupon_id,
			'detail' => '',
			'receiver_id' => $receiver_id,
			'receiver' => $receiver,
			//'express_code' => '',
			'coin' => 0,
			'pay_by_money' => $pay_by_money,
			'preview' => 0,
			'merchant_id' => $merchant_id,
			'shop_id' => $shop_id,
			'room_id' => $room_id,
			'booking_start' => $checkin_time,
			'booking_end' => $checkout_time,
			'is_strict' => 1,
		);

		// log_message('debug', json_encode($order));
		$order_result = IRoomApp_helper::load($order);
		// log_message('debug', json_encode($order_result));
		$order_result = json_decode($order_result, true);

		$data = array(
			'order_result' => $order_result,
		);

		if ($order_result['code'] == 0) {
			return Util_helper::result($data, 'success');
		} else {
			return Util_helper::result($data, $order_result['msg'], $order_result['code']);
		}
	}

	public function show()
	{
		$id = $this->input->get_post('id');

		if (empty($id)) {
			return Util_helper::result(null, 'empty row', 1);
		}

		$order_result = $this->getRow($id);

		if ($order_result['code'] != 0) {
			return Util_helper::result(null, $order_result['msg'], $order_result['code']);
		}

		return Util_helper::result($order_result['data']);
	}

	public function getRow($id)
	{
		$data = array(
			'method' => 'orders.order.get',
			'id' => $id,
			'fields' => '*',
			'member' => 1,
			'weixin' => 1,
			'receiver' => 1,
			'unmask' => 1,
		);

		$data['is_strict'] = 1;
		$data['get_member'] = 1;
		$data['get_weixin'] = 1;
		$data['fetch_booking'] = 1;

		$_shops = array();
		$_rooms = array();


		$order_result = IRoomApp_helper::load($data);
		// die($order_result);
		$order_result = json_decode($order_result, true);

		$is_strict = 0;
		$shop_id = 0;
		$room_id = 0;

		$bookings = $order_result['result']['booking'];

		if (isset($order_result['result']['shop_id'])) {
			$is_strict = 1;
			$shop_id = $order_result['result']['shop_id'];
		}

		if (isset($order_result['result']['room_id'])) {
			$room_id = $order_result['result']['room_id'];
		}

		$order_result['result']['_booking'] = $bookings;

		foreach ($bookings as $bid => $booking) {

			if (!$is_strict) {
				$shop_id = $booking['shop_id'];
				$room_id = $booking['room_id'];
			}

			if (!isset($_shops[$shop_id])) {

				$shop_data = array(
					'method' => 'shops.shop.get',
					'fields' => '*',
					'id' => $shop_id,
				);

				$shop_result = IRoomApp_helper::load($shop_data);
				$shop_result = json_decode($shop_result, true);
				$_shops[$shop_id] = $shop_result['result'];
			}
			$booking['shop'] = $_shops[$shop_id];

			if (!isset($_rooms[$room_id])) {

				$room_data = array(
					'method' => 'rooms.room.get',
					'fields' => '*',
					'id' => $room_id,
				);

				$room_result = IRoomApp_helper::load($room_data);
				$room_result = json_decode($room_result, true);
				$_rooms[$room_id] = $room_result['result'];
			}
			$booking['room'] = $_rooms[$room_id];
			$booking['dateline'] = date('Y-m-d', strtotime($booking['start']));
			$order_result['result']['booking'][$bid] = $booking;
		}

		$order_result['result']['status_label'] = $this->status_list[$order_result['result']['status']];
		$order_result['result']['is_strict'] = $is_strict;

		return array(
			'code' => $order_result['code'],
			'msg' => $order_result['msg'],
			'data' => $order_result['result'],
		);

//		if ($order_result['code'] != 0) {
//			return Util_helper::result(null, $order_result['msg'], $order_result['code']);
//		}
//
//		return Util_helper::result($order_result['result']);
	}

	public function prepay1()
	{
		$app_id = isset($_POST['app_id']) ? $_POST['app_id'] : 0;
		$wx_apps = $this->config->item('wx_apps');

		$id = isset($_POST['id']) ? $_POST['id'] : 0;
		$mid = isset($_POST['mid']) ? $_POST['mid'] : 0;
		$tid = isset($_POST['tid']) ? $_POST['tid'] : 0;
		$sid = isset($_POST['sid']) ? $_POST['sid'] : 0;
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : 0;
		$merOrderId = isset($_POST['order_number']) ? $_POST['order_number'] : 0;
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

		if ($flag) {
			$return_data = array(
				'code' => '9999',
				'msg' => '非法请求，请联系管理员-1' . $totalAmount,
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
			'trade_no' => $merOrderId,
			'times' => $times,
			'amount' => $totalAmount,
			'payment_type' => 'UNIONPAY',
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		);
		$res = $this->pay_log->create($pay_log);

		/// $mid = '898445173110048';
		/// $tid = '67123758';
		$mid = '898445173110047';
		$tid = '67123274';
		$subMid = $wx_apps[$app_id]['wx_bankpay_mid'];
		$subTid = $wx_apps[$app_id]['wx_bankpay_tid'];

		$mid = $wx_apps[$app_id]['wx_bankpay_mid'];
		$tid = $wx_apps[$app_id]['wx_bankpay_tid'];
		$subMid = '';//$wx_apps[$app_id]['wx_bankpay_mid'];
		$subTid = '';//$wx_apps[$app_id]['wx_bankpay_tid'];

		$subAppId = $app_id;

//		if($shop_id == '439') {
//			$mid = '898445173110049';
//			$tid = '67123759';
//			$subMid = '898445186610001';
//			$subTid = '67124258';
//		}
//		$subAppId = 'wxa9dd96c791e01f15';

		if ((empty($subMid) || empty($subTid)) || ($subMid == 'undefined' || $subTid == 'undefined')) {
			//默认商户
			$data = array(
				'merOrderId' => $merOrderId,
				'totalAmount' => $totalAmount,
				'subAppId' => $subAppId,
				'subOpenId' => $openId,
				'mid' => $mid,
				'tid' => $tid,
			);
		} else {
			//子商户

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
			if (isset($res['miniPayRequest'])) {

				$this->pay_log->update_status($merOrderId);

				$return_data = array(
					'code' => '0',
					'msg' => '请支付',
					'result' => $res['miniPayRequest'],
					'return' => json_encode($res),
				);

			} else {
				$return_data = array(
					'code' => '9998',
					'msg' => '测试环境，无法支付',
					'return' => json_encode($res),
				);
			}
		} else {
			$return_data = array(
				'code' => '9999',
				'msg' => $res['errMsg'],
				'return' => json_encode($res),
				'_data' => $data,
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


	public function prepay_bankpay()
	{
		$id = isset($_POST['id']) ? $_POST['id'] : 0;
		$mid = isset($_POST['mid']) ? $_POST['mid'] : 0;
		$tid = isset($_POST['tid']) ? $_POST['tid'] : 0;

		$mid = '89844515813AAAE';
		$tid = 'AXKX6BP0';
		// $app_id = 'wx1b566be082e9f0d9';
		$app_id = 'wxa9dd96c791e01f15';
		// $app_id = 'wx1b566be082e9f0d9';

		$sid = isset($_POST['sid']) ? $_POST['sid'] : 0;
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : 0;
		$merOrderId = isset($_POST['order_number']) ? $_POST['order_number'] : 0;
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

		if ($flag) {
			$return_data = array(
				'code' => '9999',
				'msg' => '非法请求，请联系管理员-1' . $totalAmount,
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
			'trade_no' => $merOrderId,
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

		// 白马荟
//		 $mid = '898445173110047';
//		 $tid = '67123274';

		// 2021-12-24 新编号
		$mid = '898445173110062';
		$tid = '67124094';

//		if($shop_id == '439') {
//			$mid = '898445173110049';
//			$tid = '67123759';
//			$subMid = '898445186610001';
//			$subTid = '67124258';
//		}

		$subAppId = $app_id;

		if ((empty($subMid) || empty($subTid)) || ($subMid == 'undefined' || $subTid == 'undefined')) {
			//默认商户
			$data = array(
				'merOrderId' => $merOrderId,
				'totalAmount' => $totalAmount,
				'subAppId' => $subAppId,
				'subOpenId' => $openId,
				'mid' => $mid,
				'tid' => $tid,
			);
		} else {
			//子商户

			$subAppId = $app_id;

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
			if (isset($res['miniPayRequest'])) {

				$this->pay_log->update_status($merOrderId);

				$return_data = array(
					'code' => '0',
					'msg' => '请支付',
					'result' => $res['miniPayRequest'],
					'return' => json_encode($res),
				);

			} else {
				$return_data = array(
					'code' => '9998',
					'msg' => '测试环境，无法支付',
					'return' => json_encode($res),
				);
			}
		} else {
			$return_data = array(
				'code' => '9999',
				'msg' => $res['errMsg'],
				'return' => json_encode($res),
				'_data' => $data,
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

	public function prepayBak()
	{
		$id = isset($_POST['id']) ? $_POST['id'] : 0;
		$mid = isset($_POST['mid']) ? $_POST['mid'] : 0;
		$tid = isset($_POST['tid']) ? $_POST['tid'] : 0;

		$sid = isset($_POST['sid']) ? $_POST['sid'] : 0;
		$member_id = isset($_POST['member_id']) ? $_POST['member_id'] : 0;
		$merOrderId = isset($_POST['order_number']) ? $_POST['order_number'] : 0;
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

		if ($flag) {
			$return_data = array(
				'code' => '9999',
				'msg' => '非法请求，请联系管理员-1' . $totalAmount,
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
			'trade_no' => $merOrderId,
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

		if ($shop_id == '439') {
			$mid = '898445173110049';
			$tid = '67123759';
			$subMid = '898445186610001';
			$subTid = '67124258';
		}

		$subAppId = 'wxa9dd96c791e01f15';

		if ((empty($subMid) || empty($subTid)) || ($subMid == 'undefined' || $subTid == 'undefined')) {
			//默认商户
			$data = array(
				'merOrderId' => $merOrderId,
				'totalAmount' => $totalAmount,
				'subAppId' => $subAppId,
				'subOpenId' => $openId,
				'mid' => $mid,
				'tid' => $tid,
			);
		} else {
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
			if (isset($res['miniPayRequest'])) {

				$this->pay_log->update_status($merOrderId);

				$return_data = array(
					'code' => '0',
					'msg' => '请支付',
					'result' => $res['miniPayRequest'],
					'return' => json_encode($res),
				);

			} else {
				$return_data = array(
					'code' => '9998',
					'msg' => '测试环境，无法支付',
					'return' => json_encode($res),
				);
			}
		} else {
			$return_data = array(
				'code' => '9999',
				'msg' => $res['errMsg'],
				'return' => json_encode($res),
				'_data' => $data,
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


	public function pay()
	{
		$member_id = $this->input->get_post('member_id');
		$order_number = $this->input->get_post('order_number');
		$total_amount = $this->input->get_post('total_amount');
		$payment_method = $this->input->get_post('payment_method');

		if (empty($payment_method)) {
			$payment_method = 'WXPAY';
		}

		$order = array(
			'method' => 'orders.order.pay',
			'order_number' => $order_number,
			'payment_method' => $payment_method, //'UNIONPAY',
			'amount' => $total_amount / 100,
			'member_id' => $member_id,
		);

		$order_result = IRoomApp_helper::load($order);
		// log_message('debug', json_encode($order_result));
		$order_result = json_decode($order_result, true);
		// die(json_encode($order_result));

		$data = array(
			'method' => 'orders.order.get',
			'id' => 0,
			'by' => $order_number,
			'fields' => 'id,order_number,order_type,dateline,member_id,total_qty,total_amount,freight,fee,off,discount,final_amount,coupon_id,cuser,ctime,mtime,status,event_status,comment,removed',
			'receiver' => 1,
		);


		// die(json_encode($data));
		$order_result = IRoomApp_helper::load($data);

		$order_result = json_decode($order_result, true);

		return Util_helper::result($order_result);
		// return Util_helper::result($order_result['result']);
	}

	// 追加预约时间
	public function append()
	{
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		$data = json_decode($data, true);

		$member_id = isset($data['member_id']) ? $data['member_id'] : '';
		$order_number = isset($data['order_number']) ? $data['order_number'] : '';
		$booking_end = isset($data['booking_end']) ? $data['booking_end'] : '';
		$payment = isset($data['payment']) ? $data['payment'] : '';

		log_message('DEBUG', 'order.append#' . json_encode($data));

		if (empty($order_number) || empty($booking_end) || empty($payment)) {
			$return_data = array(
				'code' => '9999',
				'msg' => '参数不能为空',
			);

			die(json_encode($return_data));
		}

		$data['member_id'] = $member_id;
		$data['is_strict'] = 1;
		$data['method'] = 'orders.room.booking.renew';

		log_message('DEBUG', 'order.append#' . json_encode($data));

		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);

		log_message('DEBUG', 'order.append#' . json_encode($result));

		if ($result['code'] == 0) {
			return Util_helper::result($result['result']);
		} else {
			return Util_helper::result(null, $result['msg'], $result['code']);
		}
	}

	public function close()
	{
		$order_number = $this->input->get_post('order_number');

		$order = array(
			'method' => 'orders.order.close',
			'order_number' => $order_number,
		);

		$order_result = IRoomApp_helper::load($order);
		// log_message('debug', json_encode($order_result));
		$order_result = json_decode($order_result, true);

		return Util_helper::result($order_result);
	}

	public function change()
	{
		$member_id = isset($data['member_id']) ? $data['member_id'] : '';
		$order_number = $this->input->get_post('order_number');
		$room_id = $this->input->get_post('room_id');
		$booking_start = $this->input->get_post('booking_start');
		$booking_end = $this->input->get_post('booking_end');

		$order = array(
			'method' => 'orders.room.booking.change',
			'order_number' => $order_number,
			'room_id' => $room_id,
			'booking_start' => $booking_start,
			'booking_end' => $booking_end,
			'is_strict' => 1,
			'member_id' => $member_id,
		);

		$order_result = IRoomApp_helper::load($order);
		// log_message('debug', json_encode($order_result));
		$order_result = json_decode($order_result, true);

		if ($order_result['code'] == 0) {
			return Util_helper::result($order_result);
		} else {
			return Util_helper::result($order_result, $order_result['msg'], $order_result['code']);
		}

	}


	public function prepay()
	{
		$app_id = $this->input->get_post('app_id');
		$sid = $this->input->get_post('sid');
		$member_id = $this->input->get_post('member_id');
		$order_number = $this->input->get_post('order_number');
		$final_amount = $this->input->get_post('final_amount');
		$total_amount = $this->input->get_post('total_amount');

//		$final_amount = $total_amount;

		$identity = $this->input->get_post('openId');
		$type = $this->input->get_post('type', 'room');

		$app_id = 'wx1b566be082e9f0d9';

		if (empty($member_id) || empty($app_id)) {
			return Util_helper::result(null, '参数不能为空', -1);
		}

		if (is_array($member_id) || is_array($order_number)) {
			return Util_helper::result(null, '参数错误', -1);
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
			'amount' => $final_amount,
			'payment_type' => 'WXPAY',
			'status' => 1,
			'app_id' => $app_id,
			'out_trade_no' => '',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		);

		log_message('DEBUG', 'pay_input#' . json_encode($pay_log));

		$res = $this->pay_log->create($pay_log);

		$pay_log = new \stdClass();

		$pay_log->pay_title = '無二SPACE订房订单';
		$pay_log->pay_remark = 'room';
		$pay_log->pay_flag = 'room';
		$pay_log->pay_no = $pay_no;
		$pay_log->pay_amount = $final_amount;
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


		log_message('DEBUG', 'pay_result#' . json_encode($result));

		if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
			$result = json_decode($result['data'], true);

			$return_data = array(
				'code' => '0',
				'msg' => '请支付',
				'result' => $result,
				'return' => json_encode($res),
			);

			die(json_encode($return_data));
		} else {
			$return_data = array(
				'code' => '-1',
				'msg' => $result['err_code_des'],
			);

			die(json_encode($return_data));
		}
	}

	//	code: "0"
	//	msg: "请支付"
	//	result: {timeStamp: "1640755126", package: "prepay_id=wx29131846089206eb3f65671339cf460000",…}
	//		appId: "wxa9dd96c791e01f15"
	//		nonceStr: "ec04145bf5de4f5292552328515a0019"
	//		package: "prepay_id=wx29131846089206eb3f65671339cf460000"
	//		paySign: "Vr/HPx1E/WpELMTlBezVAbaQoBBOKltKdKo/DexzrWu/fMcARH3obKs2+zN0JMKoH2JC7eUXe0E6s8Qa6sPi60DQbhNPS6ZD4mfcgUfKexCcVYxAfpqfMJh/yE+k5SG0umlICiQSbDxIWiyhrhXHoxkhUf3QkM1oy3USPTptbSF+S8sc+f34XVM5PeNuUQagZm7lJHNvCq9mPZCtQiYdFSsk53gG1SUxCyoYT4x3JnIS5she3VKPy/1JVR6wGX2NaHP0I3nrZX6f+tw+D/r796mwPxjFMXzBmzD0D1lwzd54J4mtpoIL/wzJpMo1BIwVaij86oiNdAKld2bjdDy+Gw=="
	//		signType: "RSA"
	//		timeStamp: "1640755126"
	//	return: "{"msgType":"wx.unifiedOrder","connectSys":"UNIONPAY","msgSrc":"WWW.CHZSBMH.COM","delegatedFlag":"N","merName":"\u6f6e\u5dde\u5e02\u767d\u9a6c\u835f\u6587\u5316\u4f20\u64ad\u6709\u9650\u516c\u53f8","mid":"898445173110062","settleRefId":"25259091569N","tid":"67124094","totalAmount":1,"targetMid":"347336456","responseTimestamp":"2021-12-29 13:18:46","errCode":"SUCCESS","miniPayRequest":{"timeStamp":"1640755126","package":"prepay_id=wx29131846089206eb3f65671339cf460000","paySign":"Vr\/HPx1E\/WpELMTlBezVAbaQoBBOKltKdKo\/DexzrWu\/fMcARH3obKs2+zN0JMKoH2JC7eUXe0E6s8Qa6sPi60DQbhNPS6ZD4mfcgUfKexCcVYxAfpqfMJh\/yE+k5SG0umlICiQSbDxIWiyhrhXHoxkhUf3QkM1oy3USPTptbSF+S8sc+f34XVM5PeNuUQagZm7lJHNvCq9mPZCtQiYdFSsk53gG1SUxCyoYT4x3JnIS5she3VKPy\/1JVR6wGX2NaHP0I3nrZX6f+tw+D\/r796mwPxjFMXzBmzD0D1lwzd54J4mtpoIL\/wzJpMo1BIwVaij86oiNdAKld2bjdDy+Gw==","appId":"wxa9dd96c791e01f15","signType":"RSA","nonceStr":"ec04145bf5de4f5292552328515a0019"},"targetStatus":"SUCCESS|SUCCESS","seqId":"25259091569N","merOrderId":"6630211229211229481769786717","status":"WAIT_BUYER_PAY","targetSys":"WXPay","sign":"AEA6DF4BB15DA358B19E607B2081D35A"}"

	public function pay_notify()
	{
		$pay_no = $this->uri->segment(4);

		$params = $this->input->get();
		$params += $this->input->post();

		$xml = file_get_contents('php://input');//监听是否有数据传入

		$this->load->model('Paylog_model', 'pay_log');
		$pay_log = $this->pay_log->get($pay_no);

		if (empty($pay_log)) {
			die('fail-1');
		}

		if ($pay_log->status != 1) {
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
			die('fail-3');
		}

		$this->pay_log->update_status($pay_no, $result['trade_no']);

		$data['order_number'] = $pay_log->order_sn;
		$data['payment_method'] = 'WXPAY';
		$data['amount'] = $result['amount'];
		$data['method'] = 'orders.order.pay';

		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);

		log_message('DEBUG', 'WX_PAY_NOTIFY::' . $pay_no . ',' . json_encode($result));
	}
}
