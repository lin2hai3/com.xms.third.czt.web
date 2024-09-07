<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/11/25
 * Time: 21:50
 */

namespace model;
use CI_Controller;
use Client_helper;
use ColumnType;
use DataColumn;
use DataTable;
use Util_helper;

require_once 'application/libraries/DataTable.php';
require_once 'application/libraries/DataColumn.php';
require_once 'application/libraries/DataItem.php';
require_once 'application/libraries/DataFilter.php';
require_once 'application/libraries/ColumnType.php';

class Shop extends CI_Controller
{

    public function index()
    {
        $data['method'] = 'shops.shops.get';
        $data['fields'] = 'id,merchant_id,fullname,name,keywords,hours,address,location,phone,image,description,linkman,mid,tid,opening_hours,interval_time,cleaning,booking_days,booking_min_times,booking_max_times,checkin_offset,checkout_offset,cuser,ctime,mtime,enabled,removed,published';

        $result = Client_helper::load($data);
        $result = json_decode($result, true);

        $data = array(
            'title' => '门店管理',
            'rows' => $result['result']['rows'],
            'table' => $this->getTable(),
        );

        return Util_helper::formatResult($data);
    }

    public function show()
    {
        $id = $this->input->get_post('id');

        $data['id'] = $id;
        $data['method'] = 'shops.shop.get';
        // $data['fields'] = 'id,merchant_id,fullname,name,keywords,hours,address,location,phone,image,description,linkman,mid,tid,opening_hours,interval_time,cleaning,booking_days,booking_min_times,booking_max_times,checkin_offset,checkout_offset,cuser,ctime,mtime,enabled,removed,published';
        $data['fields'] = '*';


        $result = Client_helper::load($data);
        $result = json_decode($result, true);

        $data = array(
            'title' => '门店详情',
            'row' => $result['result'],
            'table' => $this->getTable(),
        );

        return Util_helper::formatResult($data);
    }

    public function edit()
    {
        $id = $this->input->get_post('id');

        $data['id'] = $id;
        $data['method'] = 'shops.shop.get';
        $data['fields'] = 'id,merchant_id,fullname,name,keywords,hours,address,location,phone,image,description,linkman,mid,tid,opening_hours,interval_time,cleaning,booking_days,booking_min_times,booking_max_times,checkin_offset,checkout_offset,cuser,ctime,mtime,enabled,removed,published';

        $result = Client_helper::load($data);
        $result = json_decode($result, true);

        $data = array(
            'title' => '编辑门店',
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

        $row['method'] = 'shops.shop.update';

        $result = Client_helper::load($row);
        $result = json_decode($result, true);

        if ($result['code'] == 0) {
            return Util_helper::formatResult(null, '修改成功');
        } else {
            return Util_helper::formatResult(null, $result['msg'], 10003);
        }
    }

    public function getTable()
    {
        $table = new DataTable('shop', 'shop');
        $table->canAddRow = false;
        $table->showAddData = false;
        $table->canRemoveRow = false;

        $table->addColumn(new DataColumn('name', '名称'))
            ->setType(ColumnType::STRING)->setNotNull();

        $table->addColumn(new DataColumn('description', '描述'))
            ->setType(ColumnType::STRING)->setNotNull();

        $table->addColumn(new DataColumn('phone', '客服电话'))
            ->setType(ColumnType::STRING);

        $table->addColumn(new DataColumn('address', '地址'))
            ->setType(ColumnType::STRING);

        $table->addColumn(new DataColumn('location', '定位'))
            ->setType(ColumnType::LOCATION);

        $table->addColumn(new DataColumn('hours', '营业时间(显示用)'))
            ->setType(ColumnType::STRING);

        $table->addColumn(new DataColumn('opening_hours', '营业时间'))
            ->setType(ColumnType::STRING);

        $table->addColumn(new DataColumn('booking_days', '可提前预约天数'))
            ->setType(ColumnType::NUMBER);

        $table->addColumn(new DataColumn('booking_min_times', '最小预约时间倍数'))
            ->setType(ColumnType::NUMBER);

        $table->addColumn(new DataColumn('booking_max_times', '最大预约时间倍数'))
            ->setType(ColumnType::NUMBER);

        $table->addColumn(new DataColumn('checkin_offset', '可提前入住时间(秒)'))
            ->setType(ColumnType::NUMBER);

        $table->addColumn(new DataColumn('checkout_offset', '可延迟离店时间(秒)'))
            ->setType(ColumnType::NUMBER);

//		$table->addColumn(new DataColumn('order_close_delay', '订单入住前开启门厅电源时间(秒)'))
//			->setType(ColumnType::NUMBER);
//
//		$table->addColumn(new DataColumn('room_preck_offset', '未付款订单自动关闭时间(秒)'))
//			->setType(ColumnType::NUMBER);

        return $table;
    }
}
