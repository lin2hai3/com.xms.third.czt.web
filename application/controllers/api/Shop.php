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

		$result = EtaApp_helper::load($data);

		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			$locations = explode(',', $result['result']['location']);
			$result['result']['longitude'] = $locations[1];
			$result['result']['latitude'] = $locations[0];

			$result['result']['address'] = $result['result']['address'] . '(点击进入导航)';
		}

		$result = json_encode($result, JSON_UNESCAPED_UNICODE);

		die($result);
	}

	public function addr()
	{
		$id = $this->input->get_post('id');

		if ($id == 332) {
			header('Location: https://map.qq.com/?addr=%E5%B9%BF%E4%B8%9C%E7%9C%81%E6%BD%AE%E5%B7%9E%E5%B8%82%E6%B9%98%E6%A1%A5%E5%8C%BA%E7%BB%B5%E5%BE%B7%E5%B9%BC%E5%84%BF%E5%9B%AD%E5%8C%97%28%E5%A4%AA%E5%B9%B3%E8%B7%AF%E8%A5%BF%29&isopeninfowin=1&markertype=1&name=%E5%BA%9C%E5%9F%8E%E8%8B%B1%E6%AD%8C%E8%88%9E%E4%BD%93%E9%AA%8C%E9%A6%86&pointx=116.651&pointy=23.6684&ref=WeChat&type=marker');
		}
	}
}
