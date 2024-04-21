<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2019/12/14
 * Time: 14:05
 */

class DataItem
{
    public $code;
    public $name;

    public function __construct($name, $code)
    {
        $this->name = $name;
        $this->code = $code;
    }
}
