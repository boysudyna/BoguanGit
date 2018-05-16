<?php
// excel数据导入的
namespace application\admin\controller;

use think\request;
use think\Db;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use PHPExcel_ReadFilter;

set_time_limit(0);
class ImportController extends CommonController {

    public function index() {
        if($_GET['date'] != date('Y-m-d'))
            exit('执行日期不正确');

        $dirArr = array(
            'mobile_data_pond_01'=>'pond/01',
            'mobile_data_pond_02'=>'pond/02',
            'mobile_data_pond_03'=>'pond/03',
            'mobile_data_pond_04'=>'pond/04'
        );

        foreach ($dirArr as $key => $value) {
            $dir = ROOT_PATH . 'public' . DS . 'resource'. DS;
            $dir .= $value . DS;
            $fileList = scandir($dir);
            $dbName = $key;
            $total = 0;
            foreach($fileList as $fileName) {
                if($fileName == '.' || $fileName == '..')
                    continue;

                $pathName = $dir.$fileName;
                if(is_file($pathName)) {
                    $handel = fopen($pathName, 'r');
                    $fileTag = 'Five';
                    $i = 0;
                    $per = 3000;
                    while(! feof($handel)) {
                        $buffer = fgets($handel, 1024);
                        // 查找列数，不等于5列的特别处理
                        $string = trim($buffer);
                        if($string)
                            $colArr = explode("\t", trim($string));

                        if($i == 0) {
                            $colNums = count($colArr);
                            if($colNums == '4')
                                $fileTag = 'Four';
                        }

                        // 跳过首行标题
                        if($i == 0) {
                            $i ++;
                            continue;
                        }

                        if($colArr) {
                            if($fileTag == 'Four') {
                                $data[$i]['md_phone'] = $colArr[2];
                                $data[$i]['md_buys'] = $colArr[1];
                                $data[$i]['md_used'] = $colArr[3];
                                $data[$i]['md_expiry_date'] = date('Y-m',strtotime("{$colArr[0]}, +1 month")).'-01';
                                $data[$i]['md_date'] = $colArr[0];
                                if($i % $per == 0) {
                                    Db::name($dbName)->insertAll($data);
                                    unset($data);
                                    // $data = array();
                                    usleep(300);
                                }
                            } else {
                                $data[$i]['md_phone'] = $colArr[3];
                                $data[$i]['md_buys'] = $colArr[2];
                                $data[$i]['md_used'] = $colArr[4];
                                $data[$i]['md_expiry_date'] = $colArr[1];
                                $data[$i]['md_date'] = $colArr[0];
                                if($i % $per == 0) {
                                    Db::name($dbName)->insertAll($data);
                                    unset($data);
                                    // $data = array();
                                    usleep(300);
                                }
                            }

                        }

                        $i ++;
                    }

                    if($data) {
                        Db::name($dbName)->insertAll($data);
                        unset($data);
                        // $data = array();
                        usleep(300);
                        echo $pathName.'==执行完成==><br />'; 
                    }

                    fclose($handel);
                    $total += $i;
                }
            }

            exit ('总行数：'.$total.'!<br />');
        }

        echo '执行完成。。。。';
        exit('!');
    }

    public function showSql() {
        $table = array('t_mobile_data_01', 't_mobile_data_02', 't_mobile_data_03', 't_mobile_data_04');
        $numbers = array('2', '3', '4', '5', '7', '10');
        foreach ($table as $key => $tableName) {
            foreach ($numbers as $kk => $vv) {
                $sql = "SELECT COUNT(*) as S FROM (SELECT * FROM {$tableName} WHERE 1 GROUP BY md_phone HAVING COUNT(*) >= {$vv}) T";
                $nums = Db::query($sql);
                $nums = $nums[0]['S'];
                echo $tableName . ' /' . $vv . ' /' .$nums . '===<br />';
            }
        }

        $sql = "SELECT A.*,B.md_date,c.md_date,d.md_date 
                FROM `t_mobile_data_01` A 
                JOIN `t_mobile_data_02` B USING(md_phone) 
                JOIN `t_mobile_data_03` C USING(md_phone) 
                JOIN `t_mobile_data_04` D USING(md_phone) 
                limit 10";

        exit('!');
    }

    public function excel() {
        $dir = ROOT_PATH . 'public' . DS . 'resource'. DS;
        $fileList = scandir($dir);
        foreach($fileList as $fileName) {
            if($fileName == '.' || $fileName == '..')
                continue;

            $pathName = $dir.$fileName;
            // 方法1：分块读取
            $rowSize = 100; 
            $startRow = 2;//从第二行开始读取
            $endRow = $rowSize;
            $excelOrders = array();
            while (true) {
                $excelOrders = $this->readFromExcelB($pathName, $startRow, $endRow);
                print_r($excelOrders);
                exit;
                if(empty($excelOrders)) {
                      break;
                }

                $startRow = $endRow + 1;
                $endRow = $endRow + $rowSize;
            }
    
            // 方法2：循环读取
            // $objExcel = PHPExcel_IOFactory::load($pathName);
            // $sheet = $objExcel->getSheet(0); // 读取第一個工作表  
            // $highestRow = $sheet->getHighestRow(); // 取得总行数  
            // $highestColumm = $sheet->getHighestColumn(); // 取得总列数  
              
            // /** 循环读取每个单元格的数据 */  
            // for ($row = 2; $row <= $highestRow; $row++){//行数是以第1行开始  
            //     for ($column = 'A'; $column <= $highestColumm; $column++) {//列数是以A列开始  
            //         $dataset[] = $sheet->getCell($column.$row)->getValue();  
            //         //echo $column.$row.":".$sheet->getCell($column.$row)->getValue()."<br />";  
            //         echo $sheet->getCell($column.$row)->getValue()."  ";                  
            //     }  
            //     exit('!');  
            // }  
            
        }

        dump($fileList);
        exit;
    }

    public function readFromExcelB($excelFile, $startRow = 1, $endRow = 100) {
        $excelType = PHPExcel_IOFactory::identify($excelFile);
        $excelReader = PHPExcel_IOFactory::createReader($excelType);

        if(strtoupper($excelType) == 'CSV') {
            $excelReader->setInputEncoding('GBK');
        }

        if ($startRow && $endRow) {
            $excelFilter           = new PHPExcel_ReadFilter();
            $excelFilter->startRow = $startRow;
            $excelFilter->endRow   = $endRow;
            $excelReader->setReadFilter($excelFilter);
        }

        $phpexcel    = $excelReader->load($excelFile);
        $activeSheet = $phpexcel->getActiveSheet();

        $highestColumn      = $activeSheet->getHighestColumn(); //最后列数所对应的字母，例如第1行就是A
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); //总列数

        $data = array();
        for ($row = $startRow; $row <= $endRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $data[$row][] = (string) $activeSheet->getCellByColumnAndRow($col, $row)->getValue();
            }
            if(implode($data[$row], '') == '') {
                unset($data[$row]);
            }
        }

        return $data;
    }
}