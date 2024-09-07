<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/11/25
 * Time: 2:37
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


class Room extends CI_Controller
{
    public $sets = array(
        'wifi' => array(
            'name' => 'WIFI',
            'image' => '../../static/wifi.png',
        ),
        'automat' => array(
            'name' => '自动售卖机',
            'image' => '../../static/automat.png',
        ),
        'charger' => array(
            'name' => '共享充电宝',
            'image' => '../../static/charger.png',
        ),
        'projector' => array(
            'name' => '投影仪',
            'image' => '../../static/projector.png',
        ),
    );

    public function index()
    {
        $data['method'] = 'rooms.rooms.get';
        // $data['fields'] = 'id,merchant_id,shop_id,name,description,area,seats,base_rates,increase_rates,has_wifi,has_automat,has_charger,enabled';
        $data['fields'] = '*';

        $result = Client_helper::load($data);
        $result = json_decode($result, true);

        $return_data = array(
            'title' => '房间管理',
            'rows' => $result['result']['rows'],
            'table' => $this->getTable(),
        );

        return Util_helper::formatResult($return_data);
    }

    public function show()
    {
        $id = $this->input->get_post('id');

        $data['id'] = $id;
        $data['method'] = 'rooms.room.get';
        // $data['fields'] = 'id,merchant_id,shop_id,name,description,area,seats,base_rates,increase_rates,has_wifi,has_automat,has_charger,enabled,images';
        $data['fields'] = '*';

        $result = Client_helper::load($data);
        $result = json_decode($result, true);

        $sets = explode(',', $result['result']['facilities']);
        $_sets = array();
        foreach ($sets as $set) {
            if (isset($this->sets[$set])) {
                array_push($_sets, $this->sets[$set]);
            }
        }
        $result['result']['sets'] = $_sets;

        $return_data = array(
            'title' => '房间详情',
            'row' => $result['result'],
            'table' => $this->getTable(),
        );

        return Util_helper::formatResult($return_data);
    }

    public function create()
    {
        $data = array(
            'title' => '新增房间',
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

        $row['method'] = 'rooms.room.insert';

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
        $data['method'] = 'rooms.room.get';
        $data['fields'] = '*';

        $result = Client_helper::load($data);
        $result = json_decode($result, true);

        // $img_host = $this->config->item('img_host');
        $images = array();
        if (!empty($result['result']['images'])) {
            foreach ($result['result']['images'] as &$image) {
                $images[] = $image['url'];
            }
        }
        $result['result']['images'] = $images;
        $result['result']['images_name'] = $images;

        $result['result']['facilities'] = explode(',', $result['result']['facilities']);
        foreach ($this->sets as $key => $set) {
            if (in_array($key, $result['result']['facilities'])) {
                $result['result']['has_' . $key] = true;
            }
        }

        $return_data = array(
            'title' => '编辑房间',
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

        $img_host = $this->config->item('img_host');
        $images = array();
        if (isset($row['images'])) {
            for ($idx = 1; $idx <= 5; $idx++) {
                $row['image' . $idx] = '';
            }
            foreach ($row['images'] as $key => $image) {
                $row['image' . ($key + 1)] = str_replace($img_host, '', $image);
            }
        }
        $row['images'] = json_encode($images);
        unset($row['images_name']);

        $facilities = array();
        foreach ($this->sets as $key => $set) {
            if (isset($row['has_' . $key]) && $row['has_' . $key]) {
                $facilities[] = $key;
                unset($row['has_' . $key]);
            }
        }
        $row['facilities'] = implode(',', $facilities);
        unset($row['facilities_set']);

        $row['method'] = 'rooms.room.update';
        // print_r($row);die();
        $result = Client_helper::load($row);
        $result = json_decode($result, true);

        if ($result['code'] == 0) {
            return Util_helper::formatResult(null, '修改成功');
        } else {
            return Util_helper::formatResult(null, $result['msg'], 10003);
        }
    }

    public function checkin()
    {
        $order_number = $this->input->get_post('order_number');

        $data['order_number'] = $order_number;
        $data['method'] = 'rooms.room.checkin';

        $result = Client_helper::load($data);
        $result = json_decode($result, true);

        if ($result['code'] == 0) {
            return Util_helper::formatResult(null, '开门成功');
        } else {
            return Util_helper::formatResult(null, $result['msg'], $result['code']);
        }


    }

    public function start_clean()
    {
        $id = $this->input->get_post('id');

        $data['id'] = $id;
        $data['status'] = 'CLEANING';
        $data['method'] = 'rooms.room.update';

        $result = Client_helper::load($data);
        $result = json_decode($result, true);

        if ($result['code'] == 0) {
            return Util_helper::formatResult(null, '开始打扫');
        } else {
            return Util_helper::formatResult(null, $result['msg'], 10003);
        }
    }

    public function end_clean()
    {
        $id = $this->input->get_post('id');

        $data['id'] = $id;
        $data['status'] = 'FREE';
        $data['method'] = 'rooms.room.update';

        $result = Client_helper::load($data);
        $result = json_decode($result, true);

        if ($result['code'] == 0) {
            return Util_helper::formatResult(null, '完成打扫');
        } else {
            return Util_helper::formatResult(null, $result['msg'], 10003);
        }
    }

    public function getTable()
    {
        $table = new DataTable('room', 'room');
        $table->canRemoveRow = false;

        $table->addColumn(new DataColumn('name', '名称'))
            ->setType(ColumnType::STRING)->setNotNull();

        $table->addColumn(new DataColumn('description', '描述'))
            ->setType(ColumnType::STRING)->setNotNull();

        $table->addColumn(new DataColumn('base_rates', '基础价'))
            ->setType(ColumnType::DIGIT)->setNotNull();

        $table->addColumn(new DataColumn('increase_rates', '递增价'))
            ->setType(ColumnType::DIGIT)->setNotNull();

        $table->addColumn(new DataColumn('area', '面积'))
            ->setType(ColumnType::STRING);

        $table->addColumn(new DataColumn('seats', '可容纳人数'))
            ->setType(ColumnType::STRING);

        $table->addColumn(new DataColumn('has_wifi', '是否配套WIFI'))
            ->setType(ColumnType::SWITCHOR);

        $table->addColumn(new DataColumn('has_automat', '自动售货机'))
            ->setType(ColumnType::SWITCHOR);

        $table->addColumn(new DataColumn('has_charger', '共享充电宝'))
            ->setType(ColumnType::SWITCHOR);

        $table->addColumn(new DataColumn('has_projector', '投影仪'))
            ->setType(ColumnType::SWITCHOR);

        $table->addColumn(new DataColumn('published', '前端是否可见'))
            ->setType(ColumnType::SWITCHOR);

        $table->addColumn(new DataColumn('enabled', '前端是否可预约'))
            ->setType(ColumnType::SWITCHOR);


        $upload_url = $this->config->item('upload_url');

        $table->addColumn(new DataColumn('images', '图片'))
            ->setType(ColumnType::IMAGES, array(), $upload_url);

        //$table->addColumn(new DataColumn('image', '图片'))
        //	->setType(ColumnType::IMAGE, array(), 'https://etb.admin.willmeet.com/?act=Uploader.post');
        // ->setType(ColumnType::IMAGE, array(), 'http://youjian.admin.666os.com/?act=Uploader.upload');


        return $table;
    }
}
