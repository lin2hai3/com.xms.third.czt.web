<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/11/3
 * Time: 14:03
 */

class Request_helper
{
	/**
	 * 发起http请求
	 * @param string $url 请求网址
	 * @param mixed $data 请求参数
	 * @param string $method GET|POST|PUT|PATCH|DELETE 请求方法
	 * @param bool $encode $data是否需要进行拼接
	 * @param bool $json 是否附带JSON头部
	 * @param string $proxy_host 代理地址
	 * @return string
	 */
	public static function api_request($url, $data, $method, $encode = true, $json = true, $proxy_host = '')
	{
		$protocol = 'http';
		if (strpos($url, '://') !== false) {
			$explode_arr = explode('://', $url);
			$protocol = $explode_arr[0];
		}
		$method = strtoupper($method);
		$data_fields = '';
		if ($encode && (is_array($data) || is_object($data))) {
			$data_fields = http_build_query($data);
		} else {
			$data_fields = $data;
		}
		if ($method == 'GET' && $encode && $data_fields) {
			$url .= '?' . $data_fields;
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		if (!empty($proxy_host)) {
			curl_setopt($curl, CURLOPT_PROXY, $proxy_host);
		}

		if ($json) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json;charset=UTF-8'
			]);
		}

		switch ($method) {
			case 'GET' :
				curl_setopt($curl, CURLOPT_HTTPGET, true);
				break;
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'PATCH':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'PUT':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
		}

		if ($protocol == 'https') {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1);
		}

		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}

	public static function icbc_request($url, $data, $method, $encode = true, $json = true, $proxy_host = '')
	{
		$protocol = 'http';
		if (strpos($url, '://') !== false) {
			$explode_arr = explode('://', $url);
			$protocol = $explode_arr[0];
		}
		$method = strtoupper($method);
		$data_fields = '';
		if ($encode && (is_array($data) || is_object($data))) {
			$data_fields = http_build_query($data);
		} else {
			$data_fields = $data;
		}
		if ($method == 'GET' && $encode && $data_fields) {
			$url .= '?' . $data_fields;
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		if (!empty($proxy_host)) {
			curl_setopt($curl, CURLOPT_PROXY, $proxy_host);
		}

//		if ($json) {
//			curl_setopt($curl, CURLOPT_HTTPHEADER, [
//				'Content-Type: application/json;charset=UTF-8'
//			]);
//		}

		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
		]);

		switch ($method) {
			case 'GET' :
				curl_setopt($curl, CURLOPT_HTTPGET, true);
				break;
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'PATCH':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'PUT':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
		}

		if ($protocol == 'https') {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1);
		}

		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}


	/**
	 * 发起http请求
	 * @param string $url 请求网址
	 * @param mixed $data 请求参数
	 * @param string $method GET|POST|PUT|PATCH|DELETE 请求方法
	 * @param bool $encode $data是否需要进行拼接
	 * @param mixed $header 是否附带请求头部
	 * @param string $proxy_host 代理地址
	 * @return string
	 */
	public static function request($url, $data, $method, $encode = true, $header = null, $proxy_host = '')
	{
		$protocol = 'http';
		if (strpos($url, '://') !== false) {
			$explode_arr = explode('://', $url);
			$protocol = $explode_arr[0];
		}
		$method = strtoupper($method);
		$data_fields = '';
		if ($encode && (is_array($data) || is_object($data))) {
			$data_fields = http_build_query($data);
		} else {
			$data_fields = $data;
		}
		if ($method == 'GET' && $encode && $data_fields) {
			$url .= '?' . $data_fields;
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60); // env('HTTP_REQUEST_TIMEOUT', 60)
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if (!empty($proxy_host)) {
			curl_setopt($curl, CURLOPT_PROXY, $proxy_host);
		}
		if (!empty($header)) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}

		// curl_setopt($curl, CURLOPT_HTTPHEADER, array("Expect:"));

		switch ($method) {
			case 'GET' :
				curl_setopt($curl, CURLOPT_HTTPGET, true);
				break;
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'PATCH':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'PUT':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
		}
		if ($protocol == 'https') {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1);
		}
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}

	public static function get($url, $data, $encode = true, $header = null, $proxy_host = '')
	{
		return self::request($url, $data, 'GET', $encode, $header, $proxy_host);
	}

	public static function post($url, $data, $encode = true, $header = null, $proxy_host = '')
	{
		return self::request($url, $data, 'POST', $encode, $header, $proxy_host);
	}

	/**
	 * 发起http请求
	 * @param string $url 请求网址
	 * @param mixed $data 请求参数
	 * @param string $method GET|POST|PUT|PATCH|DELETE 请求方法
	 * @param bool $encode $data是否需要进行拼接
	 * @param mixed $header 是否附带请求头部
	 * @param string $proxy_host 代理地址
	 * @param array $use_cert cert pem
	 * @return string
	 */
	public static function request_with_cert($url, $data, $method, $encode = true, $header = null, $proxy_host = '', $use_cert = null)
	{
		$protocol = 'http';
		if (strpos($url, '://') !== false) {
			$explode_arr = explode('://', $url);
			$protocol = $explode_arr[0];
		}

		$method = strtoupper($method);
		$data_fields = '';
		if ($encode && (is_array($data) || is_object($data))) {
			$data_fields = http_build_query($data);
		} else {
			$data_fields = $data;
		}

		if ($method == 'GET' && $encode && $data_fields) {
			$url .= '?' . $data_fields;
		}

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60); // env('HTTP_REQUEST_TIMEOUT', 60)
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		if (!empty($proxy_host)) {
			curl_setopt($curl, CURLOPT_PROXY, $proxy_host);
		}

		if (!empty($header)) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}

		if (!empty($use_cert)) {
			logger($use_cert);
			curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
			curl_setopt($curl, CURLOPT_SSLCERT, $use_cert['ssl_cert_path']);
			curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
			curl_setopt($curl, CURLOPT_SSLKEY, $use_cert['ssl_key_path']);
		}

		switch ($method) {
			case 'GET':
				curl_setopt($curl, CURLOPT_HTTPGET, true);
				break;
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'PATCH':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'PUT':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data_fields);
				break;
		}

		if ($protocol == 'https') {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1);
		}

		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}

	public static function download($url, $file_path, $method = 'GET') {
		$protocol = 'http';
		if(strpos($url, '://') !== false) {
			$explode_arr = explode('://', $url);
			$protocol = $explode_arr[0];
		}
		$method = strtoupper($method);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
		$fp = fopen($file_path, 'w+');
		curl_setopt($curl, CURLOPT_FILE, $fp);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60); // env('HTTP_REQUEST_TIMEOUT', 60)
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

		switch ($method){
			case 'GET' :
				curl_setopt($curl, CURLOPT_HTTPGET, true);
				break;
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, true);
				break;
		}

		if($protocol == 'https')
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1);
		}

		$data = curl_exec($curl);
		curl_close($curl);
		fclose($fp);
		return $data;
	}

	public static function put_url($url,$header,$data){
		$ch = curl_init(); //初始化CURL句柄
		curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//SSL认证。
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PUT"); //设置请求方式
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置提交的字符串
		$output = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if($code == 200) return true;
		else return false;
		//return json_decode($output,true);
	}

	public static function get_url($url,$header){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		$output = curl_exec($ch);
		curl_close($ch);
		$obj = simplexml_load_string($output,"SimpleXMLElement", LIBXML_NOCDATA);
		$output = json_decode(json_encode($obj),true);
		return $output;
	}

	public static function post_url($url,$header,$data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		// POST数据
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		// 把post的变量加上
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	public static function del_url($url,$header) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

		//设置头
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置请求头

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//SSL认证。
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	public static function head_url($url,$header) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		//设置头
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //设置请求头

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//SSL认证。
		$output = curl_exec($ch);
		curl_close($ch);

		return $output;
	}
}
