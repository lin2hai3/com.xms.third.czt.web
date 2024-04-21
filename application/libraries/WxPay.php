<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2019/12/7
 * Time: 11:08
 */

// require 'RequestUtil.php';


class WxPay
{
    public $name = '微信支付';
    public $version = '1.0';
    public $logo = 'http://img.unitedshop.cn/static/image/payment/wechat.png';
    public $installed = false;
    public $status = -1;
    public $plugin_name = 'WxpayPayment';
    public $support_device = '';
    public $standalone = true;
    public $config = array(
        'app_id' => '', //绑定支付的APPID（必须配置，开户邮件中可查看）
        'mch_id' => '', //商户号（必须配置，开户邮件中可查看）
        'app_key' => '', //商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）, 请妥善保管， 避免密钥泄露
        'sign_type' => 'MD5',
        'trade_type' => 'JSAPI',
        'public_pem' => '',
        'private_pem' => '',
	);
    //同步/异步支付方式
    public $sync = false;
    private $public_pem;
    private $private_pem;

    public static $lang = array(
        'app_id' => '公众号/小程序APPID',
        'mch_id' => '微信商户号',
        'app_key' => '商户支付密钥',
        'sign_type' => '签名算法(MD5)',
        'trade_type' => '交易类型(JSAPI)',
        'public_pem' => '微信证书apiclient_cert.pem',
        'private_pem' => '微信证书密钥apiclient_key.pem'
	);

    // 发起支付接口名称（统一下单）
    private $pay_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    // 查询交易状态接口名称（查询订单）
    private $query_url = 'https://api.mch.weixin.qq.com/pay/orderquery';

    // 发起退款接口名称
    private $refund_url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';

    // 付款到零钱 - 付款
    private $pay_balance_url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

    // 付款到银行卡 - 付款
    // https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_2
    private $pay_bank_url = 'https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank';

    // 付款到零钱 - 查询付款
    private $query_balance_url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';

    // 付款到银行卡 - 查询付款
    private $query_bank_url = 'https://api.mch.weixin.qq.com/mmpaysptrans/query_bank';

    // 获取RSA加密公钥API
    // https://pay.weixin.qq.com/wiki/doc/api/tools/mch_pay.php?chapter=24_7
    private $rsa_key_url = 'https://fraud.mch.weixin.qq.com/risk/getpublickey';

    public function __construct($payment = null)
    {
        if (!empty($payment) && !empty($payment->config)) {
            $this->config = array_merge($this->config, json_decode($payment->config, true));
            $this->public_pem = $payment->public_pem;
            $this->private_pem = $payment->private_pem;
        }
    }

    public function prePay($payLog, $return_url = '', $notify_url = '')
    {
        // $notify_url = empty($notify_url) ? route('channel.pay.notify', ['pay_no' => $payLog->pay_no]) : $notify_url;

        $params = array(
            'appid' => $this->config['app_id'],
            'mch_id' => $this->config['mch_id'],
            'device_info' => '1000',
            'nonce_str' => $this->generateRandomString('lower-digit', 32),
            'sign_type' => $this->config['sign_type'],
            'trade_type' => $this->config['trade_type'],
            'body' => $payLog->pay_title,      //商品简单描述String(128)
            'detail' => $payLog->pay_remark,   //商品详细描述，对于使用单品优惠的商户，该字段必须按照规范上传
            'attach' => $payLog->pay_flag,     //附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用
            'out_trade_no' => $payLog->pay_no,
            'total_fee' => $payLog->pay_amount * 100,
            'spbill_create_ip' => $payLog->pay_ip,
            'notify_url' => $notify_url,
            'openid' => $payLog->identity,
		);

        if ($this->config['trade_type'] == 'NATIVE') {
            $params['product_id'] = '10000001';
        }

        if ($this->config['sign_type'] == 'MD5') {
            $params['sign'] = $this->md5Sign($params);
        }

        if ($this->config['sign_type'] == 'HMAC-SHA256') {
            $params['sign'] = $this->sha256Sign($params);
        }

        // die(json_encode($params));

        // LogUtil::pay('WX-PAYMENT REQUEST::' . json_encode($params, JSON_UNESCAPED_UNICODE));
        $params = $this->arrayToXml($params);

       	$response = RequestUtil::request($this->pay_url, $params, 'POST', false, false);

        // LogUtil::pay('WX-PAYMENT RESPONSE::' . json_encode($response, JSON_UNESCAPED_UNICODE));
        $response = $this->xmlToArray($response);

        $res['return_code'] = $response['return_code'];
        $res['result_code'] = isset($response['result_code']) ? $response['result_code'] : 'FAIL';

        if ($response['return_code'] == 'SUCCESS' && $response['result_code'] == 'SUCCESS') {

            if ($this->config['trade_type'] == 'JSAPI') {
                $res['data'] = $this->buildJsApiData($response);
            }
            if ($this->config['trade_type'] == 'NATIVE') {
                $res['data'] = array(
                    'code_url' => $response['code_url']
				);
            }
        } else {
        	$err_code = isset($response['err_code']) ? $response['err_code'] : '';
        	$err_code_des = isset($response['err_code_des']) ? $response['err_code_des'] : '';
            $res['return_msg'] = $response['return_msg'];
            $res['err_code'] = $err_code;
            $res['err_code_des'] = $err_code_des;
        }
        // LogUtil::pay('WX-PAYMENT RESULT::' . json_encode($res, JSON_UNESCAPED_UNICODE));

        return $res;
    }

