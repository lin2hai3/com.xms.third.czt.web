<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cms extends CI_Controller
{
	// public $cms_url = 'http://dev.cms.xms.wiki/api/xmiog8j6ur6t14xoc1olmaogaonapl92';
	// public $cms_url = 'http://dev.cms.xms.wiki/open';
	public $cms_url = 'https://cms.xms.wiki/open';

	public function api()
	{
		$uris = array(
			'fetch_user' => '/cms/fetch/user',
			'wish.index' => '/apps/wish/index',
			'wish.show' => '/apps/wish/show',
			'wish.store' => '/apps/wish/store',
			'wish.donate' => '/apps/wish/donate',
			'wish.status' => '/apps/wish/status',

			'wish.mine' => '/apps/wish/mine',
			'wish.edit' => '/apps/wish/editDonor',
			'wish.update' => '/apps/wish/update',
			'wish.upload' => '/apps/wish/upload',
			'wish.bind_wechat' => '/apps/wish/bindWechat',

//			 	checkIsAdminUri:'/apps/wish/admin/is_admin',
//			 	adminIndexUri:'/apps/wish/admin/index',
//			 	auditWishUri:'/apps/wish/admin/audit_wish',
//			 	auditDonateUri:'/apps/wish/admin/audit_donate',
//			 	adminUpdateUri:'/apps/wish/admin/update',
//			 	adminShowUri:'/apps/wish/admin/show',
//			 	imagesUri:'/apps/wish/index_images',
//			 	confirmissuedUri:'/apps/wish/admin/confirm_issued',
//
//			 	agentJsonUri: '/apps/wish/agent_json',
		);

		// $uri = $this->input->get_post('uri');
		$method = $this->uri->segment(4);

		$uri = '';

		if (isset($uris[$method])) {
			$uri = $uris[$method];
		}

		$data = $this->input->post();

		$params = array(// 'sid' => '123',
		);

		$params = array_merge($params, $data);

		$response = Request_helper::request($this->cms_url . $uri, $params, 'POST', false, false);

		echo $response;
	}

	public function upload()
	{
		// 设置响应头为 JSON
		header('Content-Type: application/json');

		// 响应初始化
		$response = [
			'return_code' => 10003,  // 默认返回错误
			'return_msg' => '未知错误',
			'data' => null,
		];

		// 设置上传目录
		$uploadDir = 'uploads/';

		// 检查目标目录是否存在，如果不存在则自动创建
		if (!is_dir($uploadDir)) {
			// 尝试创建目录并设置权限
			if (!mkdir($uploadDir, 0777, true)) {
				// 创建目录失败
				$response['return_msg'] = '无法创建上传目录！';
				echo json_encode($response);
				exit;
			}
		}

		$host = 'https://linhai.666os.com/v2/';

		// 检查文件是否通过表单上传
		if (isset($_FILES['file'])) {
			// 获取上传文件的相关信息
			$file = $_FILES['file'];

			// 获取文件名、临时文件路径、文件类型等
			$fileName = $file['name'];
			$fileTmpName = $file['tmp_name'];
			$fileSize = $file['size'];
			$fileError = $file['error'];

			// 文件扩展名
			$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

			// 定义允许的文件扩展名
			$allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];

			// 检查文件扩展名是否允许
			if (in_array($fileExt, $allowed)) {
				// 检查是否有上传错误
				if ($fileError === 0) {
					// 检查文件大小（例如最大5MB）
					if ($fileSize < 5000000) {
						// 生成唯一的文件名
						$newFileName = uniqid('', true) . '.' . $fileExt;

						// 设置目标路径
						$fileDestination = $uploadDir . $newFileName;

						// 移动文件到目标目录
						if (move_uploaded_file($fileTmpName, $fileDestination)) {
							$response['return_code'] = 10000;  // 0 表示成功
							$response['return_msg'] = '文件上传成功！';
							$response['data'] = [
								'file_name' => $newFileName,
								'file_path' => $fileDestination,
								'url' => $host  . $fileDestination,
							];
						} else {
							$response['return_msg'] = '文件上传失败！';
						}
					} else {
						$response['return_msg'] = '文件太大！请确保文件小于5MB。';
					}
				} else {
					$response['return_msg'] = '上传过程中出现错误！错误代码: ' . $fileError;
				}
			} else {
				$response['return_msg'] = '不允许的文件类型！';
			}
		} else {
			$response['return_msg'] = '没有选择文件！';
		}

		// 输出响应
		echo json_encode($response);
	}
}
