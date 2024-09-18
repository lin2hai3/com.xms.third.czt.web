<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2022/9/8
 * Time: 9:30
 */
class Enroll extends CI_Controller
{
	// 项目详情页，是否显示库存
	private $item_detail_show_inventory = true;

	// 报名详情页面，是否显示取消按钮
	private $record_detail_show_cancel = true;


	public function index()
	{
		echo '<select><option>101</option></select>';

		echo '<table><tr></tr></table>';
	}

	public function show()
	{
		$no = $this->input->get_post('no');
		$sex = $this->input->get_post('sex');

		$this->load->model('Activity_model', 'act');
		$activity = $this->act->get($no);

		if (empty($activity)) {
			return util_helper::result(null, '活动不存在', -1);
		}

		$items = $this->act->items($no, 1);

		$_items = array();
		if (!empty($sex)) {
			foreach ($items as $item) {
				if ($item->sex == $sex) {
					array_push($_items, $item);
				}
			}
		} else {
			$_items = $items;
		}

		$activity->items = $_items;

		return util_helper::result($activity);
	}

	public function show_item()
	{

		$user_id = $this->input->get_post('user_id');
		$activity_no = $this->input->get_post('activity_no');
		$item_no = $this->input->get_post('item_no');

		$this->load->model('Activity_model', 'act');
		$activity = $this->act->get($activity_no);

		if (empty($activity_no) || empty($item_no)) {
			return util_helper::result(null, '参数不能为空', '-1');
		}

		if (empty($activity) || $activity->status != 1) {
			return util_helper::result(null, '活动已经下架，无法报名', '-1');
		}

		$activity_item = $this->act->get_item($activity_no, $item_no);
		if (empty($activity_item) || $activity_item->status != 1) {
			return util_helper::result(null, '报名项目已经下架，无法报名', '-1');
		}

		$this->load->model('Activity_model', 'act');

		$activity_item->class_list = $this->act->getClassList();

		$activity_item->show_inventory = $this->item_detail_show_inventory;

//		$activity_item->student_list = array(array(
//			'student_id' => 1,
//			'class' => '101',
//			'name' => '林海'
//		), array(
//			'student_id' => 2,
//			'class' => '102',
//			'name' => '小涵'
//		));

		$this->load->model('Userstudent_model', 'user_student');

		$students = $this->user_student->getAll($user_id);
		$activity_item->student_list = $students;

		$activity_item->inventory_mask = 10;

		if ($activity_item->inventory > $activity_item->inventory_mask) {
			$activity_item->inventory = '可报名';
		}

		return util_helper::result($activity_item);
	}

	public function store_record()
	{
		$admins = array('gxQqZOW9', 'q2MbqbOR');

		$sid = $this->input->get_post('sid');
		$activity_no = $this->input->get_post('activity_no');
		$item_no = $this->input->get_post('item_no');
		$class = $this->input->get_post('class');
		$name = $this->input->get_post('name');
		$parent_name = $this->input->get_post('parent_name');
		$parent_phone = $this->input->get_post('parent_phone');

		if (empty($sid) || empty($activity_no) || empty($item_no)) {
			return util_helper::result(null, '参数不能为空', '-1');
		}

		if (empty($class)) {
			return util_helper::result(null, '班级不能为空', '-1');
		}

		if (empty($name)) {
			return util_helper::result(null, '学生姓名不能为空', '-1');
		}

		if (empty($parent_name)) {
			return util_helper::result(null, '家长姓名不能为空', '-1');
		}

		if (empty($parent_phone)) {
			return util_helper::result(null, '家长手机不能为空', '-1');
		}

		$record = array(
			'sid' => $sid,
			'activity_no' => $activity_no,
			'item_no' => $item_no,
			'class' => $class,
			'name' => $name,
			'parent_name' => $parent_name,
			'parent_phone' => $parent_phone,
			'status' => 1,
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s'),
		);

		$this->load->model('Activity_model', 'act');
		$activity = $this->act->get($activity_no);

		if (empty($activity) || $activity->status != 1) {
			return util_helper::result(null, '活动已经下架，无法报名', '-1');
		}

		$activity_item = $this->act->get_item($activity_no, $item_no);
		if (empty($activity_item) || $activity_item->status != 1) {
			return util_helper::result(null, '报名项目已经下架，无法报名', '-1');
		}

		if ($activity_item->inventory < 1) {
			return util_helper::result(null, '报名项已报满，无法报名', '-1');
		}

		if (!in_array($sid, $admins)) {
			$start_time = strtotime($activity->start_time);
			$end_time = strtotime($activity->end_time);

			if ($start_time > time()) {
				return util_helper::result(null, '活动未开始，敬请留意开始时间', '-1');
			}

			if ($end_time < time()) {
				return util_helper::result(null, '活动已结束，谢谢关注', '-1');
			}
		}

		$this->load->model('Enroll_model', 'enroll');
		// $res = $this->enroll->checkClassAndName($record);

		$this->load->model('Student_model', 'student');
		$res = $this->student->checkClassAndName($record);

		if (empty($res)) {
			return util_helper::result(null, $record['class'] . '-' . $record['name'] . '不存在，请检查后再报名', '-1');
		}

		$res = $this->enroll->checkRecord($record);

		if (!empty($res)) {
			return util_helper::result(null, '一个学生只能报名一个项目', '-1');
		}

		$res = $this->enroll->storeRecord($record);

		if (empty($res)) {
			return util_helper::result(null, '该项目已经报满，请选择其他项目报名', '-1');
		}

		return util_helper::result(null, '报名成功');
	}

	public function list_records()
	{
		$sid = $this->input->get_post('sid');
		$activity_no = $this->input->get_post('activity_no');

		if (empty($activity_no)) {
			$activity_no = '1004';
		}

		if (empty($sid)) {
			return util_helper::result(null, '参数不能为空', '-1');
		}

		$this->load->model('Enroll_model', 'enroll');
		$result = $this->enroll->listRecords($sid, $activity_no);

		foreach ($result as &$row) {
			$this->load->model('Activity_model', 'act');
			$activity_item = $this->act->get_item($row->activity_no, $row->item_no);
			if (!empty($activity_item)) {
				$row->item_name = $activity_item->name;
			} else {
				$row->item_name = '';
			}

			$row->show_cancel = $this->record_detail_show_cancel;
		}

		return util_helper::result($result);
	}

	public function show_record()
	{
		$sid = $this->input->get_post('sid');
		$id = $this->input->get_post('id');

		if (empty($sid) || empty($id)) {
			return util_helper::result(null, '参数不能为空', '-1');
		}


		$this->load->model('Enroll_model', 'enroll');
		$result = $this->enroll->showRecord($sid, $id);

		$this->load->model('Activity_model', 'act');
		$activity_item = $this->act->get_item($result->activity_no, $result->item_no);
		if (!empty($activity_item)) {
			$result->item_name = $activity_item->name;
		} else {
			$result->item_name = '';
		}

		$result->show_cancel = $this->record_detail_show_cancel;

		return util_helper::result($result);
	}

	public function cancel_record()
	{
		$sid = $this->input->get_post('sid');
		$id = $this->input->get_post('id');

		if (empty($sid) || empty($id)) {
			return util_helper::result(null, '参数不能为空', '-1');
		}

		$this->load->model('Enroll_model', 'enroll');
		$result = $this->enroll->cancelRecord($sid, $id);

		if ($result) {
			return util_helper::result(null, '取消成功');
		} else {
			return util_helper::result(null, '取消失败', -1);
		}
	}
}
