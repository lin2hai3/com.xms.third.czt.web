<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2022/9/8
 * Time: 9:35
 */

class Enroll_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		// $this->load->database();
	}

	public function checkClassAndName($record)
	{
		$this->db->where('activity_no', $record['activity_no']);
		$this->db->where('class', $record['class']);
		$this->db->where('name', $record['name']);
		$this->db->where('status', 1);
		return $this->db->get('activity_student')->row();
	}

	public function checkRecord($record)
	{
		$this->db->where('activity_no', $record['activity_no']);
		// $this->db->where('item_no', $record['item_no']);
		$this->db->where('class', $record['class']);
		$this->db->where('name', $record['name']);
		$this->db->where('status', 1);
		return $this->db->get('enroll_record')->row();
	}

	public function storeRecord($record)
	{
		$this->db->trans_begin();

		$item = $this->db->where('no', $record['item_no'])
			->get('activity_item')
			->row();

		$this->db->from('activity_item');
		$this->db->set('inventory', $item->inventory - 1);
		$this->db->set('user_count', $item->user_count + 1);
		$this->db->set('updated_at', date('Y-m-d H:i:s'));
		$this->db->where('no', $record['item_no']);
		$this->db->where('inventory > ', 0);
		$this->db->update();

		$result = false;
		if ($this->db->affected_rows() > 0) {
			$result = $this->db->insert('enroll_record', $record);
			$this->db->trans_commit();
		} else {
			$this->db->trans_rollback();
		}

		return $result;
	}

	public function cancelRecord($sid, $id)
	{
		$this->db->where('id', $id);
		$this->db->where('sid', $sid);
		$record = $this->db->get('enroll_record')->row();

		if (empty($record)) {
			return false;
		}

		$this->db->where('no', $record->item_no);
		$item = $this->db->get('activity_item')->row();

		$this->db->from('enroll_record');
		$this->db->set('status', 9);
		$this->db->where('sid', $sid);
		$this->db->where('id', $id);
		$this->db->where('status', 1);
		$this->db->update();

		if ($this->db->affected_rows() > 0) {

			$this->db->from('activity_item');
			$this->db->set('inventory', $item->inventory + 1);
			$this->db->set('user_count', $item->user_count - 1);
			$this->db->set('updated_at', date('Y-m-d H:i:s'));
			$this->db->where('no', $record->item_no);
			$this->db->where('inventory > ', 0);
			$this->db->update();

			return true;
		} else {
			return false;
		}
	}

	public function listRecords($sid, $activity_no)
	{
		$this->db->where('sid', $sid);
		$this->db->where('activity_no', $activity_no);
		$this->db->where('status', 1);
		return $this->db->get('enroll_record')->result();
	}

	public function showRecord($sid, $id)
	{
		$this->db->where('sid', $sid);
		$this->db->where('id', $id);
		$this->db->where('status', 1);
		return $this->db->get('enroll_record')->row();
	}
}
