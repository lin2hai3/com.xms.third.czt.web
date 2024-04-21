<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/2/2
 * Time: 9:31
 */

class Paper_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function create($paper)
	{
		$result = $this->db->insert('paper', $paper);
		return $result;
	}

	public function get($no)
	{
		$this->db->where('id', $no);
		return $this->db->get('paper')->result();
	}
}
