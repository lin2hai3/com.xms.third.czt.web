<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2019/12/9
 * Time: 0:27
 */


class DataFilter
{
    public $type = '';
    public $name = '';
    public $code = '';
    public $list = '';
    public $vals = [];
    public $sort = 1;
    public $row = 1;

    public function __construct($type, $name, $code, $list, $vals, $sort = 1, $row = 1)
    {
        $this->type = $type;
        $this->name = $name;
        $this->code = $code;
        $this->list = $list;
        $this->vals = $vals;
        $this->sort = $sort;
        $this->row = $row;
    }
}
