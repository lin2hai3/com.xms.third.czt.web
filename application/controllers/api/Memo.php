<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Memo extends CI_Controller
{
	public function index()
	{
		$this->load->model('Memo_model', 'memo');
		$rows = $this->memo->getAll();

		Util_helper::result(array('rows' => $rows));
	}

	public function create()
	{

	}

	public function store()
	{
		$sid = $this->input->get_post('sid');
		$memo = $this->input->get_post('memo');

		if (empty($sid) || empty($memo)) {
			return Util_helper::result(null, '参数不能为空', 1);
		}

		$member_id = $this->getMemberIdBySid($sid);

		$data = array(
			'sid' => $sid,
			'memo' => $memo,
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'created_by' => $member_id,
			'updated_at' => date('Y-m-d H:i:s'),
			'updated_by' => $member_id,
		);

		$this->load->model('Memo_model', 'memo');
		$rows = $this->memo->create($data);

		return Util_helper::result(null, '保存成功');
	}

	public function status()
	{
		$sid = $this->input->get_post('sid');
		$id = $this->input->get_post('id');
		$status = $this->input->get_post('status');

		if (empty($sid) || empty($id)) {
			return Util_helper::result(null, '参数不能为空', 1);
		}

		$member_id = $this->getMemberIdBySid($sid);

		$this->load->model('Memo_model', 'memo');
		$this->memo->update_status($id, $status, $member_id);

		return Util_helper::result(null, '状态修改成功', 0);
	}

	public function getMemberIdBySid($sid)
	{
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

		return $result['result']['member_id'];
	}
}
