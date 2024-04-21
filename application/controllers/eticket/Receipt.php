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
		$config = array(
			'api_host' => 'http://v5.666os.com/',
			'api_app_id' => 'linhai',
			'api_app_key' => 'linhai',
		);

		$data['method'] = 'tickets.receipts.owner.get';
		$data['method'] = 'tickets.logs.get';
		$data['fields'] = 'id,ticket_id,receipt_id,member_id,shop_id,admin_id,ctime';
		$data['keyword'] = '围棋';
		$data['shop_id'] = 450;
		$data['page_size'] = '20';

		$result = Client_helper::loadWithConfig($config, $data);

		die($result);
	}
}
