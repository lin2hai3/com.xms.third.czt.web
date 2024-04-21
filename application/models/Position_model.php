<?php


class Position_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function get_row_by_id($id)
	{
		$this->db->where_in('map_id', $id);
		return $this->db->get('position')->row();
	}

	public function get_row_by_name($name)
	{
		$this->db->where_in('name', $name);
		return $this->db->get('position')->row();
	}

	public function get_rows($names)
	{
		$this->db->where_in('name', $names);
		$rows = $this->db->get('position')->result();
		log_message('error', $this->db->last_query());
		return $rows;
	}
}
