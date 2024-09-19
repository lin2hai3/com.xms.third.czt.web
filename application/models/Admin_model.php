<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2022/9/8
 * Time: 9:35
 */

class Admin_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		// $this->load->database();
	}

	public function getAdmin($admin)
	{
		$this->db->where('username', $admin['username']);
		$this->db->where('password', $admin['password']);
		$this->db->where('status', 1);
		return $this->db->get('admin')->row();
	}

}
