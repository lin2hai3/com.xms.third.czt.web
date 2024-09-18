<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student extends CI_Controller
{

	public function index()
	{
		$user_id = $this->input->get_post('user_id');
		$activity_no = $this->input->get_post('activity_no');

		if (empty($activity_no)) {
			$activity_no = '1004';
		}

		$this->load->model('Userstudent_model', 'user_student');

		$students = $this->user_student->getAll($user_id, $activity_no);

		// echo $this->db->last_query();

		return Util_helper::result($students);
	}

	public function create()
	{
		$activity_no = $this->input->get_post('activity_no');

		if (empty($activity_no)) {
			$activity_no = '1004';
		}

		$this->load->model('Student_model', 'student');

		$class_list = $this->student->getClass($activity_no);

		return Util_helper::result($class_list);
	}

	public function store()
	{
		$user_id = $this->input->get_post('user_id');
		$class = $this->input->get_post('class');
		$name = $this->input->get_post('name');
		$seat = $this->input->get_post('seat');
		$parent_name = $this->input->get_post('parent_name');
		$parent_phone = $this->input->get_post('parent_phone');

		$activity_no = $this->input->get_post('activity_no');

		if (empty($activity_no)) {
			$activity_no = '1004';
		}

		if (empty($user_id)) {
			return Util_helper::result(null, '用户不能为空', 1);
		}

		if (empty($class)) {
			return Util_helper::result(null, '班级不能为空', 1);
		}

		if (empty($name)) {
			return Util_helper::result(null, '学生姓名不能为空', 1);
		}

		if (empty($seat)) {
			return Util_helper::result(null, '学生座号不能为空', 1);
		}

		// $student = $this->db->

		if (empty($parent_name)) {
			// $parent_name = '';
			return Util_helper::result(null, '父母姓名不能为空', 1);
		}

		if (empty($parent_phone)) {
			// $parent_phone = '';
			return Util_helper::result(null, '父母电话不能为空', 1);
		}

		$this->load->model('Student_model', 'student');
		$student = $this->student->get($class, $seat, $name);

		if (empty($student)) {
			return Util_helper::result(null, '查不到学生，请检查班级-座号-姓名', 1);
		}

		$user_student = array(
			'activity_no' => $activity_no,
			'user_id' => $user_id,
			'student_id' => $student->id,
			'class' => $student->class,
			'seat' => $student->seat,
			'sex' => $student->sex,
			'name' => $student->name,
			'parent_name' => $parent_name,
			'parent_phone' => $parent_phone,
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		);

		$this->load->model('Userstudent_model', 'user_student');

		$student = $this->user_student->get($user_student);

		// echo $this->db->last_query();

		if (empty($student)) {
			$student = $this->user_student->create($user_student);
			return Util_helper::result(null, '绑定成功');
		}
		else {
			return Util_helper::result(null, '绑定失败-重复绑定', 1);
		}

	}

	public function destroy()
	{
		$user_id = $this->input->get_post('user_id');
		$id = $this->input->get_post('id');

		if (empty($user_id) || empty($id)) {
			return Util_helper::result(null, '用户不能为空', 1);
		}

		$this->load->model('Userstudent_model', 'user_student');

		$student = $this->user_student->getById($user_id, $id);

		if (empty($student)) {
			return Util_helper::result(null, '记录不能为空', 1);
		}

		$student = $this->user_student->destroy($user_id, $id);

		return Util_helper::result(null, '删除成功');
	}

	public function export()
	{
		$token = $this->input->get_post('token');

		if ($token != 'b59651193460606e4dda8f6fbc723671' . date('Hi')) {
			die('404 not found');
		}

		$this->load->model('Userstudent_model', 'user_student');

		$students = $this->user_student->export();


		echo '<table>';

		foreach ($students as $student) {
			echo '<tr>';

			echo '<td>' . $student->class . '</td>';
			echo '<td>' . $student->seat . '</td>';
			echo '<td>' . $student->name . '</td>';
			echo '<td>' . $student->count . '</td>';

			echo '</tr>';
		}

		echo '</table>';
	}

	public function result()
	{
		$token = $this->input->get_post('token');

		if ($token != 'b59651193460606e4dda8f6fbc723671' . date('Hi')) {
			die('404 not found');
		}

		$this->load->model('Userstudent_model', 'user_student');

		$rows = $this->user_student->result();


		echo '<table>';

		echo '<tr>';

		echo '<td>班名</td>';
		echo '<td>班级</td>';
		echo '<td>座号</td>';
		echo '<td>姓名</td>';
		echo '<td>家长</td>';
		echo '<td>电话</td>';

		echo '</tr>';

		foreach ($rows as $row) {
			echo '<tr>';

			echo '<td>' . $row->activity_item_name . '</td>';
			echo '<td>' . $row->class . '</td>';
			echo '<td>' . $row->seat . '</td>';
			echo '<td>' . $row->student_name . '</td>';
			echo '<td>' . $row->parent_name . '</td>';
			echo '<td>' . $row->parent_phone . '</td>';

			echo '</tr>';
		}

		echo '</table>';
	}
}
