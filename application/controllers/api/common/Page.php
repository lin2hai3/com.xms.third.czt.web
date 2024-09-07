<?php

namespace common;
use CI_Controller;

/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/11/17
 * Time: 16:00
 */
class Page extends CI_Controller
{

	public function index()
	{
		$method = $this->uri->segment(4);

		switch ($method) {
			case 'jinshan_index':
				$result = $this->jianShanIndex();
				break;

			default:
				$result = $this->defaultIndex();
				break;
		}

		echo(json_encode($result));
	}

	public function jianShanIndex()
	{
		$data = array();
		$data['swiper'] = array(
			array(
				'img' => 'http://etu.666os.com/slides/a1.jpg',
				//'img' => 'https://linhai.666os.com/assets/images/js03.jpg',
				'url' => '',
			),
			array(
				'img' => 'http://etu.666os.com/slides/a1.jpg',
				//'img' => 'https://linhai.666os.com/assets/images/js05.jpg',
				'url' => '',
			),
			array(
				'img' => 'http://etu.666os.com/slides/a1.jpg',
				//'img' => 'https://linhai.666os.com/assets/images/js06.jpg',
				'url' => '',
			),
//			array(
//				//'img' => $this->getImage('/images/banner/a3.jpg'),
//				'img' => 'https://linhai.666os.com/assets/images/js04.jpg',
//				'url' => '',
//			),
//			array(
//				//'img' => $this->getImage('/images/banner/a3.jpg'),
//				//'img' => 'http://etu.666os.com/slides/a1.jpg',
//				'img' => 'https://linhai.666os.com/assets/images/js02.jpg',
//                'url' => '',
//                // 'url' => '/pages/new/activity/a4',
//				// 'url' => '/pages/new/table/table?id=10002',
//			),
		);

		$data['nav'] = array(
//			array(
//				'img' => $this->getImage('/images/classify/kongtiao.png'),
//				'url' => '',
//				'name' => '领券',
//			),
//			array(
//				'img' => $this->getImage('/images/classify/Icewash.png'),
//				'url' => '',
//				'name' => '美食',
//			),
//			array(
//				'img' => $this->getImage('/images/classify/heater.png'),
//				'url' => '',
//				'name' => '景点',
//			),
//			array(
//				'img' => $this->getImage('/images/classify/bed.png'),
//				'url' => '',
//				'name' => '文化',
//			),
//			array(
//				'img' => $this->getImage('/images/classify/boutique.png'),
//				'url' => '',
//				'name' => '活动',
//			),
		);

		$data['nav2'][] = array(
			array(
				'id' => '1',
				'img' => $this->getImage('/icons/pos_1.png'),
				'url' => '/pages/new/maps/maps?key=必尝美食',
				'name' => '美食',
				'keyword' => '必尝美食',
			),
			array(
				'id' => '2',
				'img' => $this->getImage('/icons/pos_2.png'),
				'url' => '/pages/new/maps/maps?key=餐饮',
				'name' => '餐饮',
			),
			array(
				'id' => '3',
				'img' => $this->getImage('/icons/pos_3.png'),
				'url' => '/pages/new/maps/maps?key=住宿',
				'name' => '住宿',
			),
			array(
				'id' => '4',
				'img' => $this->getImage('/icons/pos_4.png'),
				'url' => '/pages/new/maps/maps?key=卫生间',
				'name' => '卫生间',
			),
		);

		$data['nav2'][] = array(
			array(
				'id' => '5',
				'img' => $this->getImage('/icons/pos_5.png'),
				'url' => '/pages/new/maps/maps?key=景点',
				'name' => '景点',
				'keyword' => '景点',
			),
			array(
				'id' => '6',
				'img' => $this->getImage('/icons/pos_6.png'),
				'url' => '/pages/new/maps/maps?key=牌坊',
				'name' => '牌坊',
				'keyword' => '牌坊',
			),
			array(
				'id' => '7',
				'img' => $this->getImage('/icons/pos_7.png'),
				'url' => '/pages/new/maps/maps?key=名巷',
				'name' => '名巷',
				'keyword' => '名巷',
			),
			array(
				'id' => '8',
				'img' => $this->getImage('/icons/pos_8.png'),
				'url' => '/pages/new/maps/maps?key=停车场',
				'name' => '停车场',
				'keyword' => '停车场',
			),
		);

		$data['nav2'][] = array(
			array(
				'id' => '9',
				'img' => $this->getImage('/icons/pos_9.png'),
				'url' => '/pages/new/article/article?page=laoyebaohao',
				'name' => '祈福',
				'keyword' => '祈福',
			),
			//array(
			//	'id' => '10',
			//	'img' => $this->getImage('/icons/shop.png'),
			//	'url' => '/pages/goods/list',
			//	'name' => '商城',
			//	'keyword' => '商城',
			//),
			array(
				'id' => '11',
				'img' => $this->getImage('/icons/zone.png'),
				'url' => '/pages/new/shop/zone',
				'name' => '商圈',
				'keyword' => '商圈',
			),
			array(
				'id' => '12',
				'img' => $this->getImage('/icons/pos_1.png'),
				'url' => '/pages/new/luckdraw/luckdraw?no=12f815761ed2f3469a28da2ee46adc2a',
				'name' => '抽奖',
				'keyword' => '',
			),
			// array(
			//     'id' => '10',
			//     'img' => '/images/signin.png',
			//     'url' => '',
			//     'name' => '企业登记码',
			//     'keyword' => '企业登记码',
			// ),
		);

		$data['module'] = array(
			array(

				'img' => '/images/pos_1.png',
				'url' => '',
				'name' => '活动',
			),
		);

		//
		///pages/new/article/article?page=laoyebaohao

		// https://eticket.willmeet.com/users/eticket/thirdy/assets/images/activity/bmh_20200224142302.png
		// /pages/new/shop/register2

		// $this->getImage('/images/ad20200312.png')
		// /pages/new/shop/zone

		// $this->getImage('/images/ad20200313.jpg')
		// /pages/new/shop/info?id=439

		// ad20200413.png
		// /pages/new/table/table?id=10001

		// ad20200425.png
		// /pages/new/table/tables

		$data['ad'] = array(
			'status' => 'off',
			'img' => $this->getImage('/images/ad20200425.png?v=1'),
			'url' => '/pages/new/table/tables',
			'name' => '',
		);

		$data['ad2'] = array(
			'status' => 'off',
			'img' => $this->getImage('/images/ad20200425.png?v=1'),
			'url' => '/pages/new/article/article?page=custom&url=https://s.didi.cn/F1dtc',
			'name' => '',
		);

		return array('code' => 0, 'result' => $data);
	}

