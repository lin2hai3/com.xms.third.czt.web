<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member extends CI_Controller
{
	public function index()
	{
		
	}

	public function show()
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

		// fetch member_id
		$params = array(
			'method' => 'weixin.member.id.get',
			'fields' => '*',
			'weixin_id' => $weixin_id,
		);

		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		if (!isset($result['result']['member_id'])) {
			return Util_helper::result(null, 'error input', -1);
		}

		$member_id = $result['result']['member_id'];

		$params = array(
			'method' => 'members.member.get',
			'fields' => '*',
			'id' => $member_id,
		);

		$result = EtaApp_helper::load($params);
		$result = json_decode($result, true);

		if ($result['result']['id'] == 2840) {
			$result['result']['flag'] = 'A';
		}

		die(json_encode($result));
	}
}
