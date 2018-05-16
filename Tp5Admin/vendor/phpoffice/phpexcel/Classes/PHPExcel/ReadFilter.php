<?php
/** PHPExcel root directory */
if (!defined('PHPEXCEL_ROOT')) {
    /**
     * @ignore
     */
    define('PHPEXCEL_ROOT', dirname(__FILE__) . '/../');
    require(PHPEXCEL_ROOT . 'PHPExcel/Autoloader.php');
}

class PHPExcel_ReadFilter implements PHPExcel_Reader_IReadFilter {

    public $startRow = 1;
    public $endRow;

    public function readCell($column, $row, $worksheetName = '') {
        //如果endRow没有设置表示读取全部
        if (!$this->endRow) {
            return true;
        }
        //只读取指定的行
        if ($row >= $this->startRow && $row <= $this->endRow) {
            return true;
        }

        return false;
    }

}