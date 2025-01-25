<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/13
 * Time: 19:32
 */

class Memo_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function create($memo)
	{
		$result = $this->db->insert('memo', $memo);
		return $result;
	}

	public function getAll()
	{
		$this->db->where('status', 1);
		return $this->db->get('memo')->result();
	}

	public function update_status($id, $status, $member_id)
	{
		$this->db->set('status', $status);
		$this->db->set('updated_by', $member_id);
		$this->db->set('updated_at', date('Y-m-d H:i:s'));
		$this->db->where('id', $id);
		$this->db->update('memo');
	}
}
