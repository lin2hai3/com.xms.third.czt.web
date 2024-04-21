<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/30
 * Time: 0:00
 */

require_once 'application/libraries/DataTable.php';
require_once 'application/libraries/DataColumn.php';
require_once 'application/libraries/DataItem.php';
require_once 'application/libraries/DataFilter.php';
require_once 'application/libraries/ColumnType.php';

class Coupon extends CI_Controller
{
	public function index()
	{

	}

	public function create()
	{
		$data = array(
			'title' => '新增优惠券',
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

		$row['merchant_id'] = 1;
		$row['published'] = $row['published'] ? 1 : 0;
		$row['removed'] = 0;
		$row['shop_id'] = 1;

		$row['method'] = 'coupons.template.insert';

		$result = Client_helper::load($row);
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
		$data['method'] = 'coupons.template.get';
		$data['fields'] = '*';

		$result = Client_helper::load($data);
		$result = json_decode($result, true);

		$return_data = array(
			'title' => '编辑优惠券',
			'row' => $result['result'],
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($return_data);
	}

	public function update()
	{
		$id = $this->input->get_post('id');
		$row_input = $this->input->get_post('row');
		$row_input = json_decode($row_input, true);

		if ($id != $row_input['id']) {
			return Util_helper::formatResult(null, 'error id', 10003);
		}

		$row['id'] = $row_input['id'];

		$table = $this->getTable();
		$columns = $table->columns;
		foreach ($columns as $column) {
			$row[$column->field] = $row_input[$column->field];
		}

		$row['merchant_id'] = 1;
		$row['published'] = $row['published'] ? 1 : 0;
		$row['removed'] = 0;

		if (isset($row['image_name'])) {
			$row['image_name'] = json_encode($row['image_name']);
		}

		// $row['order_type'] = 'ROOM';

		$row['method'] = 'coupons.template.update';

		$result = Client_helper::load($row);
		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			return Util_helper::formatResult(null, '修改成功');
		}
		else {
			return Util_helper::formatResult(null, $result['msg'], 10003);
		}
	}

	public function delete()
	{
		$id = $this->input->get_post('id');

		// marketing.recharge.card.delete
		$row['id'] = $id;
		$row['method'] = 'coupons.template.remove';

		$result = Client_helper::load($row);
		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			return Util_helper::formatResult(null, '删除成功');
		}
		else {
			return Util_helper::formatResult(null, $result['msg'], 10003);
		}
	}

	public function getTable()
	{
		$table = new DataTable('coupon', 'coupon');
		$table->canRemoveRow = true;

		$table->addColumn(new DataColumn('title', '名称'))
			->setType(ColumnType::STRING)->setNotNull();

		$table->addColumn(new DataColumn('description', '描述'))
			->setType(ColumnType::STRING)->setNotNull();

		$table->addColumn(new DataColumn('coupon_type', '类型'))
			->setType(ColumnType::SELECTOR, array(), array(
				new DataItem('满减券', 'OFF'),
				new DataItem('折扣', 'DISCOUNT'),
				new DataItem('包邮券', 'FREE'),
			))->setNotNull();

		$table->addColumn(new DataColumn('amount_over', '满减金额'))
			->setType(ColumnType::DIGIT)->setNotNull();

		$table->addColumn(new DataColumn('off', '减去'))
			->setType(ColumnType::DIGIT)->setNotNull();

		$table->addColumn(new DataColumn('discount', '折扣'))
			->setType(ColumnType::DIGIT)->setNotNull();

//		$table->addColumn(new DataColumn('receivable_stime', '可领取开始时间'))
//			->setType(ColumnType::DATETIME);

//		$table->addColumn(new DataColumn('receivable_etime', '可领取结束时间'))
//			->setType(ColumnType::DATETIME);

//		$table->addColumn(new DataColumn('receivable_timerange', '可领取时间段 00:00-23:59'))
//			->setType(ColumnType::STRING);

//		$table->addColumn(new DataColumn('usable_stime', '可使用开始时间'))
//			->setType(ColumnType::DATETIME);
//
//		$table->addColumn(new DataColumn('usable_etime', '可使用结束时间'))
//			->setType(ColumnType::DATETIME);
//
//		$table->addColumn(new DataColumn('usable_timerange', '可使用时间段 00:00-23:59'))
//			->setType(ColumnType::STRING);


		$table->addColumn(new DataColumn('max_limit', '发放总量'))
			->setType(ColumnType::STRING);

		$table->addColumn(new DataColumn('day_limit', '每次发放数量'))
			->setType(ColumnType::STRING);

		$table->addColumn(new DataColumn('account_limit', '每账号领取数量'))
			->setType(ColumnType::STRING);

		$upload_url = $this->config->item('upload_url');

		// https://iroom.admin.willmeet.com
//		$table->addColumn(new DataColumn('image', '图片'))
//			->setType(ColumnType::IMAGE, array(), 'https://etb.admin.willmeet.com/?act=Uploader.post');
//			->setType(ColumnType::IMAGE, array(), 'http://youjian.admin.666os.com/?act=Uploader.upload');

		$table->addColumn(new DataColumn('published', '是否发布'))
			->setType(ColumnType::SWITCHOR);

		return $table;
	}
}
