<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Yipiao extends CI_Controller
{
	public function get_ticket_list()
	{
		$type = $this->input->get_post('type');
		$keyword = $this->input->get_post('keyword');

		$url = 'https://www.czfclxs.com/api/ticket/comboTicket/getComboTicketPoJoList';
		$data = array();

		if (!empty($type)) {
			$data['type'] = $type;
		}

		if (!empty($keyword)) {
			$data['comboName'] = $keyword;
		}

		if (empty($data)) {
			$data = '{}';
		} else {
			$data = json_encode($data);
		}

		$res = Request_helper::api_request($url, $data, 'POST');

		$res = json_decode($res, true);

		if (isset($res['data']) && is_array($res['data'])) {
			foreach ($res['data'] as &$item) {
				$item['wx_appid'] = 'wxf207735ea23c7cb5';
				$item['wx_path'] = 'pages/singleTicketDetails/index?id=' . $item['id'];
				$item['wx_extra_data'] = array(
					'memberCardIdBMH' => '1753313518252568577'
				);
			}
		}

		unset($item);

		die(json_encode($res));
	}

	public function create_receipt()
	{

	}

	public function cancel_receipt()
	{

	}
}
