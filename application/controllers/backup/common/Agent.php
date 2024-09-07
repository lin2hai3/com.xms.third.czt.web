<?php

namespace common;
use CI_Controller;
use Client_helper;
use Util_helper;

defined('BASEPATH') or exit('No direct script access allowed');

class Agent extends CI_Controller
{

	public $sets = array(
		'wifi' => array(
			'name' => 'WIFI',
			'image' => '../../static/wifi.png',
		),
		'automat' => array(
			'name' => '自动售卖机',
			'image' => '../../static/automat.png',
		),
		'charger' => array(
			'name' => '共享充电宝',
			'image' => '../../static/charger.png',
		),
		'projector' => array(
			'name' => '投影仪',
			'image' => '../../static/projector.png',
		),
	);

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 *        http://example.com/index.php/welcome
	 *    - or -
	 *        http://example.com/index.php/welcome/index
	 *    - or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		// echo 'common.shop.index';
		// $this->load->view('welcome_message');

		// $api_config['app_id'] = $this->config->item('api_app_id');
		// $api_config['app_key'] = $this->config->item('api_app_key');

		// $this->input->get_post('method');

		// $data = array(
		// 	'method' => 'weixin.openid.check',
		// 	'appid' => 'wxa9dd96c791e01f15',
		// 	'openid' => 'oV_0Q5Tf95STu79XnO673bQ3c9IA'
		// );

		// $method = $this->input->get_post('method');
		$data = $this->input->get_post('data');
		$data = json_decode($data, true);
		// die($this->uri->segment(4));

		$method = $this->uri->segment(4);
		$data['method'] = $method;

		if (!isset($data['method']) || empty($data['method'])) {
			return Util_helper::result(null, 'no method', 1);
		}

		if ($data['method'] == 'weixin.profile.get') {
			$data['weixin_id'] = intval($data['weixin_id']);

			if (empty($data['weixin_id'])) {
				return Util_helper::result(null, 'error weixin_id', 1);
			}
		}

		$result = Client_helper::load($data);
		// die($result);
		$result = json_decode($result, true);

		if (!isset($result['result'])) {
			$result['result'] = false;
		}

		// debug
		if (empty($result['result'])) {
			$result['result'] = array();
			$result['result']['_data'] = $data;
		}


		$data = $result['result'];
		switch ($method) {
			case 'shops.shop.get':
				$data['gallery'] = array(array(
					'img' => $data['image']
				));

				$location = explode(',', $data['location']);
				$data['longitude'] = isset($location[0]) ? $location[0] : '';
				$data['latitude'] = isset($location[1]) ? $location[1] : '';

				// $data['longitude'] = 116.632301;
				// $data['latitude'] = 23.661701;
				break;

			case 'rooms.rooms.get':
				// die(json_encode($result));
				// die(json_encode($data));
				$_rows = array();
				foreach ($data['rows'] as $row) {

					$sets = explode(',', $row['facilities']);
					$_sets = array();
					foreach ($sets as $set) {
						if (isset($this->sets[$set])) {
							array_push($_sets, $this->sets[$set]);
						}
					}

					$row['sets'] = $_sets;

//					if (!empty($row['area'])) {
//						array_push($row['policy_items'], $row['area'] . '平方');
//					}
//					if (!empty($row['seats'])) {
//						array_push($row['policy_items'], '可容' . $row['seats'] . '人');
//					}

//					if (!empty($row['has_wifi'])) {
//						array_push($row['sets'], 'WIFI');
//					}
//					if (!empty($row['has_automat'])) {
//						array_push($row['sets'], '自动售货机');
//					}
//					if (!empty($row['has_charger'])) {
//						array_push($row['sets'], '共享充电宝');
//					}

					$row['gallery'] = array(array(
						// 'img' => 'https://img11.xms.wiki/images/czt_111.jpg',
						'img' => isset($row['pic_url']) ? $row['pic_url'] : '',
					));

					$row['products'] = array(array(
						'base_rates' => $row['base_rates'],
						'increase_rates' => $row['increase_rates'],
						'price' => $row['base_rates'],
					));

					//if ($row['enabled']) {
					$_rows[] = $row;
					//}
				}
				$data['rows'] = $_rows;
				break;

			default:
				break;
		}

		return Util_helper::result($data, $result['msg'], $result['code']);
	}

}
