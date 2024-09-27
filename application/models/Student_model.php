<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/13
 * Time: 19:32
 */

class Student_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function create($user)
	{
		$result = $this->db->insert('student', $user);
		return $result;
	}

	public function get($class, $seat, $name)
	{
		$this->db->where('class', $class);
		$this->db->where('seat', $seat);
		$this->db->where('name', $name);
		return $this->db->get('student')->row();
	}

	public function getClass($activity_no)
	{
		$this->db->select('class');
		$this->db->distinct();
		$this->db->order_by('class');
		$this->db->where('activity_no', $activity_no);
		$rows = $this->db->get('student')->result();

		$rows2 = array();

		foreach ($rows as $row) {
			$rows2[] = $row->class;
		}

		return $rows2;
	}

	public function getAll($activity_no)
	{
		$this->db->where('activity_no', $activity_no);
		$this->db->where('status', 1);
		return $this->db->get('student')->result();
	}

	public function checkClassAndName($record)
	{
		$this->db->where('activity_no', $record['activity_no']);
		$this->db->where('class', $record['class']);
		$this->db->where('name', $record['name']);
		$this->db->where('status', 1);
		return $this->db->get('student')->row();
	}
}