	public function defaultIndex()
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
//                'url' => '/pages/new/activity/a4',
				'url' => '/pages/new/table/table?id=10002',
			),
		);

		$data['nav'] = array(
			array(
				'img' => $this->getImage('/images/classify/kongtiao.png'),
				'url' => '',
				'name' => '领券',
			),
			array(
				'img' => $this->getImage('/images/classify/Icewash.png'),
				'url' => '',
				'name' => '美食',
			),
			array(
				'img' => $this->getImage('/images/classify/heater.png'),
				'url' => '',
				'name' => '景点',
			),
			array(
				'img' => $this->getImage('/images/classify/bed.png'),
				'url' => '',
				'name' => '文化',
			),
			array(
				'img' => $this->getImage('/images/classify/boutique.png'),
				'url' => '',
				'name' => '活动',
			),
		);

		$data['nav2'][] = array(
			array(
				'id' => '1',
				'img' => $this->getImage('/icons/pos_1.png'),
				'url' => '/pages/new/maps/maps?key=必尝美食',
				'name' => '美食',
				'keyword' => '必尝美食',
			),
			array(
				'id' => '2',
				'img' => $this->getImage('/icons/pos_2.png'),
				'url' => '/pages/new/maps/maps?key=餐饮',
				'name' => '餐饮',
			),
			array(
				'id' => '3',
				'img' => $this->getImage('/icons/pos_3.png'),
				'url' => '/pages/new/maps/maps?key=住宿',
				'name' => '住宿',
			),
			array(
				'id' => '4',
				'img' => $this->getImage('/icons/pos_4.png'),
				'url' => '/pages/new/maps/maps?key=卫生间',
				'name' => '卫生间',
			),
		);

		$data['nav2'][] = array(
			array(
				'id' => '5',
				'img' => $this->getImage('/icons/pos_5.png'),
				'url' => '/pages/new/maps/maps?key=景点',
				'name' => '景点',
				'keyword' => '景点',
			),
			array(
				'id' => '6',
				'img' => $this->getImage('/icons/pos_6.png'),
				'url' => '/pages/new/maps/maps?key=牌坊',
				'name' => '牌坊',
				'keyword' => '牌坊',
			),
			array(
				'id' => '7',
				'img' => $this->getImage('/icons/pos_7.png'),
				'url' => '/pages/new/maps/maps?key=名巷',
				'name' => '名巷',
				'keyword' => '名巷',
			),
			array(
				'id' => '8',
				'img' => $this->getImage('/icons/pos_8.png'),
				'url' => '/pages/new/maps/maps?key=停车场',
				'name' => '停车场',
				'keyword' => '停车场',
			),
		);

		$data['nav2'][] = array(
			array(
				'id' => '9',
				'img' => $this->getImage('/icons/pos_9.png'),
				'url' => '/pages/new/article/article?page=laoyebaohao',
				'name' => '祈福',
				'keyword' => '祈福',
			),
			//array(
			//	'id' => '10',
			//	'img' => $this->getImage('/icons/shop.png'),
			//	'url' => '/pages/goods/list',
			//	'name' => '商城',
			//	'keyword' => '商城',
			//),
			array(
				'id' => '11',
				'img' => $this->getImage('/icons/zone.png'),
				'url' => '/pages/new/shop/zone',
				'name' => '商圈',
				'keyword' => '商圈',
			),
			array(
				'id' => '12',
				'img' => $this->getImage('/icons/pos_1.png'),
				'url' => '/pages/new/luckdraw/luckdraw?no=12f815761ed2f3469a28da2ee46adc2a',
				'name' => '抽奖',
				'keyword' => '',
			),
			// array(
			//     'id' => '10',
			//     'img' => '/images/signin.png',
			//     'url' => '',
			//     'name' => '企业登记码',
			//     'keyword' => '企业登记码',
			// ),
		);

		$data['module'] = array(
			array(

				'img' => '/images/pos_1.png',
				'url' => '',
				'name' => '活动',
			),
		);

		//
		///pages/new/article/article?page=laoyebaohao

		// https://eticket.willmeet.com/users/eticket/thirdy/assets/images/activity/bmh_20200224142302.png
		// /pages/new/shop/register2

		// $this->getImage('/images/ad20200312.png')
		// /pages/new/shop/zone

		// $this->getImage('/images/ad20200313.jpg')
		// /pages/new/shop/info?id=439

		// ad20200413.png
		// /pages/new/table/table?id=10001

		// ad20200425.png
		// /pages/new/table/tables

		$data['ad'] = array(
			'status' => 'off',
			'img' => $this->getImage('/images/ad20200425.png?v=1'),
			'url' => '/pages/new/table/tables',
			'name' => '',
		);

		$data['ad2'] = array(
			'status' => 'off',
			'img' => $this->getImage('/images/ad20200425.png?v=1'),
			'url' => '/pages/new/article/article?page=custom&url=https://s.didi.cn/F1dtc',
			'name' => '',
		);

		return array('code' => 0, 'result' => $data);
	}

	public function getImage($img)
	{
		return 'https://linhai.666os.com/' . $img;
	}
}
