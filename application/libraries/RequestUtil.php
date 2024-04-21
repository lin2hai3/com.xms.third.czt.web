<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2019/12/5
 * Time: 18:23
 */


class RequestUtil
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
    public static function api_request($url, $data, $method, $encode = true, $json = true, $proxy_host = '') {
        $protocol = 'http';
        if(strpos($url, '://') !== false) {
            $explode_arr = explode('://', $url);
            $protocol = $explode_arr[0];
        }

        $method = strtoupper($method);
        $data_fields = '';
        if($encode && (is_array($data) || is_object($data))) {
            //$data_fields = http_build_query($data);
        } else {
            $data_fields = $data;
        }

        if($method == 'GET' && $encode && $data_fields) {
            $url .= '?'.$data_fields;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if(!empty($proxy_host)) {
            curl_setopt($curl, CURLOPT_PROXY, $proxy_host);
        }

        if($json) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));
        }

        switch ($method){
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

        if($protocol == 'https')
        {
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
	public static function request($url, $data, $method, $encode = true, $header = null, $proxy_host = '') {
		$protocol = 'http';
		if(strpos($url, '://') !== false) {
			$explode_arr = explode('://', $url);
			$protocol = $explode_arr[0];
		}
		$method = strtoupper($method);
		$data_fields = '';
		if($encode && (is_array($data) || is_object($data))) {
			$data_fields = http_build_query($data);
		} else {
			$data_fields = $data;
		}
		if($method == 'GET' && $encode && $data_fields) {
			$url .= '?'.$data_fields;
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if(!empty($proxy_host)) {
			curl_setopt($curl, CURLOPT_PROXY, $proxy_host);
		}
		if(!empty($header)) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		}

		//logger('post data:'.$data_fields);

		switch ($method){
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
		if($protocol == 'https')
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1);
		}
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}

}
