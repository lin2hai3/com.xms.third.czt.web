<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/13
 * Time: 19:32
 */

class User_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function create($user)
	{
		$result = $this->db->insert('user', $user);
		return $result;
	}

	public function getByOpenId($open_id)
	{
		$this->db->where('wx_open_id', $open_id);
		return $this->db->get('user')->row();
	}

	public function getById($user_id)
	{
		$this->db->where('id', $user_id);
		return $this->db->get('user')->row();
	}
}
