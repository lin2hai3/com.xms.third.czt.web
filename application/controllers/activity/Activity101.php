<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Activity101 extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		// echo 'activity.index';
		// $this->load->view('welcome_message');

		// $this->load->model('Paper_model', 'paper');

		// $res = $this->paper->get(2);
		// var_dump($res);

		// $paper = array('name' => '123', 'type' => '11');
		// $res = $this->paper->create($paper);
		// var_dump($res);

		$no = $this->input->get_post('no');

		if (empty($no)) {
			$no = '12f815761ed2f3469a28da2ee46adc2a';
		}

		$this->load->model('Activity_model', 'act');
		$activity = $this->act->get($no);

		if (empty($activity)) {
			return Util_helper::result(null, '活动不存在', -1);
		}

		$prizes = $this->act->prizes($activity->id);

		$_prizes = array();
		foreach ($prizes as $prize) {
			$_prizes[] = array(
				'id' => $prize->prize_id,
				'name' => $prize->name,
				'status' => $prize->status,
				'extends' => json_decode($prize->extends, true),
				'desc' => $prize->desc,
			);
		}

		$_activity = array(
			'no' => $activity->no,
			'name' => $activity->name,
			'status' => $activity->status,
			'start_time' => $activity->start_time,
			'end_time' => $activity->end_time,
			'desc' => $activity->desc,
			'prizes' => $_prizes,
		);

		return util_helper::result($_activity);
	}

	public function draw()
	{
		$member_id = $this->input->post('mid');
		$no = $this->input->post('no');

		if (empty($member_id) || empty($no)) {
			return Util_helper::result(null, '参数错误', -1);
		}

		$this->load->model('Activity_model', 'act');
		$activity = $this->act->get($no);

		if (empty($activity)) {
			return Util_helper::result(null, '活动不存在', -1);
		}

		$retry = 5;

		do {
			$log_id = $this->act->draw($member_id, $activity);
		} while ($retry-- > 0 && empty($log_id));

		if (empty($log_id)) {
			return Util_helper::result(null, '活动结束', -1);
		}

		$log = $this->act->getLog($log_id);

		$prizes = $this->act->prizes($activity->id);

		for($idx = 0; $idx < count($prizes); $idx++) {
			if($prizes[$idx]->prize_id == $log->prize_id) {
				$log->index = $idx;
				break;
			}
		}

		return util_helper::result($log);
	}
}
