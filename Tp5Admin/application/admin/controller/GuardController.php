<?php

/**
 *  手机保障数据查询
 * @file   ConfigController.php  
 * @date   2016-10-10 9:39:19 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace application\admin\controller;

use think\Request;
use think\Db;
use PHPExcel_IOFactory;
use PHPExcel;
use org\Upload;

class GuardController extends CommonController {

    public function index() {
        if(request()->param()){
            $where = array();
            $data = input('param.');
            if($data['phone'])
                $where['m_phone'] = $data['phone'];

            if($data['meal'])
                $where['m_name'] = $data['meal'];

            if($data['month'])
                $where['m_tag'] = $data['month'];
            // 搜索
            if(input('?Search')) {
                // 如果是搜索则输出总金额
                if(request()->isPost()) {
                    $totalMoney = db("phone_detail")->field("sum(m_money) as S")->where($where)->find();
                    $this->assign('totalMoney', $totalMoney['S']);
                }

                // 分页传参数， ['query' => request()->param()] 或者 $parmas = request()->param(); $obj->appends($parmas)->render();
                $lists = db("phone_detail")->where($where)->order('m_id asc')->paginate(15, false, ['query' => request()->param()]);
                $this->assign('lists', $lists);
                $this->assign('reqData', $data);
            } else {
                // 导出
                $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
                $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
                $PHPSheet->setTitle('手机保障'); //给当前活动sheet设置名称
                if(! $where) {
                    js_alert('为防止数据过大，请输入条件！');
                }

                $titleArr = ['mid','电话','运营商','开设日期','结束日期','套餐号','收费天数','金额','月份', '执行时间'];
                // 设置行标题
                $this->collowToCode($PHPSheet, $titleArr, 1);
                $ret = db("phone_detail")->where($where)->select();
                $k = 2;
                foreach ($ret as $key => $value) {
                    $key += $k;
                    $this->collowToCode($PHPSheet, $value, $key);
                }

                $fileName = date('Ymd');
                $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007'表示生成2007版本的xlsx，
                ob_end_clean();//清除缓冲区,避免乱码;
                header('Content-Type: application/vnd.ms-excel');
                header("Content-Disposition: attachment;filename={$fileName}.xlsx");
                header('Cache-Control: max-age=0');
                $PHPWriter->save('php://output'); //表示在$path路径下面生成demo.xlsx文件
                exit;
            }
        }
        
        return $this->fetch();
    }


    public function import() {
        $dir = ROOT_PATH . 'public' . DS . 'uploads'. DS . 'files';
        $dirArr = scandir($dir);
        unset($dirArr[0], $dirArr[1]);
        foreach ($dirArr as $key => $value) {
            $list[$value] = $value;
        }

        $file = '';
        if(request()->param()) {
            $data = input('param.');
            $file = $data['ftxt'];
            $phone = $data['phone'];
            $begLine = $data['begLine'];
            $nums = $data['nums'] >= 1000 ? 1000 : $data['nums'];
            if(! is_file($dir.DS.$file)) {
                alert_error('文件不存在！', url());
            }

            $handle = fopen($dir.DS.$file, 'r');
            $i = 0;
            $showStr = '';
            while(!feof($handle)) {
                $i ++;
                $buffer = fgets($handle, 1024);
                if($begLine && $i < $begLine)
                    continue;
                if($phone) {
                    // $str = preg_replace('/(\d+)|(\d+)|(\d+)|/', $phone, $buffer);
                    $reg = "/^(\d+)\|(\d+)\|\d*(".$phone.")/";
                    // $str = preg_match($reg, $buffer);
                    if(preg_match($reg, $buffer)){
                        $str = preg_replace("/^(\d+\|)(\d+\|)(\d*)(".$phone.")(\d*)/", '$1$2$3<font color="red">$4</font>$5', $buffer);
                        $showStr .= $str . '<br />';
                    }
                }
            }

           $this->assign('showStr', $showStr);
        } else {
            $data['nums'] = 1000;
        }

        $select = select($list, $file, 'name="ftxt"');
        $this->assign('dir', $select);
        $this->assign('reqData', $data);
        return $this->fetch();
    }

    public function upload() {
        if (!empty($_FILES)) {
            // $tempFile = $_FILES['file']['tmp_name'];
            // $targetFile = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'files';
            
            // // Validate the file type
            // $fileTypes = array('jpg','jpeg','gif','png'); // File extensions
            // $fileParts = pathinfo($_FILES['file']['name']);
            
            // if (in_array($fileParts['extension'],$fileTypes)) {
            //     move_uploaded_file($tempFile, $targetFile.DS.$_FILES['file']['name']);
            //     echo 'Success';
            // } else {
            //     echo 'Error: Invalid file type.';
            // }
            $config = array(
                'ext' => 'txt, rar, docs',
            );

            $file = request()->file('file');
            $info = $file->validate($config)->move(ROOT_PATH . 'public' . DS . 'uploads'. DS . 'files', '');
            if($info){
                alert_success('上传成功', url('import'));
            }else{
                alert_error('失败：' . $file->getError());
            }
            exit;
        }
    }

    public function collowToCode($obj, $data, $i) {
        $data = array_values($data);
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        foreach($data as $col => $v) {
            $obj->setCellValue($code[$col].$i, $v);
        }
    }

}
