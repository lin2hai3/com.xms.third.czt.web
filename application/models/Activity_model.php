<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/2/2
 * Time: 9:31
 */

class Activity_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();

		// $this->load->database();
	}

	public function getClassList()
	{
		$rows = $this->db->from('activity_student')->select('class')->distinct()->get()->result();
		$_rows = array();

		foreach ($rows as $row) {
			array_push($_rows, $row->class);
		}

		return $_rows;
	}

	public function create($paper)
	{
		$result = $this->db->insert('activity', $paper);
		return $result;
	}

	public function get($no)
	{
		$this->db->where('no', $no);
		return $this->db->get('activity')->row();
	}

	public function get_item($activity_no, $item_no)
	{
		$this->db->where('activity_no', $activity_no);
		$this->db->where('no', $item_no);
		$this->db->where('status', 1);
		return $this->db->get('activity_item')->row();
	}

	public function items($activity_no, $status = 0)
	{
		if (!empty($status)) {
			$this->db->where('status', $status);
		}

		$this->db->where('activity_no', $activity_no);
		return $this->db->get('activity_item')->result();
	}

	public function prizes($activity_id)
	{
		$this->db->from('activity_prize');
		$this->db->join('prize', 'activity_prize.prize_id = prize.id');
		$this->db->where('activity_id', $activity_id);
		$this->db->order_by('sort');
		return $this->db->get()->result();
	}

	public function draw($member_id, $activity)
	{
		$res = false;
		$this->db->trans_start();

		$this->db->where('status', 1);
		$this->db->where('activity_id', $activity->id);
		$log = $this->db->get('activity_log')->row();

		if (empty($log) && $activity->max_count >= $activity->count) {

			$prizes = $this->prizes($activity->id);
			$box = array();

			for($idx = 0; $idx < count($prizes); $idx++) {
				for($jdx = 0; $jdx < $prizes[$idx]->count; $jdx++) {
					array_push($box, $prizes[$idx]->prize_id);
				}
			}

			shuffle($box);
			shuffle($box);
			shuffle($box);

			for($idx = 0; $idx < $activity->append_count; $idx++) {
				$this->db->insert('activity_log', array(
					'sn' => Util_helper::getSerialNo(),
					'activity_id' => $activity->id,
					'prize_id' => $box[$idx],
					'status' => 1,
					'member_id' => 0,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				));
			}

			$this->db->where('id', $activity->id);
			$this->db->update('activity', array(
				'count' => $activity->count + $activity->append_count,
				'updated_at' => date('Y-m-d H:i:s'),
			), array(
				'id' => $activity->id,
			));
		}

		$this->db->where('status', 1);
		$this->db->where('activity_id', $activity->id);
		$log = $this->db->get('activity_log')->row();

		if (!empty($log)) {
			$this->db->where('status', 1);
			$this->db->where('id', $log->id);
			$res = $this->db->update('activity_log', array(
				'status' => 2,
				'member_id' => $member_id,
				'updated_at' => date('Y-m-d H:i:s'),
			));

			$this->db->where('id', $activity->id);
			$this->db->update('activity', array(
				'used_count' => $activity->used_count + 1,
				'updated_at' => date('Y-m-d H:i:s'),
			), array(
				'id' => $activity->id,
			));
		}

		$this->db->trans_complete();

		if ($res) {
			return $log->id;
		}
		else {
			return 0;
		}
	}

	public function getLog($id)
	{
		$this->db->where('id', $id);
		return $this->db->get('activity_log')->row();
	}
}
