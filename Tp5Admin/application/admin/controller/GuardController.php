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

            // 如果是搜索则输出总金额
            if(request()->isPost()) {
                $totalMoney = db("phone_detail")->field("sum(m_money) as S")->where($where)->find();
                $this->assign('totalMoney', $totalMoney['S']);
            }

            // 分页传参数， ['query' => request()->param()] 或者 $parmas = request()->param(); $obj->appends($parmas)->render();
            $lists = db("phone_detail")->where($where)->order('m_id asc')->paginate(15, false, ['query' => request()->param()]);
            $this->assign('lists', $lists);
            $this->assign('reqData', $data);
        }
        
        return $this->fetch();
    }

    public function export() {
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('手机保障'); //给当前活动sheet设置名称
        $titleArr = ['m_id','电话','运营商','开设日期','结束日期','套餐号','收费天数','金额','月份'];
        // 设置行标题
        $this->collowToCode($PHPSheet, $titleArr, $i=1);
        // 设置内容
        
        $PHPSheet->setCellValue('A1','姓名')->setCellValue('B1','分数');//给当前活动sheet填充数据，数据填充是按顺序一行一行填充的，假如想给A1留空，可以直接setCellValue('A1','');
        $PHPSheet->setCellValue('A2','张三')->setCellValue('B2','50');
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007'表示生成2007版本的xlsx，
        ob_end_clean();//清除缓冲区,避免乱码;
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=demo2.xlsx");
        header('Cache-Control: max-age=0');
        $PHPWriter->save('php://output'); //表示在$path路径下面生成demo.xlsx文件
        exit;
    }

    public function collowToCode($obj, $data, $i, $length) {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        foreach($data as $col => $v) {
            $obj->setCellValue($code[$col].$i, $v);
        }
    }

}
