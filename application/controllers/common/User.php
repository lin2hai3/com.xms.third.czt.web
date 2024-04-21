<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Edited by Jason 2021-11-03
 * Class User
 */
class User extends CI_Controller
{

	private $access_token = '';
	private $access_token_expired = 0;

	public function index()
	{
		echo 'common.user.index';
		// $this->load->view('welcome_message');
	}

	public function uniLogin()
	{
		$code = $this->input->get('code');
		$app_id = $this->input->get('app_id');

		$wx_config['app_id'] = $this->config->item('wx_app_id');
		$wx_config['app_secret'] = $this->config->item('wx_app_secret');

		$wx_apps = $this->config->item('wx_apps');

		$wx_config['app_id'] = isset($wx_apps[$app_id]) ? $wx_apps[$app_id]['wx_app_id'] : '';
		$wx_config['app_secret'] = isset($wx_apps[$app_id]) ? $wx_apps[$app_id]['wx_app_secret'] : '';

		$merchant_id = $this->config->item('merchant_id');
		$shop_id = $this->config->item('shop_id');

//		$data = array(
//			'method' => 'wxapp.init.get',
//			'code' => $code,
//		);
//
//		$init_result = Client_helper::load($data);
//		$init_result = json_decode($init_result, true);
//
//		$data = array(
//			'init_result' => $init_result['result'],
//		);
//
//		return Util_helper::result($data);


		// $access_token = $this->getAccessToken($wx_config['app_id']);

		$weixin_profile = $this->getGrantInfoForWxapp($wx_config['app_id'], $wx_config['app_secret'], $code);

		if (empty($weixin_profile)) {
			return Util_helper::result(null,'微信授权失败', 1);
		}

		$data = array(
			'method' => 'weixin.openid.check',
            'appid' => $wx_config['app_id'],
            'openid' => $weixin_profile['openid'],
			'session_key' => $weixin_profile['session_key'],
		);

		$weixin_info = Client_helper::load($data);
		$weixin_info = json_decode($weixin_info, true);

		if ($weixin_info['code'] != 0) {
			return Util_helper::result($data, $weixin_info['msg'], $weixin_info['code']);
		}
		$weixin_info = $weixin_info['result'];

		$data = array(
			'method' => 'weixin.sid.encode',
			'weixin_id' => $weixin_info['id'],
		);

		$result_info = Client_helper::load($data);
		$result_info = json_decode($result_info, true);


		$weixin_info['sid'] = $result_info['result'];
		$weixin_info['openid'] = $weixin_profile['openid'];


		$data = array(
			'method' => 'weixin.member.id.get',
			'weixin_id' => $weixin_info['id'],
		);

		$member_info = Client_helper::load($data);
		$member_info = json_decode($member_info, true);

		$employee_info = array();

		if ($member_info['code'] == 0) {

			$member_id = $member_info['result']['member_id'];

			$data = array(
				'method' => 'members.member.get',
				'id' => $member_id,
				'get_extend' => 1,
				'fields' => 'id,ctime,mtime,flag,username,nickname,pinyin,mobile,email,inviter_id,enabled,removed',
			);

			$member_info = Client_helper::load($data);
			$member_info = json_decode($member_info, true);
			$_member_info = $member_info;
			if ($member_info['code'] == 0) {
				$member_info = $member_info['result'];
			} else {
				// if ($weixin_info['id'] == 1) {
				// 	$member_info = array('id' => 1240, 'flag' => 'A');
				// }
				// else {
				// 	$member_info = array('id' => 0, 'flag' => 'C');
				// }
				$member_info = array('id' => 0, 'flag' => 'C');
			}

			$data = array(
				'method' => 'shops.employees.get',
				'shop_id' => $shop_id,
				'member_id' => $member_id,
				'fields' => '*',
			);

			$employee_info = Client_helper::load($data);
			$employee_info = json_decode($employee_info, true);

			if ($employee_info['result']['total_results'] > 0 && isset($employee_info['result']['rows'][$member_id])) {
				if ($employee_info['result']['rows'][$member_id]['is_cleaner'] != 1) {
					$member_info['flag'] = 'A';
				}
			}
		}
		else {
			$member_info = array('id' => 0, 'flag' => 'C');
		}

//		if ($weixin_info['id'] == 1) {
//			$member_info['flag'] = 'A';
//			$member_info['merchant_id'] = 1;
//			$member_info['shop_id'] = 1;
//		}

		$member_info['merchant_id'] = $merchant_id;
		$member_info['shop_id'] = $shop_id;

		if (!isset($member_info['money'])) {
			$member_info['money'] = 0;
		}
		$member_info['format_money'] = sprintf('%.2lf', $member_info['money'] / 100);





		$data = array(
			'weixin_profile' => $weixin_profile,
			'weixin_info' => $weixin_info,
			'member_info' => $member_info,
			// '_member_info' => $_member_info,
			'$employee_info' => $employee_info,
		);

		return Util_helper::result($data);
	}

