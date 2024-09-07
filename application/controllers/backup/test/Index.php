<?php

namespace test;
use CI_Controller;
use Util_helper;

/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2021/12/30
 * Time: 2:58
 */
class Index extends CI_Controller
{

    public function test_index()
    {
        $data = array(
            'abc' => 'abc',
        );

        log_message('DEBUG', 'test index');

        return Util_helper::formatResult($data);
    }
}
