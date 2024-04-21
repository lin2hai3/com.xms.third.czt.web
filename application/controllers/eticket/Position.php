<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Position extends CI_Controller
{
	public function index()
	{

	}

	public function store()
	{

	}

	public function update()
	{

	}

	public function destroy()
	{

	}

	public function fetch()
	{
		$keyword = $this->input->get_post('keyword');
		$input_data = $this->input->get_post('data');
		$input_data = json_decode($input_data, true);
		$markers = $input_data['markers'];
		$poi_data = $input_data['poisData'];

		// 组装名称
		$marker_names = array();
		foreach ($markers as $marker) {
			$marker_names[] = $marker['name'];
		}

		$db_markers = array();
		$db_marker_names = array();

		$ignore_keywords = array('美食', '景点', '卫生间', '厕所', '停车场');

		if (!in_array($keyword, $ignore_keywords)) {

			$this->load->model('Position_model', 'pos');
			$db_markers = $this->pos->get_rows($marker_names);

			$db_marker_maps = array();
			$db_marker_names = array();
			foreach ($db_markers as $db_marker) {
				if (!isset($db_marker_maps[$db_marker->name])) {
					$db_marker_maps[$db_marker->name] = $db_marker;
					$db_marker_names[] = $db_marker->name;
				}
			}

			$_markers = array();
			$_poi_data = array();

			for ($idx = 0; $idx < count($markers); $idx++) {
				if (isset($db_marker_maps[$markers[$idx]['name']])) {
					$markers[$idx]['article_url'] = $db_marker_maps[$markers[$idx]['name']]->article_url;
					$_markers[] = $markers[$idx];
					$_poi_data[] = $poi_data[$idx];
				}
			}
		}
		else {
			$_markers = $markers;
			$_poi_data = $poi_data;
		}

		$return_data = array(
			'markers' => $_markers,
			'poisData' => $_poi_data,
			'$marker_names' => $marker_names,
			'$db_markers' => $db_markers,
			'$db_marker_names' => $db_marker_names,
		);

		return util_helper::result($return_data);
	}
}
