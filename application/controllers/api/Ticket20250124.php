<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ticket20250124 extends CI_Controller
{
	protected $is_debug = false;
	protected $test_ids = 328;
	protected $hidden_ids = array(334);

	protected $share_ticket_ids = array(332, 334);

	protected $default_inventory = 80;

	public function index()
	{
		$page = $this->input->get_post('page');
		$keyword = $this->input->get_post('keyword');
		$is_published = $this->input->get_post('is_published');
		$is_published = 1;
		$pagination = Util_helper::getPagination($page);

		$params = array();
		$params['method'] = 'tickets.tickets.get';
		$params['fields'] = '*';
		$params['page'] = $page;
		$params['page_size'] = $pagination->limit;
		$params['keyword'] = $keyword;
		// $params['orderby'] = 'id DESC';
		$params['orderby'] = 'ontop DESC';

		if (!empty($is_published)) {
			$params['is_published'] = $is_published;
		}

		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		$pagination = Util_helper::getPagination($page);
		$pagination->setCount($result['result']['total_results']);
		$result['result']['pagination'] = $pagination;

		return Util_helper::result($result['result']);
	}

	public function page_index()
	{
		$page = $this->input->get_post('page');
		$keyword = $this->input->get_post('keyword');
		$pagination = Util_helper::getPagination($page);

		if (empty($page)) {
			$page = 1;
		}

		$params = array();
		$params['method'] = 'tickets.tickets.get';
		$params['fields'] = '*';
		$params['page'] = $page;
		$params['is_published'] = 1;
		$params['is_ontop'] = 1;
		$params['page_size'] = $pagination->limit;
		$params['keyword'] = $keyword;
		$params['orderby'] = 'id DESC';

		if ($this->is_debug) {
			$params['ids'] = $this->test_ids;
		}

		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		$pagination = Util_helper::getPagination($page);
		$pagination->setCount($result['result']['total_results']);
		$result['result']['pagination'] = $pagination;

		foreach ($result['result']['rows'] as $key => &$row) {
			if (in_array($row['id'], $this->hidden_ids)) {
				unset($result['result']['rows'][$key]);
			}
		}

		unset($row);

		$result['result']['rows'] = array_values($result['result']['rows']);

		return Util_helper::result($result['result']);
	}

	public function show()
	{
		$id = $this->input->get_post('id');
		$sid = $this->input->get_post('sid');
		$show_full = $this->input->get_post('show_full');
		$show_remain = $this->input->get_post('show_remain');

		if (empty($show_full)) {
			$show_full = 0;
		}

		if (empty($show_remain)) {
			$show_remain = 1;
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


		$result['result']['show_share'] = false;
		if (in_array($id, $this->share_ticket_ids)) {
			$result['result']['show_share'] = true;
		}


		$this->load->model('Ticket_model', 'ticket');
		$db_ticket = $this->ticket->fetch($id);
		$result['result']['pay_channel'] = $db_ticket->pay_channel;


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
			if ($this->start_with($row, 'default#')) {
				$type = 1;
				$time_span = str_replace('default#', '', $row);
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

			if (!empty($default_rule)) {
				$time_spans = $default_rule;
			}

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

				$start_time = date('Y-m-d H:i', strtotime($start_time));
				$end_time = date('Y-m-d H:i', strtotime($end_time));

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

				if ($inventory < 0) {
					$inventory = 0;
				}

				$item['inventory'] = $inventory;
				$item['sale_count'] = $count_result['result']['count'];

				$item['real_inventory'] = 0;
				$item['real_sale_count'] = 0;

				if ($show_remain == 1) {
					$data = array();
					$data['method'] = 'tickets.receipts.get';
					$data['fields'] = '*';
					$data['ticket_id'] = $id;
					$data['stime'] = $start_time;
					$data['etime'] = $end_time;
					$data['page_size'] = '20';

					$remain_result = EtaApp_helper::load($data);

					$remain_result = json_decode($remain_result, true);

					$real_sale_count = 0;
					if ($remain_result['result']['total_results'] > 0) {
						foreach ($remain_result['result']['rows'] as $row) {
							$real_sale_count += ceil($row['amount'] / $row['price']); // 老人小孩半票 也算一个位
						}
					}

					$item['real_inventory'] = $item['total_inventory'] - $real_sale_count;
					$item['real_sale_count'] = $real_sale_count;

					$item['inventory'] = $item['real_inventory'];
				}

				// 过期都显示卖完
				if (strtotime($end_time) < time()) {
					$item['inventory'] = 0;
				}


				$item['_start_time'] = $start_time;
				$item['_end_time'] = $end_time;
				$item['_sale_count'] = $count_result['result']['count'];
			}

			unset($item);

			$_rule['items'] = $rule;
			$_rules[] = $_rule;
		}

		$result['result']['rules'] = $_rules;


		$result['result']['skus'] = $this->get_skus($id);

		die(json_encode($result));
	}

	public function get_skus($id)
	{
		$skus = array();

		if ($id == 332 || $id == 333) {
			$skus['100001'] = array(
				'code' => '100001',
				'name' => '成人票',
				'rate' => 1,
				'count' => 1,
				'discount' => 1,
			);

			$skus['100002'] = array(
				'code' => '100002',
				'name' => '老人儿童半价票',
				'rate' => 0.5,
				'count' => 1,
				'discount' => 0.5,
			);

			$skus['100003'] = array(
				'code' => '100003',
				'name' => '一大一小/一大一老',
				'rate' => 1.5,
				'count' => 2,
				'discount' => 0.75,
			);

			$skus['100004'] = array(
				'code' => '100004',
				'name' => '三人团票',
				'rate' => 3,
				'count' => 3,
				'discount' => 1,
			);

			$skus['100005'] = array(
				'code' => '100005',
				'name' => '五人团票',
				'rate' => 5,
				'count' => 5,
				'discount' => 1,
			);
		}

		$skus = array_values($skus);

		return $skus;
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
			if ($this->start_with($row, 'default#')) {
				$type = 1;
				$time_span = str_replace('default#', '', $row);
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
			$db_rules1[] = $date . '#' . $db_rule;
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

	public function update_ticket()
	{
		$sid = $this->input->get_post('sid');

		// $id = 5803;

		// fetch weixin id
		$params = array(
			'method' => 'weixin.sid.decode',
			'fields' => '*',
			'sid' => $sid,
		);

		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		if (!isset($result['result']['weixin_id'])) {
			return Util_helper::result(null, 'error input', -1);
		}

		$weixin_id = $result['result']['weixin_id'];

		$id = $this->input->get_post('id');
		$pay_channel = $this->input->get_post('pay_channel');

		if (empty($id) || empty($pay_channel)) {
			$result = array('code' => -1, 'msg' => 'error input');
			die(json_encode($result));
		}

		$this->load->model('Ticket_model', 'ticket');
		$db_ticket = $this->ticket->setPayChannel($id, $pay_channel, $weixin_id);

		$result = array('code' => 0, 'msg' => 'success');
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
		$sid = $this->input->get_post('sid');
//		$url = 'https://etu.666os.com/wxacode/agents/2840_TIC_332.png';
		$bg_img = 'https://linhai.666os.com/assets/images/czt_ygw_2.jpg';
//		$bg_img = 'https://etu.666os.com/wxacode/agents/2840_TIC_332.png';

		if (empty($sid)) {
			$sid = str_replace('https://etu.666os.com/wxacode/agents/', '', $url);
			$sid = str_replace('_TIC_332.png', '', $sid);
		}

		// fetch weixin id
		$data = array();
		$data['method'] = 'weixin.sid.decode';
		$data['fields'] = '*';
		$data['sid'] = $sid;

		$result = EtaApp_helper::load($data);
		$result = json_decode($result, true);
		$weixin_id = $result['result']['weixin_id'];

		// fetch member_id
		$data = array();
		$data['method'] = 'weixin.member.id.get';
		$data['fields'] = '*';
		$data['weixin_id'] = $weixin_id;

		$result = EtaApp_helper::load($data);
		$result = json_decode($result, true);
		$member_id = $result['result']['member_id'];

		// fetch member
		$data = array();
		$data['method'] = 'members.member.get';
		$data['fields'] = '*';
		$data['id'] = $member_id;

		$result = EtaApp_helper::load($data);
		$result = json_decode($result, true);
		$flag = $result['result']['flag'] . substr($result['result']['mobile'], -4);





		list($bg_width, $bg_height) = getimagesize($bg_img);
		list($url_width, $url_height) = getimagesize($url);
		//把二维码压缩
		$new_width = $url_width / 4;
		$new_height = $url_height / 4;

		$width = $bg_width; // 最终图像的宽度
		$height = $bg_height + $new_height + 50; // 最终图像的高度

		$dst_image = imagecreatetruecolor($bg_width, $bg_height); // 创建一个空白的目标图像
		// 分配白色为背景颜色
		$white = imagecolorallocate($dst_image, 255, 255, 255);
		// 填充整个图像
		imagefill($dst_image, 0, 0, $white);


//		file_put_contents('image1.jpg', file_get_contents($bg_img));
//		file_put_contents('image2.jpg', file_get_contents($url));
		// 加载源图片
		$src_image1 = imagecreatefromjpeg($bg_img);
		$src_image2 = imagecreatefromjpeg($url);

		//压缩二维码
		$newImage = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($newImage, $src_image2, 0, 0, 0, 0, $new_width, $new_height, $url_width, $url_height);

		// 将源图片拷贝到目标图像中
		imagecopyresampled($dst_image, $src_image1, 0, 0, 0, 0, $bg_width, $bg_height, imagesx($src_image1), imagesy($src_image1)); // 拷贝第一张图片到左半边
		imagecopyresampled($dst_image, $newImage, 220, 827, 0, 0, $new_width, $new_height, imagesx($newImage), imagesy($newImage)); // 拷贝第二张图片到右半边

//		$font = 'msyh.ttc';
		$font = "/html/linhai/assets/fonts/msyhbd.ttc";;

		$text = "府城英歌舞体验馆欢迎您";
		$color = imagecolorallocatealpha($dst_image, 0, 0, 0, 0);
		imagefttext($dst_image, 30, 0, 125, $bg_height + $new_height / 2 + 40, $color, $font, $text);

		// imagefttext($dst_image, 24, 0, 550, 60, $color, $font, $flag); // 整个手机号码的位置
		imagefttext($dst_image, 24, 0, 650, 60, $color, $font, $flag); // 手机号后四位的位置

		// 保存拼接后的图像
		$file_name = time() . '.png';
		imagepng($dst_image, $file_name);

		// 销毁图像资源
		imagedestroy($dst_image);
		imagedestroy($src_image1);
		imagedestroy($src_image2);
		imagedestroy($newImage);

		$content = file_get_contents('/html/linhai/v2/' . $file_name);
		$content = base64_encode($content);

		//删除图片文件
		unlink($file_name);

		if ($content !== false) {
			$data = array(
				'code' => 0,
				'msg' => 'success!',
				'result' => $content
			);
		} else {
			$data = array(
				'code' => 1,
				'msg' => '读取文件失败',
			);
		}

		die(json_encode($data));
	}

//	public function test()
//	{
//		$fonts = shell_exec('fc-list 2>/dev/null');
//
//		// 清理输出并去除重复字体
//		$fontArray = array_unique(explode("\n", $fonts));
//
//		print_r($fontArray);
//	}
}
