<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/11/28
 * Time: 14:18
 */

require_once 'application/libraries/DataTable.php';
require_once 'application/libraries/DataColumn.php';
require_once 'application/libraries/DataItem.php';
require_once 'application/libraries/DataFilter.php';
require_once 'application/libraries/ColumnType.php';


class Control extends CI_Controller
{
	public function index()
	{
		$relate_id = $this->input->get_post('relate_id');

		$data = array(
			'room_id' => $relate_id,
			'method' => 'rooms.controls.get',
			'fields' => 'id,room_id,device_id,port,delay,checkin_status,checkout_status,label',
		);

		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);

		if (!isset($result['result']['rows'])) {
			$result['result']['rows'] = [];

			$return_data = array(
				'title' => '设备管理',
				'count' => $result['result']['total_results'],
				'rows' => array(),
				'table' => $this->getTable(),
			);

			return Util_helper::formatResult($return_data);
		}

		$data1 = array(
			'room_id' => $relate_id,
			'method' => 'rooms.controls.status.get',
		);
		$status_result = IRoomApp_helper::load($data1);
		$status_result = json_decode($status_result, true);

		$status_rows = array();
		foreach ($status_result['result'] as $row) {
			$status_rows[$row['device_id']] = $row;
		}

		$_rows = array();
		foreach ($result['result']['rows'] as $row) {
			$_row = array(
				'name' => $row['label'],
				'id' => $row['id'],
				'no' => $row['id']
			) + $row;

			if (isset($status_rows[$row['device_id']])) {
				$_row['status'] = $status_rows[$row['device_id']]['status'];
				$_row['device_online'] = $status_rows[$row['device_id']]['device_online'];
			}
			else {
				$_row['status'] = 0;
				$_row['device_online'] = 0;
			}

			if ($_row['device_online']) {
				// 在线
				if ($_row['status']) {
					// $_row['bgColor'] = '#7ecef4';
					$_row['bgColor'] = '#eb6100';
				}
				else {
					// $_row['bgColor'] = '#e5e5e5';
					$_row['bgColor'] = '#000000';
				}
			}
			else {
				// $_row['bgColor'] = '#f5af9a';
				// 离线
				$_row['bgColor'] = '#7f7f7f';
			}

			$_rows[] = $_row;
		}

		$return_data = array(
			'title' => '设备管理',
			'count' => $result['result']['total_results'],
			'rows' => $_rows,
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($return_data);
	}

	public function show()
	{
		$id = $this->input->get_post('id');

		$data['id'] = $id;
		$data['method'] = 'rooms.control.get';
		$data['fields'] = 'id,room_id,device_id,port,delay,checkin_status,checkout_status,label';

		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);

		$return_data = array(
			'title' => '设备详情',
			'row' => $result['result'],
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($return_data);
	}

	public function create()
	{
		$data = array(
			'title' => '新增设备',
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($data, 'create');
	}

	public function store()
	{
		$table = $this->getTable();

		$row = array();
		foreach ($table->columns as $column) {
			$row[$column->field] = $this->input->get_post($column->field);
		}

		$row['shop_id'] = $this->input->get_post('shop_id');
		$row['room_id'] = $this->input->get_post('relate_id');
		$row['checkin_status'] = $row['checkin_status'] ? 1 : 0;
		$row['checkout_status'] = $row['checkout_status'] ? 1 : 0;

		$row['method'] = 'rooms.control.insert';

		$result = IRoomApp_helper::load($row);
		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			return Util_helper::formatResult(null, '新增成功');
		}
		else {
			return Util_helper::formatResult(null, $result['msg'], 10003);
		}
	}

	public function edit()
	{
		$id = $this->input->get_post('id');

		$data['id'] = $id;
		$data['method'] = 'rooms.control.get';
		$data['fields'] = 'id,room_id,device_id,port,delay,checkin_status,checkout_status,label';

		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);

		$return_data = array(
			'title' => '编辑设备',
			'row' => $result['result'],
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($return_data);
	}

	public function update()
	{
		$id = $this->input->get_post('id');
		$row = $this->input->get_post('row');
		$row = json_decode($row, true);

		$row['method'] = 'rooms.control.update';
		$row['checkin_status'] = $row['checkin_status'] ? 1 : 0;
		$row['checkout_status'] = $row['checkout_status'] ? 1 : 0;


		$result = IRoomApp_helper::load($row);
		$result = json_decode($result, true);


		if ($result['code'] == 0) {
			return Util_helper::formatResult(null, '修改成功');
		}
		else {
			return Util_helper::formatResult(null, $result['msg'], 10003);
		}
	}

	public function turn()
	{
		$id = $this->input->get_post('id');
		$status = $this->input->get_post('status');

		$data['id'] = $id;
		$data['status'] = $status;
		$data['method'] = 'rooms.control.turn';

		$result = IRoomApp_helper::load($data);
		$result = json_decode($result, true);

//		$return_data = array(
//			'title' => '设备详情',
//			'row' => $result['result'],
//			'table' => $this->getTable(),
//		);

		$msg = $result['result']['msg'];
		if ($status == 0) {
			$msg = '关闭成功';
		}
		if ($status == 1) {
			$msg = '打开成功';
		}

		if ($result['code'] == 0 && $result['result']['code'] == 0) {
			return Util_helper::formatResult($result, $msg);
		}
		else {
			return Util_helper::formatResult($result, $result['result']['msg'], $result['result']['code']);
		}

	}

	public function getTable()
	{
		$table = new DataTable('room', 'room');
		$table->canRemoveRow = false;

		$table->addColumn(new DataColumn('label', '名称'))
			->setType(ColumnType::STRING)->setNotNull();

		$table->addColumn(new DataColumn('device_id', '设备id'))
			->setType(ColumnType::STRING)->setNotNull();

		$table->addColumn(new DataColumn('port', '设备端口'))
			->setType(ColumnType::DIGIT)->setNotNull();

		$table->addColumn(new DataColumn('delay', '点动模式（单位秒）'))
			->setType(ColumnType::DIGIT)->setNotNull();

		$table->addColumn(new DataColumn('checkin_status', '入住时开关状态'))
			->setType(ColumnType::SWITCHOR);

		$table->addColumn(new DataColumn('checkout_status', '退房时开关状态'))
			->setType(ColumnType::SWITCHOR);

		return $table;
	}
}
