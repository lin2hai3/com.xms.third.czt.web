<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/2/7
 * Time: 10:20
 */

class Util_helper
{
	public static function result($data, $msg = 'success', $code = 0)
	{
		if (empty($data)) {
			echo json_encode(array(
				'code' => $code,
				'msg' => $msg,
			), JSON_UNESCAPED_UNICODE);
		} else {
			echo json_encode(array(
				'code' => $code,
				'msg' => $msg,
				'data' => $data,
			), JSON_UNESCAPED_UNICODE);
		}
	}

	public static function formatResult($data, $msg = 'success', $code = 10000)
	{
		if (empty($data)) {
			echo json_encode(array(
				'return_code' => $code,
				'return_msg' => $msg,
			), JSON_UNESCAPED_UNICODE);
		} else {
			echo json_encode(array(
				'return_code' => $code,
				'return_msg' => $msg,
				'data' => $data,
			), JSON_UNESCAPED_UNICODE);
		}
	}

	public static function getSerialNo()
	{

		list($usec, $sec) = explode(' ', microtime());
		$sequence = intval($usec * 1000000);
		$sequence = $sequence % 4096;
		$serial = microtime(true) * 1000;
		//4位机器码
		$serial = $serial << 4;
		$serial |= 1;

		//12位序列号
		$serial = $serial << 12;
		$serial |= $sequence;
		$serial = decbin($serial);

		return bindec('0' . $serial) . '';
	}

	public static function containsSqlInjection($input)
	{
		// 常见的SQL注入特征
		$patterns = ['/union\s+select/i', '/select\s+.*\s+from\s+/i', '/insert\s+into/i', '/update\s+.*\s+set\s+/i', '/delete\s+from\s+/i', '/drop\s+table/i', '/--\s+/i', '/\bOR\b\s+/i', '/\bAND\b\s+/i', '/\b--\b/i'];
		foreach ($patterns as $pattern) {
			if (preg_match($pattern, $input)) {
				return true;
			}
		}
		return false;
	}
}
