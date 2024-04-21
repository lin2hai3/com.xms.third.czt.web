<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2022/9/9
 * Time: 10:29
 */

class Config_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();

		// $this->load->database();
	}

	public function fetch()
	{
		$this->db->where('wx_app_code', 'eticket');
		$configs = $this->db->get('config')->result();

		$_configs = array();
		foreach ($configs as $config) {
			$_configs[$config->key] = $config->value;
		}

		return $_configs;
	}
}
