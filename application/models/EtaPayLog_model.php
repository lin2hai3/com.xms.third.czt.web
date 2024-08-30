<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/13
 * Time: 19:32
 */

class EtaPayLog_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function create($pay_log)
	{
		$result = $this->db->insert('eta_pay_log', $pay_log);
		return $result;
	}

	public function get($no)
	{
		$this->db->where('trade_no', $no);
		return $this->db->get('eta_pay_log')->row();
	}

	public function get_last($order_sn)
	{
		$this->db->where('order_sn', $order_sn);
		$this->db->order_by('id', 'desc');
		return $this->db->get('eta_pay_log')->row();
	}

	public function update_status($trade_no, $out_trade_no)
	{
		$this->db->set('status', 2);
		$this->db->set('out_trade_no', $out_trade_no);
		$this->db->where('trade_no', $trade_no);
		$this->db->update('eta_pay_log');
	}

	public function update_refund_no($trade_no, $refund_no, $refund_amount)
	{
		$this->db->set('refund_no', $refund_no);
		$this->db->set('refund_amount', $refund_amount);
		$this->db->update('eta_pay_log');
	}
}
