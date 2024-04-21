<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2019/12/8
 * Time: 23:11
 */

class DataTable
{
    //名称
    public $name = '默认列表';
    //
    public $key = 'id';
    //
    public $code = '';
    //
    public $addUrl = '';
    //数据源
    public $dataUrl = '';
    //
    public $viewPath = '';
    //
    public $editPath = '';
    //首页
    public $indexUrl = '';
    //添加
    public $createUrl = '';
    //工具栏
    public $toolbarStyle = '';//default
    //分页
    public $showPage = false;
    //每页条数
    public $pageLimit = 50;
    //表格合并栏
    public $showTotalRow = false;
    //表格选中列
    public $showCheckColumn = false;
    //搜索面板
    public $showSearch = false;
    //添加记录
    public $showAddData = false;
    //表格列
    public $columns = [];
    //显示在列表的行
    public $listColumns = [];
    //行操作
    public $actionBars = [];
    //筛选条件
    public $filters = [];
    //导出按钮
    public $export = [];
    //导出路径
    public $exportUrl = '';

    //-----------------行操作相关-----------------

    //能否编辑记录
    public $canEditRow = true;
    //能否删除记录
    public $canRemoveRow = true;
    //能否添加记录
    public $canAddRow = true;
    //能否排序
    public $canSortRow = true;
    //能否搜索
    public $canSearchRow = false;
    //能否上下架记录
    public $canStatusRow = false;
    //上架
    public $canOnRow = false;
    //下架
    public $canOffRow = false;

    //行数限制
    public $rowLimit = -1;
    //导出文件名
    public $exportFileName = '';


    //-----------------行操作相关-----------------

    public function __construct($name = '', $code = '', \Closure $callback = null)
    {
        $this->name = $name;
        $this->code = $code;

        if (!is_null($callback)) {
            $callback($this);
        }
    }

    public function addColumn(DataColumn $column)
    {
        $this->columns[] = $column;
        return $column;
    }

    public function getColumnsJSON()
    {
        $_columns = array();
        if(!empty($this->listColumns) && is_array($this->listColumns)) {
            foreach ($this->columns as $column) {
                if(in_array($column->field, $this->listColumns)) {
                    array_push($_columns, $column);
                }
            }
        }
        else {
            $_columns = $this->columns;
        }

        if($this->showCheckColumn) {
            array_unshift($_columns, array(
                'type' => 'checkbox',
            ));
        }
        if(!empty($this->actionBars)) {
            array_push($_columns, array(
                'title' => '操作',
                'toolbar' => '#action-bar',
                'align' => 'center',
                'fixed' => 'right',
                'width' => 90 + 30 + 45 * count($this->actionBars)
            ));
        }
        return json_encode(array($_columns), JSON_UNESCAPED_SLASHES);
    }

    public function addFilter(DataFilter $filter)
    {
        $this->filters[] = $filter;
        return $filter;
    }

    public function setExportFileName($exportFileName)
    {
        $this->exportFileName = $exportFileName;
    }

    public function exportDefault($records)
    {
        $_columns = array();
        if(!empty($this->listColumns) && is_array($this->listColumns)) {
            foreach ($this->columns as $column) {
                if(in_array($column->field, $this->listColumns)) {
                    array_push($_columns, $column);
                }
            }
        }
        else {
            $_columns = $this->columns;
        }

        if (empty($this->exportFileName)) {
            $title_name = $this->name . '-' . date('Y-m-d');
        }
        else {
            $title_name = $this->exportFileName;
        }

        $title = [];
        $pos = intval(count($_columns) / 2);
        $pos = ($pos > 3 ? $pos - 1 : $pos);
        for($idx = 0; $idx < count($_columns); $idx++) {
            $title[] = ($idx == $pos ? $title_name : '');
        }

        $header = [];
        foreach($_columns as $_column) {
            $header[] = $_column->title;
        }

        $_records = [];
        foreach($records as $record) {
            $_record = [];
            foreach($_columns as $_column) {
                if(is_object($record)) {
                    $_record[$_column->field] = $record->{$_column->field};
                }
                else {
                    $_record[$_column->field] = $record[$_column->field];
                }
            }
            $_records[] = $_record;
        }

        if (empty($this->exportFileName)) {
            $file_name = $this->name . '-' . date('Y-m-d');
        }
        else {
            $file_name = $this->exportFileName;
        }

        $result = ReportUtil::exportCsv($file_name, $title, $header, $_records);
        return $result;
    }
}
