<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once 'application/libraries/RequestUtil.php';

class Yipiao extends CI_Controller
{
	public $app_id = 'wxa9dd96c791e01f15';
	public $app_secret = 'f30d1c712244a0deae13e1de523f56de';


	public function index()
	{
		$page = $this->input->get_post('page', 1);

		$params = array(
			'method' => 'api_whc.ctickets.get',
			'fields' => '*',
			'page' => $page,
		);

		$result = EtaApp_helper::load($params);

		// var_dump($result);

		$result = json_decode($result, true);

		$result['result']['pages'] = ceil($result['result']['total_results'] / $result['result']['page_size']);

		$this->load->view('eticket/yipiao/index', array('result' => $result));
	}

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

}
