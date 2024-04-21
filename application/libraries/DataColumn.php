<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2019/12/9
 * Time: 0:27
 */


class DataColumn
{
    public $field = '';
    public $title = '';
    public $tips = '';

    public $width = 160;
    public $sort = false;
    public $totalRow = false;

    public $type = '';//string,selector,list,number,digit,date,time,datetime,radio,checkbox,address
    public $validation = array();// validate: function, tip, nullable, default
    public $dataSource = array();

    public $default = '';

    public $show = true;

    public $nullable = true; //字段默认：值可为空，不能为空的 -- 需要重新设置
    public $editable = true; //字段默认可以修改，填写新建后不能修改的 -- 需要重新设置

    //是否重置(当值为false不能重置，重置时保留值)
    public $canReset = true;

    public function __construct($field, $title, $tips = '')
    {
        $this->field = $field;
        $this->title = $title;
        $this->tips = $tips;
    }

    public function setType($type, $validation = array(), $dataSource = array())
    {
        $this->type = $type;
        $this->validation = $validation;
        $this->dataSource = $dataSource;

        return $this;
    }

    public function setWidth($width = 160)
    {
        $this->width = $width;

        return $this;
    }

    public function setDefault($default = '') {
        $this->default = $default;
        return $this;
    }

    public function setOther()
    {
        return $this;
    }

    public function setSort($sort = false)
    {
        $this->sort = $sort;
        return $this;
    }

    public function setShow($show = true)
    {
        $this->show = $show;
        return $this;
    }

    public function setNotNull()
    {
        $this->nullable = false;
        return $this;
    }

    public function setEditable($editable = false)
    {
        $this->editable = $editable;
        return $this;
    }

    public function setReset($canReset = false)
    {
        $this->canReset = $canReset;
        return $this;
    }

    public function hide()
    {
        $this->show = false;
        return $this;
    }
}
