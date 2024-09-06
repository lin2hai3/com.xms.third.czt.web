<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends CI_Controller
{
	public function page_index()
	{
		$data = array();
		$data['swiper'] = array(
			array(
				//'img' => $this->getImage('/images/banner/a3.jpg'),
				'img' => 'http://etu.666os.com/slides/a1.jpg',
				'url' => '',
			),
			array(
				//'img' => $this->getImage('/images/banner/a3.jpg'),
				'img' => 'http://etu.666os.com/slides/a1.jpg',
				'url' => '',
			),
			array(
				//'img' => $this->getImage('/images/banner/a3.jpg'),
				'img' => 'http://etu.666os.com/slides/a1.jpg',
				'url' => '',
                //'url' => '/pages/new/activity/a4',
				//'url' => '/pages/new/table/table?id=10002',
			),
		);

		$data['nav'] = array(
			array(
				'img' => $this->get_image('/icons/pos_5.png'),
				// 'url' => '/pages/new/maps/maps?key=景点',
				'url' => '/pages/new/maps/maps2?keyword=景点',
				'name' => '景点',
			),
			array(
				'img' => $this->get_image('/icons/pos_4.png'),
				'url' => '/pages/new/maps/maps?key=卫生间',
				// 'url' => '/pages/new/maps/maps?keyword=卫生间',
				'name' => '卫生间',
			),
			array(
				'img' => $this->get_image('/icons/pos_8.png'),
				// 'url' => '/pages/new/maps/maps?key=停车场',
				// 'url' => '/pages/new/maps/maps?key=停车场',
				'url' => '/pages/new/maps/maps2?keyword=停车场',
				'name' => '停车场',
			),
		);

		$data['nav2'] = array();

//		$data['nav2'][] = array(
//			array(
//				'id' => '1',
//				'img' => $this->get_image('/icons/pos_1.png'),
//				'url' => '/pages/new/maps/maps2?keyword=必尝美食',
//				'name' => '美食',
//				'keyword' => '必尝美食',
//			),
//			array(
//				'id' => '2',
//				'img' => $this->get_image('/icons/pos_2.png'),
//				'url' => '/pages/new/maps/maps2?keyword=餐饮',
//				'name' => '餐饮',
//			),
//			array(
//				'id' => '3',
//				'img' => $this->get_image('/icons/pos_3.png'),
//				'url' => '/pages/new/maps/maps?keyword=住宿',
//				'name' => '住宿',
//			),
//			array(
//				'id' => '4',
//				'img' => $this->get_image('/icons/pos_4.png'),
//				'url' => '/pages/new/maps/maps?key=卫生间',
//				// 'url' => '/pages/new/maps/maps2?keyword=卫生间',
//				'name' => '卫生间',
//			),
//		);
//
//		$data['nav2'][] = array(
//			array(
//				'id' => '5',
//				'img' => $this->get_image('/icons/pos_5.png'),
//				'url' => '/pages/new/maps/maps2?keyword=景点',
//				'name' => '景点',
//				'keyword' => '景点',
//			),
//			array(
//				'id' => '6',
//				'img' => $this->get_image('/icons/pos_6.png'),
//				'url' => '/pages/new/maps/maps2?keyword=牌坊',
//				'name' => '牌坊',
//				'keyword' => '牌坊',
//			),
//			array(
//				'id' => '7',
//				'img' => $this->get_image('/icons/pos_7.png'),
//				'url' => '/pages/new/maps/maps2?keyword=名巷',
//				'name' => '名巷',
//				'keyword' => '名巷',
//			),
//			array(
//				'id' => '8',
//				'img' => $this->get_image('/icons/pos_8.png'),
//				'url' => '/pages/new/maps/maps2?keyword=停车场',
//				'name' => '停车场',
//				'keyword' => '停车场',
//			),
//		);

//		$data['nav2'][] = array(
//			array(
//				'id' => '9',
//				'img' => $this->getImage('/icons/pos_9.png'),
//				'url' => '/pages/new/article/article?page=laoyebaohao',
//				'name' => '祈福',
//				'keyword' => '祈福',
//			),
//			array(
//				'id' => '10',
//				'img' => $this->getImage('/icons/shop.png'),
//				'url' => '/pages/goods/list',
//				'name' => '商城',
//				'keyword' => '商城',
//			),
//			array(
//				'id' => '11',
//				'img' => $this->getImage('/icons/zone.png'),
//				'url' => '/pages/new/shop/zone',
//				'name' => '商圈',
//				'keyword' => '商圈',
//			),
//			array(
//				'id' => '12',
//				'img' => $this->getImage('/icons/pos_1.png'),
//				'url' => '/pages/new/luckdraw/luckdraw?no=12f815761ed2f3469a28da2ee46adc2a',
//				'name' => '抽奖',
//				'keyword' => '',
//			),
//			 array(
//			     'id' => '10',
//			     'img' => '/images/signin.png',
//			     'url' => '',
//			     'name' => '企业登记码',
//			     'keyword' => '企业登记码',
//			 ),
//		);

		$data['module'] = array(
			array(

				'img' => '/images/pos_1.png',
				'url' => '',
				'name' => '活动',
			),
		);


		$this->load->model('Config_model', 'cfg');
		$configs = $this->cfg->fetch();

//		$data['ad'] = array(
//			'status' => $configs['index_banner_1_status'] == 1 ? 'on' : 'off',
//			'img' => $configs['index_banner_1_image'],
//			'url' => $configs['index_banner_1_url'],
//			'name' => '',
//		);
//
//		$data['ad2'] = array(
//			'status' => 'off',
//			'img' => $this->getImage('/images/ad20200425.png?v=1'),
//			'url' => '/pages/new/article/article?page=custom&url=https://s.didi.cn/F1dtc',
//			'name' => '',
//		);

		$data['blocks'] = array(
//			array(
//				'img' => $configs['index_block_1_img'],
//				'url' => $configs['index_block_1_url'],
//				'name' => $configs['index_block_1_name'], //'酒店预定/时租房',
//				'wx_app' => $configs['index_block_1_wx_app'],
//			),
//			array(
//				'img' => $configs['index_block_2_img'],
//				'url' => $configs['index_block_2_url'],
//				'name' => $configs['index_block_2_name'], //'领券',
//				'wx_app' => $configs['index_block_2_wx_app'],
//			),
//			array(
//				'img' => $configs['index_block_5_img'],
//				'url' => $configs['index_block_5_url'],
//				'name' => $configs['index_block_5_name'], //'民宿',
//				'wx_app' => $configs['index_block_5_wx_app'],
//			),
			array(
				'img' => $this->fill_empty($configs['index_block_6_img']),
				'url' => $this->fill_empty($configs['index_block_6_url']),
				'name' => $this->fill_empty($configs['index_block_6_name']), //'一票潮州',
				'wx_app' => $this->fill_empty($configs['index_block_6_wx_app']),
			),
//			array(
//				'img' => $configs['index_block_3_img'],
//				'url' => $configs['index_block_3_url'],
//				'name' => $configs['index_block_3_name'], //'在线祈福',
//				'wx_app' => $configs['index_block_3_wx_app'],
//			),
//			array(
//				'img' => $configs['index_block_4_img'],
//				'url' => $configs['index_block_4_url'],
//				'name' => $configs['index_block_4_name'], //'报名登记',
//				'wx_app' => $configs['index_block_4_wx_app'],
//			),

		);

		return util_helper::result($data);
	}

	public function get_image($url)
	{
		return 'https://linhai.666os.com/assets/' . $url;
	}

	public function fill_empty($value)
	{
		return isset($value) ? $value : '';
	}

}
