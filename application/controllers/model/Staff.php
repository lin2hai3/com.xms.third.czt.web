<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/11/25
 * Time: 23:28
 */

require_once 'application/libraries/DataTable.php';
require_once 'application/libraries/DataColumn.php';
require_once 'application/libraries/DataItem.php';
require_once 'application/libraries/DataFilter.php';
require_once 'application/libraries/ColumnType.php';

class Staff extends CI_Controller
{

	public function index()
	{
		$data['method'] = 'shops.employees.get';
		$data['fields'] = '*';
		$data['page_size'] = 1000;

		$result = Client_helper::load($data);
		$result = json_decode($result, true);

		$_rows = array();
		if (isset($result['result']['rows'])) {
			foreach ($result['result']['rows'] as $row) {
				$_row = array(
					//'id' => $row['id'],
					'code' => $row['member_id'],
					'name' => $row['member_id'] . (empty($row['username']) ? '' : ('-' . $row['username'])),
					'desc' => $row['mobile'],
				);

				$_rows[] = $_row + $row;
			}
		}

		$data = array(
			'title' => '员工管理',
			'rows' => $_rows,
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($data);
	}

	public function show()
	{
		$id = $this->input->get_post('id');

		$data['employee_id'] = $id;
		$data['method'] = 'shops.employees.get';
		$data['fields'] = '*';

		$result = Client_helper::load($data);
		$result = json_decode($result, true);

		$data = array(
			'title' => '员工详情',
			'row' => $result['result'],
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($data);
	}

	public function create()
	{
		$data = array(
			'title' => '新增雇员',
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
		$row['keyword'] = $this->input->get_post('member_id');

		if (empty($row['keyword'])) {
			return Util_helper::formatResult(null, '添加失败，请输入要绑定的手机号', 10003);
		}

		$row['method'] = 'shops.employee.add';

		$result = Client_helper::load($row);
		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			return Util_helper::formatResult(null, '新增成功');
		}
		else {
			return Util_helper::formatResult(null, '添加失败，' . $result['msg'], 10003);
		}
	}

	public function edit()
	{
		$id = $this->input->get_post('id');

		$data['employee_id'] = $id;
		$data['method'] = 'shops.employees.get';
		$data['fields'] = 'id,shop_id,member_id';

		$result = Client_helper::load($data);
		$result = json_decode($result, true);

		$data = array(
			'title' => '员工详情',
			'row' => $result['result'],
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($data);
	}

	public function update()
	{
		$id = $this->input->get_post('id');
		$is_cleaner = $this->input->get_post('is_cleaner');

		$data['id'] = $id;
		$data['is_cleaner'] = $is_cleaner;
		$data['method'] = 'shops.employee.update';

		$result = Client_helper::load($data);
		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			return Util_helper::formatResult(null);
		}
		else {
			return Util_helper::formatResult(null, $result['msg'], $result['code']);
		}
	}

	public function delete()
	{
		$id = $this->input->get_post('id');

		$data['id'] = $id;
		$data['method'] = 'shops.employee.remove';

		$result = Client_helper::load($data);
		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			return Util_helper::formatResult(null);
		}
		else {
			return Util_helper::formatResult(null, $result['msg'], $result['code']);
		}
	}

	public function getTable()
	{
		$table = new DataTable('shop', 'shop');
		//$table->canAddRow = false;
		//$table->showAddData = false;
		$table->canRemoveRow = false;
		$table->canEditRow = false;

		$table->addColumn(new DataColumn('member_id', '手机号码'))
			->setType(ColumnType::STRING)->setNotNull();

		return $table;
	}
}