	public function code2Session()
	{
		// 微信小程序配置-白马荟
		$wx_config['app_id'] = $this->config->item('wx_app_id');
		$wx_config['app_secret'] = $this->config->item('wx_app_secret');

		// $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		// $this->cache->save('test_cache', 'I am testing');
		// echo $this->cache->file->get('test_cache');

		$code = $this->input->get('code');

		// $access_token = $this->getAccessToken($wx_config['app_id']);

		$user_info = $this->getGrantInfoForWxapp($wx_config['app_id'], $wx_config['app_secret'], $code);

		return Util_helper::result($user_info);
	}

	private function getAccessToken($app_id)
	{
		$key = 'wechat:' . $app_id . ':config';

		if ($this->access_token_expired > time() && !empty($this->access_token)) {
			return $this->access_token;
		}

		$this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
		$config = $this->cache->file->get($key);

		// logger('缓存对象:' . json_encode($config));
		if (empty($config) || $config['expired'] < time()) {
			// access_token不存在或已过期
			// 更新access_token
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
			$url = sprintf($url, $this->app_id, $this->app_secret);

			$api_response = Request_helper::request($url, null, 'get');
			// logger('刷新AccessToken:' . $api_response);

			$api_response = json_decode($api_response, true);

			if (!isset($api_response['errcode'])) {
				$config = array(
					'app_id' => $app_id,
					'access_token' => $api_response['access_token'],
					'expired' => $api_response['expires_in'] + time()
				);

				// ttl 单位是秒，不是分钟
				// Cache::put($key, $config, $api_response['expires_in'] / 60);
				// Cache::put($key, $config, $api_response['expires_in']);

				$this->cache->save($key, $config, $api_response['expires_in']);
			}
		}

		 if (!empty($config)) {
			  $this->access_token = $config['access_token'];
			  $this->access_token_expired = $config['expired'];
		 }

		return $config['access_token'];
	}

	private function getGrantInfoForWxapp($app_id, $app_secret, $code)
	{
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';
		$url = sprintf($url, $app_id, $app_secret, $code);

		// LogUtil::api('微信获取用户信息：' . $url);
		$api_response = Request_helper::request($url, null, 'get');
		// LogUtil::api('微信获取用户信息：' . $api_response);

		$api_response = json_decode($api_response, true);

		if (empty($api_response) || !isset($api_response['openid'])) {
			return false;
		}

		//$user_info = null;
		//if(!isset($api_response['errcode'])) {
		//    $user_info = $this->getGrantUserInfo($api_response['openid'], $api_response['session_key']);
		//}

		$user_info = array(
			'nickname' => '',
			'openid' => $api_response['openid'],
			'gender' => '',
			'city' => '',
			'country' => '',
			'province' => '',
			'avatar' => '',
			'unionid' => isset($api_response['unionid']) ? $api_response['unionid'] : '',
			'session_key' => $api_response['session_key'],
		);

		// LogUtil::api('微信获取用户信息：' . json_encode($user_info));
		return $user_info;
	}

	public function init()
	{
		$code = $this->input->get('code');

		$data = array(
			'method' => 'wxapp.init.get',
			'code' => $code,
		);

		$result = Client_helper::load($data);

		return Util_helper::result($result['result']);
	}
}
