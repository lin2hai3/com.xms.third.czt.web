<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once 'application/libraries/RequestUtil.php';

class Admin extends CI_Controller
{

	public function login()
	{
		$html = <<<EOD
<style>
body,html {
background-color: #f2f2f2;
}

.input {
    width: 100%;
    margin: 0 auto;
    height: 40px;
    margin-top: 10px;
    border-radius: 4px;
    padding: 6px 12px;
    font-size: 13px;
    color: #555555;
    background-color: #ffffff;
    background-image: none;
    border: 1px solid #e4e4e4;
    outline: none
}

.input:focus {
    border-color: rgba(104, 184, 40, 0.5);
}
.button {
    width: 100%;
	background-color: cornflowerblue;;
    color: #ffffff;
    padding: 7px;
    border: none;
}
</style>

<div style="height: 100%">
	<form action="/web/admin/doLogin" style="width: 300px;background-color: white;padding: 20px;margin: 200px auto">
		<div style="padding: 5px"><input class="input" name="username" placeholder="账号"></div>
		<div style="padding: 5px"><input class="input" name="password" placeholder="密码"></div>
		<div style="padding: 5px"><button class="button">用户登录</button></div>
	</form>
</div>

EOD;

		echo $html;
	}

	public function doLogin() {
		$username = $this->input->get_post('username');
		$password = $this->input->get_post('password');


		$this->load->model('Admin_model', 'admin');
		$admin = $this->admin->getAdmin(array(
			'username' => $username,
			'password' => $password
		));

		if (empty($admin)) {
			echo '登录失败';
		} else {
			$this->session->set_userdata(array(
				'admin_id' => $admin->id
			));

			header("Location: /index.php/web/enroll/index");
			echo '登录成功';
		}
	}

	public function logout()
	{
		$this->session->unset_userdata('admin_id');

		echo '退出登录成功';
	}
}
