<?php

namespace eticket;
use CI_Controller;
use RequestUtil;
use Util_helper;

defined('BASEPATH') or exit('No direct script access allowed');

require_once 'application/libraries/RequestUtil.php';

class User extends CI_Controller
{
	public $app_id = 'wxa9dd96c791e01f15';
	public $app_secret = 'f30d1c712244a0deae13e1de523f56de';

	public function oauth()
	{
		$code = $this->input->get_post('code');
		$app_id = $this->input->get_post('app_id');

		if (empty($code)) {
			return Util_helper::result(null, 'no code', 1);
		}

		if (empty($app_id)) {
			return Util_helper::result(null, 'no app_id', 1);
		}

		$res = $this->getGrantInfoForWxapp($app_id, $code);

		if (!$res) {
			return Util_helper::result(null, 'no openid', 1);
		}

		$open_id = $res['openid'];

		$this->load->model('User_model', 'user');
		$user = $this->user->getByOpenId($open_id);

		if (empty($user)) {

			$this->db->trans_begin();

			$retry = 20;

			do {
				$this->user->create(array(
					'wx_app_id' => $app_id,
					'wx_open_id' => $open_id,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
					'status' => 1,
				));
				$user = $this->user->getByOpenId($open_id);

			} while ($retry-- > 0 && empty($user));

			$this->db->trans_commit();
		}

		return Util_helper::result(array('user_id' => $user->id));
	}

	public function get_open_id()
	{
		$user_id = $this->input->get_post('user_id');
		$randstr = $this->input->get_post('randstr');

		if ($randstr != 'd5b9b9baf1dc036872065015cde2a4e4') {
			return Util_helper::result(null, 'error input', 1);
		}

		$this->load->model('User_model', 'user');
		$user = $this->user->getById($user_id);

		if (empty($user)) {
			return Util_helper::result(null, 'no user', 1);
		}

		return Util_helper::result(array('open_id' => $user->wx_open_id));
	}

	public function getGrantInfoForWxapp($app_id, $code)
	{
		$wx_apps = $this->config->item('wx_apps');
		$app_secret = $wx_apps[$app_id]['wx_app_secret'];

		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';
		$url = sprintf($url, $app_id, $app_secret, $code);

		log_message('info', '微信获取用户信息：' . $url);
		$api_response = RequestUtil::api_request($url, null, 'get');
		log_message('info', '微信获取用户信息：' . $api_response);

		$api_response = json_decode($api_response, true);

		if (empty($api_response) || !isset($api_response['openid'])) {
			return false;
		}

		//$user_info = null;
		//if(!isset($api_response['errcode'])) {
		//    $user_info = $this->getGrantUserInfo($api_response['openid'], $api_response['session_key']);
		//}

		$user_info = [
			'nickname' => '',
			'openid' => $api_response['openid'],
			'gender' => '',
			'city' => '',
			'country' => '',
			'province' => '',
			'avatar' => '',
			'unionid' => isset($api_response['unionid']) ? $api_response['unionid'] : ''
		];

		log_message('info', '微信获取用户信息：' . json_encode($user_info));
		return $user_info;
	}
}
