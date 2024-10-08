<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2022/9/9
 * Time: 10:29
 */

class Ticket_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		// $this->load->database();
	}

	public function fetch($id, $admin_id = 0)
	{
		$this->db->where('id', $id);
		$row = $this->db->get('ticket')->row();

		if (empty($row)) {
			$ticket = array(
				'id' => $id,
				'status' => 1,
				'pay_channel' => 'fuiou',
				'created_by' => $admin_id,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_by' => $admin_id,
				'updated_at' => date('Y-m-d H:i:s'),
			);
			$result = $this->db->insert('ticket', $ticket);

			$this->db->where('id', $id);
			$row = $this->db->get('ticket')->row();
		}

		return $row;
	}

	public function setPayChannel($id, $pay_channel, $weixin_id)
	{
		$this->db->set('pay_channel', $pay_channel);
		$this->db->set('updated_by', $weixin_id);
		$this->db->where('id', $id);
		$this->db->update('ticket');
	}
}
