<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket extends CI_Controller
{
	protected $default_inventory = 80;

	public function index()
	{
		$page = $this->input->get_post('page');
		$keyword = $this->input->get_post('keyword');
		$pagination = Util_helper::getPagination($page);

		$params = array();
		$params['method'] = 'tickets.tickets.get';
		$params['fields'] = '*';
		$params['page'] = $page;
		$params['page_size'] = $pagination->limit;
		$params['keyword'] = $keyword;
		$params['orderby'] = 'id DESC';

		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		$pagination = Util_helper::getPagination($page);
		$pagination->setCount($result['result']['total_results']);
		$result['result']['pagination'] = $pagination;

		return Util_helper::result($result['result']);
	}

	public function show()
	{
		$id = $this->input->get_post('id');
		$show_full = $this->input->get_post('show_full');

		if (empty($show_full)) {
			$show_full = 0;
		}

		$data = array();
		$data['method'] = 'tickets.ticket.get';
		$data['fields'] = '*';
		$data['id'] = $id;
		$data['detail'] = 1;
		$data['extend'] = 1;
		$data['show_full'] = $show_full;

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
				} else {
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

		for ($idx = 0; $idx < 10; $idx++) {
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

			if ($show_full == 1) {
				if (empty($time_spans)) {
					$time_spans = array();
				}
				$rules[$date] = $time_spans;
			} else {
				if (!empty($time_spans)) {
					$rules[$date] = $time_spans;
				}
			}
		}

		$_rules = array();
		foreach ($rules as $date => $rule) {

			$_rule['date'] = $date;
			$_rule['weekdate'] = $this->get_weekdate($date);

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
			}

			unset($item);

			$_rule['items'] = $rule;
			$_rules[] = $_rule;
		}

		$result['result']['rules'] = $_rules;

		die(json_encode($result));
	}

	public function update_extend()
	{
		$id = $this->input->get_post('id');
		$rules = $this->input->get_post('rules');

		$rules = json_decode($rules, true);

		$date_rules = array();

		foreach ($rules as $rule) {

			if (count($rule['items']) == 0) {
				continue;
			}

			$str = '';
			foreach ($rule['items'] as $item) {
				if (!empty($str)) {
					$str .= ',';
				}
				$str .= $item['time_span'] . '|' . $item['total_inventory'];
			}

			$_date = date('Ymd', strtotime($rule['date']));
			$date_rules[$_date] = $str;
		}

		$params = array();
		$params['method'] = 'tickets.ticket.get';
		$params['fields'] = '*';
		$params['id'] = $id;
		$params['extend'] = 1;

		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		$extend = '';
		if (isset($result['result']['extend'])) {
			$extend = $result['result']['extend'];
		}

		$rows = explode("\n", $extend);

		$db_rules = array();

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

			if ($type == 1) {
				$db_rules['default'] = $time_span;
			}

			if ($type == 2) {
				$db_rules[$date] = $time_span;
			}

//			if ($type == 3) {
//				$db_rules[$date] = $time_span;
//
//				$_date = date('Ymd', strtotime($date));
//
//				if (isset($date_rules[$_date])) {
//					$db_rules[$date] = $date_rules[$_date];
//				}
//			}
		}


		foreach ($date_rules as $date => $rule) {
			$db_rules[$date] = $rule;
		}

		$db_rules1 = array();
		foreach ($db_rules as $date => $db_rule) {
			$db_rules1[] = $date . ':' . $db_rule;
		}

		$new_extend = implode("\n", $db_rules1);

		$params = array();
		$params['method'] = 'tickets.ticket.update';
		$params['id'] = $id;
		$params['extend'] = $new_extend;
		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		die(json_encode($result));
	}

	public function logs()
	{
		$page = $this->input->get_post('page');
		$keyword = $this->input->get_post('keyword');
		$pagination = Util_helper::getPagination($page);

		$params = array();
		$params['method'] = 'tickets.logs.get';
		$params['fields'] = '*';
		$params['page'] = $page;
		$params['page_size'] = $pagination->limit;
		$params['keyword'] = $keyword;
		$params['orderby'] = 'id DESC';

		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		$pagination = Util_helper::getPagination($page);
		$pagination->setCount($result['result']['total_results']);
		$result['result']['pagination'] = $pagination;

		return Util_helper::result($result['result']);
	}

	protected function start_with_week($string)
	{
		return $this->start_with($string, '0:')
			|| $this->start_with($string, '1:')
			|| $this->start_with($string, '2:')
			|| $this->start_with($string, '3:')
			|| $this->start_with($string, '4:')
			|| $this->start_with($string, '5:')
			|| $this->start_with($string, '6:');
	}

	protected function get_weekdate($date)
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

	protected function start_with($string, $startString)
	{
		return strncmp($string, $startString, strlen($startString)) === 0;
	}

	public function fetch_ticket_qrcode()
	{
		$url = $this->input->get_post('url');
	}
}
