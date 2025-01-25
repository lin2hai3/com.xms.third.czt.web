<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/13
 * Time: 19:32
 */

class Ticketinviterpaylog_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function create($pay_log)
	{
		$result = $this->db->insert('ticket_inviter_pay_log', $pay_log);
		return $result;
	}
}
