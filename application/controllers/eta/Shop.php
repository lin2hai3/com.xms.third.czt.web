<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller
{
	public function index()
	{

	}

	public function show()
	{
		$id = $this->input->get_post('id');

		// $id = 458;

		$data = array(
			'method' => 'shops.shop.get',
			'fields' => '*',
			'id' => $id,
		);

		$result = Eta_app_helper::load($data);

		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			$locations = explode(',', $result['result']['location']);
			$result['result']['longitude'] = $locations[1];
			$result['result']['latitude'] = $locations[0];
		}

		$result = json_encode($result, JSON_UNESCAPED_UNICODE);

		die($result);
	}
}
