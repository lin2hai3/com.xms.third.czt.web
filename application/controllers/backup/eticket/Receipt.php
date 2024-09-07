<?php

namespace eticket;
use CI_Controller;
use Client_helper;
use Util_helper;

/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2022/2/15
 * Time: 3:00
 */
class Receipt extends CI_Controller
{
    public function index()
    {
//		$config = array(
//			'api_host' => 'http://v5.666os.com/',
//			'api_app_id' => 'linhai',
//			'api_app_key' => 'linhai',
//		);
//
//		$data['method'] = 'tickets.receipts.owner.get';
//		$data['method'] = 'tickets.logs.get';
//		$data['fields'] = 'id,ticket_id,receipt_id,member_id,shop_id,admin_id,ctime';
//		$data['keyword'] = '围棋';
//		$data['shop_id'] = 450;
//		$data['page_size'] = '20';
//
//		$result = Client_helper::loadWithConfig($config, $data);
//
//		die($result);
    }


    public function add()
    {
        $config = array(
            'api_host' => 'http://etr.666os.com/',
            'api_app_id' => 'cticket',
            'api_app_key' => 'qZcKiQmN',
        );


        $sid = $this->input->get_post('sid');
        $ticket_id = $this->input->get_post('ticket_id');
        $stime = $this->input->get_post('stime');
        $etime = $this->input->get_post('etime');
        $timerange = $this->input->get_post('timerange');
        $remark = $this->input->get_post('remark');
        $comment = $this->input->get_post('comment');
        $qty = $this->input->get_post('qty');

        $ticket_id = intval($ticket_id);

        if (empty($ticket_id)) {
            die(json_encode(array('code' => -1, 'msg' => 'error input')));
        }

        $has_inj = Util_helper::containsSqlInjection($remark);

        if ($has_inj) {
            die(json_encode(array('code' => -1, 'msg' => 'error input')));
        }

        $has_inj = Util_helper::containsSqlInjection($comment);

        if ($has_inj) {
            die(json_encode(array('code' => -1, 'msg' => 'error input')));
        }


        // fetch weixin id
        $data = array();
        $data['method'] = 'weixin.sid.decode';
        $data['fields'] = '*';
        $data['sid'] = $sid;

        $result = Client_helper::loadWithConfig($config, $data);
        $result = json_decode($result, true);
        $weixin_id = $result['result']['weixin_id'];

        // fetch member_id
        $data = array();
        $data['method'] = 'weixin.member.id.get';
        $data['fields'] = '*';
        $data['weixin_id'] = $weixin_id;

        $result = Client_helper::loadWithConfig($config, $data);
        $result = json_decode($result, true);
        $member_id = $result['result']['member_id'];

        $data = array();
        $data['method'] = 'tickets.receipt.insert';

        $data['ticket_id'] = $ticket_id;
        $data['member_id'] = $member_id;
        $data['stime'] = $stime;
        $data['etime'] = $etime;
        $data['timerange'] = $timerange;
        $data['remark'] = $remark;
        $data['comment'] = $comment;
        $data['qty'] = $qty;

        $result = Client_helper::loadWithConfig($config, $data);

        die(json_encode(json_decode($result, true), JSON_UNESCAPED_UNICODE));
    }
}
