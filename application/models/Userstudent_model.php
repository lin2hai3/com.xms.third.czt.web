<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/13
 * Time: 19:32
 */

class Userstudent_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		$this->load->database();
	}

	public function create($user_student)
	{
		$result = $this->db->insert('user_student', $user_student);
		return $result;
	}

	public function get($user_student)
	{
		$this->db->where('user_id', $user_student['user_id']);
		$this->db->where('class', $user_student['class']);
		$this->db->where('seat', $user_student['seat']);
		$this->db->where('name', $user_student['name']);
		$this->db->where('status', 1);

		return $this->db->get('user_student')->result();
	}

	public function getById($user_id, $id)
	{
		$this->db->where('id', $id);
		$this->db->where('user_id', $user_id);
		$this->db->where('status', 1);

		return $this->db->get('user_student')->result();
	}

	public function getAll($user_id, $activity_no)
	{
		$this->db->where('user_id', $user_id);
		$this->db->where('activity_no', $activity_no);
		$this->db->where('status', 1);

		return $this->db->get('user_student')->result();
	}

	public function export()
	{
//		$this->db->select(array('class', 'seat', 'name'));
//		$this->db->where('status', 1);
//		$this->db->group_by(array('class', 'seat', 'name'));
//
//		return $this->db->get('user_student')->result();

		return $this->db->get('v_user_student')->result();
	}

	public function result()
	{
		return $this->db->get('v_enroll_record')->result();
	}

	public function destroy($user_id, $id)
	{
		$this->db->from('user_student');
		$this->db->set('status', 9);
		$this->db->where('user_id', $user_id);
		$this->db->where('id', $id);
		$this->db->where('status', 1);
		$this->db->update();
	}
}
