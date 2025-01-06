<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cms extends CI_Controller
{
	// public $cms_url = 'http://dev.cms.xms.wiki/api/xmiog8j6ur6t14xoc1olmaogaonapl92';
	public $cms_url = 'http://dev.cms.xms.wiki/open';

	public function api()
	{
		$uris = array(
			'fetch_user' => '/cms/fetch/user',
			'wish.index' => '/apps/wish/index',
			'wish.show' => '/apps/wish/show',
			'wish.store' => '/apps/wish/store',
			'wish.update_donor' => '/apps/wish/update_donor',
			'wish.mine' => '/apps/wish/mine',
			'wish.edit' => '/apps/wish/editDonor',
			'wish.update' => '/apps/wish/update',

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

		$params = array(
			'sid' => '123',
		);

		$params = array_merge($params, $data);

		$response = Request_helper::request($this->cms_url . $uri, $params, 'POST', false, false);

		echo $response;
	}
}