    public function payNotify($input, $raw_input)
    {
        //$_POST参数
        $params = $this->xmlToArray($raw_input);
        //$sign_str = CommonUtil::buildSignStr($params);

        // LogUtil::pay('WX-PAYMENT PAY NOTIFY REQUEST::' . json_encode($raw_input));

        if ($this->checkSign($params, $params['sign'])) {
            // LogUtil::pay('WX-PAYMENT PAY NOTIFY::验签成功 ' . json_encode($params));
        } else {
            // LogUtil::pay('WX-PAYMENT PAY NOTIFY::验签失败');
            return false;
        }

        if ($params['return_code'] == 'SUCCESS' && $params['result_code'] == 'SUCCESS'
            && isset($params['out_trade_no']) && isset($params['transaction_id'])
            && isset($params['total_fee'])
        ) {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            //注意回调传入的金额是以分为单位
            return array(
                'status' => true,
                'serial_no' => $params['out_trade_no'],
                'trade_no' => $params['transaction_id'],
                'amount' => $params['total_fee'] / 100,
			);
        } else {
            $res = $params['return_msg'];
            $res .= isset($params['err_code']) ? $params['err_code'] : '';
            $res .= isset($params['err_code_des']) ? $params['err_code_des'] : '';
            // LogUtil::pay('WX-PAYMENT PAY NOTIFY::结果失败 ' . $res);
            return false;
        }
    }


    /**
     * MD5签名
     * @param $params
     * @return string
     */
    private function md5Sign($params)
    {
        $sign_str = $this->buildSignStr($params);
        $sign_str .= '&key=' . $this->config['app_key'];
        $sign = md5($sign_str);
        $sign = strtoupper($sign);
        return $sign;
    }

    /**
     * sha256签名
     * @param $params
     * @return string
     */
    private function sha256Sign($params)
    {
        $sign_str = $this->buildSignStr($params);
        $sign_str .= '&key=' . $this->config['app_key'];
        $sign = hash_hmac("sha256", $sign_str, $this->config['app_key']);
        $sign = strtoupper($sign);
        return $sign;
    }

    /**
     * 签名验证
     * @param $params
     * @param $sign
     * @return bool
     */
    public function checkSign($params, $sign)
    {
        if ($this->config['sign_type'] == 'MD5') {
            // LogUtil::pay('MD5 sign:' . $this->md5Sign($params));
            return $this->md5Sign($params) == $sign;
        }

        if ($this->config['sign_type'] == 'HMAC-SHA256') {
            return $this->sha256Sign($params) == $sign;
        }

        return false;
    }

    public function buildJsApiData($result)
    {
        if (!array_key_exists('appid', $result)
            || !array_key_exists('prepay_id', $result)
            || $result['prepay_id'] == '') {
            throw new \Exception('参数错误');
        }

        $params = array(
            'appId' => $result['appid'],
            'timeStamp' => time() . '',
            'nonceStr' => $this->generateRandomString('lower-digit', 32),
            'package' => 'prepay_id=' . $result['prepay_id'],
            'signType' => 'MD5'
		);

        $params['paySign'] = $this->md5Sign($params);
        return json_encode($params);
    }

	/**
	 * 生成随机序列
	 * @param string $filter
	 *  all - 英文大小写+数字;
	 *  upper - 英文大写;
	 *  lower - 英文小写;
	 *  digit - 数字;
	 *  upper-digit - 英文大写+数字;
	 *  lower-digit - 英文小写+数字
	 * @param int $length
	 * @return string
	 */
	public function generateRandomString($filter = 'all', $length = 6) {
		$seed = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890';

		switch($filter) {
			case 'upper':
				$seed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

			case 'lower':
				$seed = 'abcdefghijklmnopqrstuvwxyz';
				break;

			case 'digit':
				$seed = '0123456789';
				break;

			case 'upper-digit':
				$seed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
				break;

			case 'lower-digit':
				$seed = 'abcdefghijklmnopqrstuvwxyz0123456789';
				break;
		}

		$random_str = '';
		$seed_length = strlen($seed);

		while($length--) {
			$random_str .= $seed[rand(0, $seed_length - 1)];
		}

		return $random_str;
	}

	/**
	 * Array 转 XML
	 * @param $array
	 * @return string
	 * @throws \Exception
	 */
	public function arrayToXml($array)
	{
		if(!is_array($array) || count($array) <= 0)
		{
			throw new \Exception('数组数据异常！');
		}

		$xml = "<xml>";
		foreach ($array as $key=>$val)
		{
			if (is_numeric($val)){
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
			}else{
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}
		}
		$xml .= "</xml>";

		return $xml;
	}

	/**
	 * XML 转 Array
	 * @param $xml
	 * @return bool|mixed
	 */
	public function xmlToArray($xml)
	{
		if(!$xml){
			return false;
		}
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $result;
	}

	/**
	 * 构建待签字符串
	 * @param $params
	 * @param $separator
	 * @return string
	 */
	public function buildSignStr($params, $separator = '&') {
		$sign_str = '';

		if(is_array($params)) {
			ksort($params);
			foreach($params as $key => $value) {
				if(empty($value) || $key == 'sign') {
					continue;
				}

				if($sign_str != '') {
					$sign_str .= $separator;
				}

				$sign_str .= $key.'='.$value;
			}
		}

		return $sign_str;
	}
}
