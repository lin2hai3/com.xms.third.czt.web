<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2019/12/5
 * Time: 18:23
 */

// require 'RequestUtil.php';

/*
 *    小程序支付生产参数如下
 *    消息来源(msgSrc)：WWW.CHZSBMH.COM
 *    来源编号（msgSrcId）：6630
 *    通讯密钥:tFKR8y6aTQyTN6cWmBaBCASBRFJHKRXxfyJX3rdFZkJpmBWY
 *    机构商户号(instMid)：MINIDEFAULT
 *    商户编号898445173110047终端号67123274
 */

class BankPay
{
    protected static $test_config = array(
        'host' => 'https://qr-test2.chinaums.com',
        'uri' =>  '/netpay-route-server/api/',
        'mid' => '898340149000005',
        'tid' =>  '88880001',
        'instMid' =>  'MINIDEFAULT',
        'msgSrc' =>  'WWW.TEST.COM',
        'msgSrcId' => '3194',
        'md5_key' =>  'fcAmtnx7MwismjWNhNKdHC44mNXtnEQeJkRrhKJwyrW2ysRR'
    );

    protected static $config = array(
        'host' => 'https://qr.chinaums.com',
        'uri' =>  '/netpay-route-server/api/',
        'mid' => '898445173110047',//城际通
        'tid' =>  '67123274',
        //'mid' => '898445173110048',//白马荟
        //'tid' => '67123758',
        'instMid' =>  'MINIDEFAULT',
        'msgSrc' =>  'WWW.CHZSBMH.COM',
        'msgSrcId' => '6630',
        'md5_key' =>  'tFKR8y6aTQyTN6cWmBaBCASBRFJHKRXxfyJX3rdFZkJpmBWY'
    );

    public static function pay($data)
    {
        $_data = array('msgType' => 'wx.unifiedOrder');
        $_data += $data;
        $_data['merOrderId'] = self::$config['msgSrcId'] . date('ymd') . $data['merOrderId'];
        $_data['tradeType'] = 'MINI';

        return self::request($_data);
    }

    private static function request($data)
    {
        $data['mid'] = empty($data['mid']) ? self::$config['mid'] : $data['mid'];
        $data['tid'] = empty($data['tid']) ? self::$config['tid'] : $data['tid'];
        $data['instMid'] = self::$config['instMid'];
        $data['msgSrc'] = self::$config['msgSrc'];
        $data['msgSrcId'] = self::$config['msgSrcId'];
        $data['requestTimestamp'] = date('Y-m-d H:i:s');
        //$data['openid'] = 'oV_0Q5Tf95STu79XnO673bQ3c9IA';
        //$data['subOpenId'] = 'oV_0Q5Tf95STu79XnO673bQ3c9IA';
        $data['orderDesc'] = empty($data['orderDesc']) ? '白马荟订单' : $data['orderDesc'];

        ksort($data);

        $info='';
        $is_first = true;

        foreach ($data as $key => $val) {
            if(is_array($val)) {
                $val = json_encode($val);
            }
            if($is_first) {
                $is_first = false;
            }
            else {
                $info .= '&';
            }
            $info .= $key;
            $info .= '=';
            $info .= $val;
        }

        $info = $info . '' . self::$config['md5_key'];

        self::log('银联支付待签串', $info);

        $data['sign'] = strtoupper(md5($info));

        $data = json_encode($data);
        $url = self::$config['host'] . self::$config['uri'];

        //var_dump($data);
        self::log('银联支付请求参数', $data);
        $res = RequestUtil::api_request($url, $data, 'POST', false, true);
        self::log('银联支付响应参数', $res);
        return $res;
    }

    public static function log($msg, $data)
    {
        if(is_array($data)) {
            $data = json_encode($data);
        }

        $filename = 'logs/' . date('Y-m-d') . '-log.txt';
        $mode = file_exists($filename) ? 'a+' : 'w';
        $myFile = fopen($filename, $mode) or die('Unable to open file!');
        $txt = '[' . date('Y-m-d H:i:s') . '] ' . $msg . ' ' . $data  . PHP_EOL;
        fwrite($myFile, $txt);
        fclose($myFile);
    }
}
