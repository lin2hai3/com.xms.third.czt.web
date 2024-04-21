<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/11/3
 * Time: 23:33
 */

class Client_helper
{
	private static $url; //接口地址
	private static $app_id; //APP ID
	private static $app_key; //APP KEY
	private static $encode; // 编码格式 json 或 xml;

	public static function init($url, $app_id, $app_key, $encode='json')
	{
		self::$url = $url;
		self::$app_id = $app_id;
		self::$app_key = $app_key;
		self::$encode = $encode;
	}

	private static function get_token($params)
	{
		$str = '';
		ksort($params);

		foreach ($params as $key => $value)
		{
			$str .= $key . $value;
		}

		$app_key = self::$app_key;

		$token = strtolower(substr(md5($app_key . $str . $app_key),0,8));

		return $token;
	}

	public static function load($params, $raw=0)
	{
		$ci = &get_instance();
		self::init($ci->config->config['api_host'], $ci->config->config['api_app_id'], $ci->config->config['api_app_key']);

		$sys_params = array();
		$sys_params['method'] = $params['method'];
		$sys_params['encode'] = self::$encode;
		$sys_params['app_id'] = self::$app_id;
		$sys_params['timestamp'] =gmdate('Y-m-d H:i:s', time() + 8*3600 );
		$sys_params['raw'] =$raw;

		$sys_params['token']=self::get_token($sys_params);

		// 1. 初始化
		$ch = curl_init();

		// 2. 设置选项，包括URL
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($ch, CURLOPT_URL, self::$url . '?' . self::parse($sys_params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		if ($raw)
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params) );//POST JSON数据
		}
		else
		{
			$postBodyString = "";
			$postMultipart = false;
			foreach ($params as $k => $v)
			{
				if("@" != substr($v, 0, 1) || substr($v,0,2)=='@=' )//判断是不是文件上传
				{
					$postBodyString .= "$k=" . urlencode($v) . "&";
				}
				else//文件上传用multipart/form-data，否则用www-form-urlencoded
				{
					$postMultipart = true;
				}
			}
			curl_setopt($ch, CURLOPT_POST, true);
			if ($postMultipart)
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}
			else
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
			}
		}

		// 3. 执行并获取HTML文档内容
		$output = curl_exec($ch);
		if (FALSE === $output) {
			return "Remote Error: " . curl_error($ch);
		}

		// 4. 释放curl句柄
		curl_close($ch);

		return $output;

	}

	public static function loadWithConfig($config, $params, $raw=0)
	{
		$ci = &get_instance();
		self::init($config['api_host'], $config['api_app_id'], $config['api_app_key']);

		$sys_params = array();
		$sys_params['method'] = $params['method'];
		$sys_params['encode'] = self::$encode;
		$sys_params['app_id'] = self::$app_id;
		$sys_params['timestamp'] =gmdate('Y-m-d H:i:s', time() + 8*3600 );
		$sys_params['raw'] =$raw;

		$sys_params['token']=self::get_token($sys_params);

		// 1. 初始化
		$ch = curl_init();

		// 2. 设置选项，包括URL
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		curl_setopt($ch, CURLOPT_URL, self::$url . '?' . self::parse($sys_params));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		if ($raw)
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params) );//POST JSON数据
		}
		else
		{
			$postBodyString = "";
			$postMultipart = false;
			foreach ($params as $k => $v)
			{
				if("@" != substr($v, 0, 1) || substr($v,0,2)=='@=' )//判断是不是文件上传
				{
					$postBodyString .= "$k=" . urlencode($v) . "&";
				}
				else//文件上传用multipart/form-data，否则用www-form-urlencoded
				{
					$postMultipart = true;
				}
			}
			curl_setopt($ch, CURLOPT_POST, true);
			if ($postMultipart)
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}
			else
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString,0,-1));
			}
		}

		// 3. 执行并获取HTML文档内容
		$output = curl_exec($ch);
		if (FALSE === $output) {
			return "Remote Error: " . curl_error($ch);
		}

		// 4. 释放curl句柄
		curl_close($ch);

		return $output;

	}


	private static function parse($params)
	{
		$str = '';
		$sep = '';

		foreach($params as $key => $val)
		{
			$str .= $sep . $key . '=' . urlencode($val);
			$sep = '&';
		}

		return $str;

	}

}
