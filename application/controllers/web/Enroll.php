<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Enroll extends CI_Controller
{
	public function __construct()
	{

		return parent::__construct();
	}

	public function index()
	{

		Util_helper::isLogin();

		$class = $this->input->get_post('class');
		$keyword = $this->input->get_post('keyword');
		$activity_item_no = $this->input->get_post('activity_item_no');
		$no = $this->input->get_post('no');

		$this->load->model('Enroll_model', 'enroll');
		$this->load->model('Activity_model', 'act');

		if (empty($no)) {
			$no = 1004;
		}

		$class_list = $this->act->getClassList();
		$activity_items = $this->act->items($no, 1);
		$_activity_items = array();

//		if (empty($class) && $keyword == '' && empty($activity_item_no)) {
//			$class = $class_list[0];
//		}

		$html = '<style>td {padding: 5px}</style><form><input type="hidden" name="no" value="' . $no . '">班级：<select name="class"><option value="">全部班级</option>';

		foreach ($class_list as $item) {
			$html .= '<option value="' . $item . '" ' . ($class == $item ? 'selected' : '' ) . '>' . $item . '</option>';
		}

		$html .= '</select>活动：<select name="activity_item_no"><option value="">全部课程</option>';

		foreach ($activity_items as $item) {
			$html .= '<option value="' . $item->no . '" ' . ($activity_item_no == $item->no ? 'selected' : '' ) . '>' . $item->name . '</option>';
			$_activity_items[$item->no] = $item;
		}

		$html .= '</select>关键词：<input name="keyword" placeholder="" value="' . $keyword . '"><button>搜索</button></form>';

		$records = $this->enroll->getRecords(array(
			'activity_no' => $no,
			'class' => $class,
			'item_no' => $activity_item_no,
			'name' => $keyword,
			'status' => 1
		));

		$records = json_decode(json_encode($records), true);
		$sort = array_column($records, 'item_no');
		$sort2 = array_column($records, 'class');
		array_multisort($sort, SORT_ASC, $sort2, SORT_ASC, $records);

		$html .= '<div style="margin-bottom: 6px;">合计：' . count($records) . '人<table border="1" style="margin-top: 10px; border-collapse:collapse;border: solid 1px #000000;"><tr><td>活动名称</td><td>班级</td><td>名称</td></tr>';

		foreach ($records as $record) {
			$activity_name = empty($_activity_items[$record['item_no']]) ? '' : $_activity_items[$record['item_no']]->name;
			$html .= '<tr><td>' . $activity_name . '</td><td>' . $record['class'] . '</td><td>' . $record['name'] . '</td></tr>';
		}

		$html .= '</table></div>';

		echo $html;
	}
}
