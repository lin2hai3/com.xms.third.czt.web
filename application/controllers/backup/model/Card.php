<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/30
 * Time: 2:58
 */

namespace model;
use CI_Controller;
use Client_helper;
use ColumnType;
use DataColumn;
use DataItem;
use DataTable;
use Util_helper;

require_once 'application/libraries/DataTable.php';
require_once 'application/libraries/DataColumn.php';
require_once 'application/libraries/DataItem.php';
require_once 'application/libraries/DataFilter.php';
require_once 'application/libraries/ColumnType.php';

class Card extends CI_Controller
{

	public function index()
	{
		$data['page_size'] = 100;
		$data['method'] = 'marketing.recharge.cards.get';
		$data['fields'] = '*';

		$result = Client_helper::load($data);
		$result = json_decode($result, true);

		foreach ($result['result']['rows'] as &$row) {
			$row['name'] = $row['title'];
		}

		$data = array(
			'title' => '储值卡管理',
			'rows' => $result['result']['rows'],
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($data);
	}

	public function create()
	{
		$data = array(
			'title' => '新增储值卡',
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
		$row['enabled'] = $row['enabled'] ? 1 : 0;
		$row['enabled'] = 1;

		$img_host = $this->config->item('img_host');
		if (isset($row['image'])) {
			$row['image'] = str_replace($img_host, '', $row['image']);
		}
		unset($row['image_name']);

		$row['method'] = 'marketing.recharge.card.insert';

		$result = Client_helper::load($row);
		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			return Util_helper::formatResult(null, '新增成功');
		} else {
			return Util_helper::formatResult(null, $result['msg'], 10003);
		}
	}

	public function edit()
	{
		$id = $this->input->get_post('id');

		$data['id'] = $id;
		$data['method'] = 'marketing.recharge.card.get';
		$data['fields'] = '*';

		$result = Client_helper::load($data);
		$result = json_decode($result, true);

		$img_host = $this->config->item('img_host');
		if (!empty($result['result']['image'])) {
			$result['result']['image'] = $img_host . $result['result']['image'];
		}

		$data = array(
			'title' => '编辑储值卡',
			'row' => $result['result'],
			'table' => $this->getTable(),
		);

		return Util_helper::formatResult($data);
	}

	public function update()
	{
		$id = $this->input->get_post('id');
		$row = $this->input->get_post('row');
		$row = json_decode($row, true);

		$row['method'] = 'marketing.recharge.card.update';

		$img_host = $this->config->item('img_host');
		if (isset($row['image'])) {
			$row['image'] = str_replace($img_host, '', $row['image']);
		}
		unset($row['image_name']);

		$result = Client_helper::load($row);
		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			return Util_helper::formatResult(null, '修改成功');
		} else {
			return Util_helper::formatResult(null, $result['msg'], 10003);
		}
	}


	public function delete()
	{
		$id = $this->input->get_post('id');

		// marketing.recharge.card.delete
		$row['id'] = $id;
		$row['method'] = 'marketing.recharge.card.delete';

		$result = Client_helper::load($row);
		$result = json_decode($result, true);

		if ($result['code'] == 0) {
			return Util_helper::formatResult(null, '删除成功');
		} else {
			return Util_helper::formatResult(null, $result['msg'], 10003);
		}
	}

	public function getTable()
	{

		$table = new DataTable('card', 'card');
		$table->canRemoveRow = true;

		$table->addColumn(new DataColumn('title', '名称'))
			->setType(ColumnType::STRING)->setNotNull();

		$table->addColumn(new DataColumn('description', '描述'))
			->setType(ColumnType::STRING)->setNotNull();

		$table->addColumn(new DataColumn('type', '类型'))
			->setType(ColumnType::SELECTOR, array(), array(
				new DataItem('余额', 'MO'),
			))->setNotNull();

//		$table->addColumn(new DataColumn('face_amount', '面额'))
//			->setType(ColumnType::DIGIT)->setNotNull();

//		$table->addColumn(new DataColumn('actual_amount', '实到金额'))
//			->setType(ColumnType::DIGIT)->setNotNull();

		$table->addColumn(new DataColumn('pay_amount', '支付金额'))
			->setType(ColumnType::DIGIT)->setNotNull();

		$table->addColumn(new DataColumn('bonus_amount', '奖励金额'))
			->setType(ColumnType::DIGIT)->setNotNull();

		$upload_url = $this->config->item('upload_url');

		$table->addColumn(new DataColumn('image', '图片'))
			->setType(ColumnType::IMAGE, array(), $upload_url);

		$table->addColumn(new DataColumn('enabled', '是否生效'))
			->setType(ColumnType::SWITCHOR);

		return $table;
	}
}
