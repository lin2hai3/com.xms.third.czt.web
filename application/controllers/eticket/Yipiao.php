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

		$config = array(
			'api_host' => 'http://etr.666os.com/',
			'api_app_id' => 'cticket',
			'api_app_key' => 'qZcKiQmN',
		);

		$params = array(
			'method' => 'api_whc.ctickets.get',
			'fields' => '*',
			'page' => $page,
		);

		$result = Client_helper::loadWithConfig($config, $params);

		// var_dump($result);

		$result = json_decode($result, true);

		$result['result']['pages'] = ceil($result['result']['total_results'] / $result['result']['page_size']);

		$this->load->view('eticket/yipiao/index', array('result' => $result));
	}
}
