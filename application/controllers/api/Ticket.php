<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket extends CI_Controller
{
	protected $default_inventory = 80;

	public function show()
	{
		$id = $this->input->get_post('id');

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
			if ($this->startsWith($row, 'default:')) {
				$type = 1;
				$time_span = str_replace('default:', '', $row);
				$date = 'default';
			} elseif ($this->startWithWeek($row)) {
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
			if (strpos($time_span, "|") > -1) {
				$time_span = explode("|", $time_span);
				$time_array = explode(",", $time_span[0]);
				$inventory = $time_span[1];
			} else {
				$time_array = explode(",", $time_span);
				$inventory = $this->default_inventory;
			}

			$_time_array = array();
			foreach ($time_array as $time_item) {
				$_time_array[] = array(
					'time_span' => $time_item,
					'inventory' => $inventory,
					'show_inventory' => true,
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
			}

			unset($item);

			$_rule['items'] = $rule;
			$_rules[] = $_rule;
		}


		$result['result']['rules'] = $_rules;

		die(json_encode($result));
	}

	function startWithWeek($string)
	{
		return $this->startsWith($string, '0:')
			|| $this->startsWith($string, '1:')
			|| $this->startsWith($string, '2:')
			|| $this->startsWith($string, '3:')
			|| $this->startsWith($string, '4:')
			|| $this->startsWith($string, '5:')
			|| $this->startsWith($string, '6:');
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

	function startsWith($string, $startString)
	{
		return strncmp($string, $startString, strlen($startString)) === 0;
	}
}
