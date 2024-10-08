<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2022/2/15
 * Time: 3:00
 */

class Receipt extends CI_Controller
{
	public function index()
	{
		$page = $this->input->get_post('page');
		$keyword = $this->input->get_post('keyword');
		$pagination = Util_helper::getPagination($page);

		$params = array();
		$params['method'] = 'tickets.receipts.get';
		$params['fields'] = '*';
		$params['page'] = $page;
		$params['page_size'] = $pagination->limit;
		$params['keyword'] = $keyword;
		$params['orderby'] = 'id DESC';

		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		foreach ($result['result']['rows'] as &$row) {
			$params = array(
				'method' => 'members.member.get',
				'fields' => '*',
				'id' => $row['member_id'],
			);

			$member_result = EtaApp_helper::load($params);
			$member_result = json_decode($member_result, true);

			$row['member_phone'] = $member_result['result']['mobile'];
		}

		unset($row);

		$pagination->setCount($result['result']['total_results']);
		$result['result']['pagination'] = $pagination;

		return Util_helper::result($result['result']);
	}

	public function add()
	{
		$sid = $this->input->get_post('sid');
		$ticket_id = $this->input->get_post('ticket_id');
		$stime = $this->input->get_post('stime');
		$etime = $this->input->get_post('etime');
		$timerange = $this->input->get_post('timerange');
		$remark = $this->input->get_post('remark');
		$comment = $this->input->get_post('comment');
		$qty = $this->input->get_post('qty');

		$ticket_id = intval($ticket_id);

		if (empty($ticket_id)) {
			die(json_encode(array('code' => -1, 'msg' => 'error input')));
		}

		$has_inj = Util_helper::containsSqlInjection($remark);

		if ($has_inj) {
			die(json_encode(array('code' => -1, 'msg' => 'error input')));
		}

		$has_inj = Util_helper::containsSqlInjection($comment);

		if ($has_inj) {
			die(json_encode(array('code' => -1, 'msg' => 'error input')));
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

		$data = array();
		$data['method'] = 'tickets.receipt.insert';

		$remark = '场次：' . $stime . '至' . $etime . ' ' . $remark;

		$data['ticket_id'] = $ticket_id;
		$data['member_id'] = $member_id;
		$data['stime'] = $stime;
		$data['etime'] = $etime;
		$data['timerange'] = $timerange;
		$data['remark'] = $remark;
		$data['comment'] = $comment;
		$data['qty'] = $qty;

		$result = EtaApp_helper::load($data);

		die(json_encode(json_decode($result, true), JSON_UNESCAPED_UNICODE));
	}

	public function apply()
	{
		$receipt_id = $this->input->get_post('receipt_id');
		$admin_id = $this->input->get_post('admin_id');

		$params = array(
			'method' => 'tickets.receipt.apply',
			'receipt_id' => $receipt_id,
			'admin_id' => $admin_id,
			'stime_offset' => 15 * 60, // 提前入场时间量（单位：秒）
		);

		$apply_result = EtaApp_helper::load($params);
		// $str = $apply_result;
		$apply_result = json_decode($apply_result, true);

		if ($apply_result['code'] != 0) {
			$msg = $apply_result['msg'];

			return Util_helper::result(null, $msg, -1);
		}

		return Util_helper::result(null);
	}

	public function owner()
	{
		$member_id = $this->input->get_post('member_id');
		$shop_id = $this->input->get_post('shop_id');

		$params = array(
			'method' => 'tickets.receipts.owner.get',
			'member_id' => $member_id,
			'shop_id' => $shop_id,
			'stime_offset' => 15 * 60, // 提前入场时间量（单位：秒）
		);

		$result = EtaApp_helper::load($params);
		// $str = $apply_result;
		$result = json_decode($result, true);

		die(json_encode($result));
	}

	public function auto_confirm()
	{
		show_404();

		$page = $this->input->get_post('page');
		if (empty($page)) {
			$page = 1;
		}

		$page = 3;
		$pagination = Util_helper::getPagination($page, 200);

		$params = array();
		$params['method'] = 'tickets.receipts.get';
		$params['fields'] = '*';
		$params['status'] = 'CONFIRMED';
		$params['keyword'] = '英歌舞体验馆预约';
		$params['page'] = $page;
		$params['page_size'] = $pagination->limit;
		$params['orderby'] = 'id DESC';

		$result = EtaApp_helper::load($params);
		// die($result);
		$result = json_decode($result, true);
		echo $result['result']['total_results'] . '<br/>';

		$total_count = 0;
		$total_amount = 0;
		$do_count = 0;

		foreach ($result['result']['rows'] as &$row) {
			echo $row['id'] . '#' . $row['ticket_id'] . $row['status'] . '<br/>';

			$total_count++;
			$total_amount += $row['amount'];

			if ($row['ticket_id'] == 332 && $row['status'] == 'CONFIRMED') {
				$params = array(
					'method' => 'tickets.receipt.update',
					'id' => $row['id'],
					'status' => 'USED',
				);

				$update_result = EtaApp_helper::load($params);

				$do_count++;
			}
		}

		echo '合计：单数：' . $total_count . '<br/>';
		echo '合计：金额：' . $total_amount . '<br/>';
		echo '核销：单数：' . $do_count . '<br/>';

		unset($row);
	}
}
