<?php

/**
 *  手机分期数据查询
 * @file   ConfigController.php  
 * @date   2016-10-10 9:39:19 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace application\admin\controller;

use think\Request;
use think\Db;
class StagedController extends CommonController {

    public function index() {
        if(request()->param()) {
            $where = array();
            $data = input('param.');
            if($data['phone']) {
                if(strpos($data['phone'], ',') !== false)
                    $where['up_phone'] = array('IN', $data['phone']);
                else
                    $where['up_phone'] = $data['phone'];
            }

            if(strlen($data['vip']))
                $where['up_vip'] = $data['vip'];

            if($data['egtArpu'])
                $where['up_ARPU'][] = array('egt', $data['egtArpu']);

            if($data['ltArpu'])
                $where['up_ARPU'][] = array('lt', $data['ltArpu']);

            if($data['egtMou'])
                $where['up_MOU'][] = array('egt', $data['egtMou']);

            if($data['ltMou'])
                $where['up_MOU'][] = array('lt', $data['ltMou']);

            if($data['egtDou'])
                $where['up_DOU'][] = array('egt', $data['egtDou']);

            if($data['ltDou'])
                $where['up_DOU'][] = array('lt', $data['ltDou']);

            if($data['egtYear'])
                $where['up_open_date'][] = array('egt', $data['egtYear']);

            if($data['ltYear'])
                $where['up_open_date'][] = array('lt', $data['ltYear']);

            if($data['egtMonth'])
                $where['up_used_month'][] = array('egt', $data['egtMonth']);

            if($data['ltMonth'])
                $where['up_used_month'][] = array('lt', $data['ltMonth']);

            if($data['abnormal'] != '-1')
                $where['up_abnormal'] = $data['abnormal'];

            // 分页传参数， ['query' => request()->param()] 或者 $parmas = request()->param(); $obj->appends($parmas)->render();
            $lists = Db::name("user_phone")->where($where)->order('up_id asc')->paginate(15, false, ['query' => request()->param()]);

            $this->assign('lists', $lists);
            $this->assign('reqData', $data);
        }
        return $this->fetch();
    }

    public function countScore($list) {
        $vipConf = array(
            '-1' => '0',
            '0' => '4',
            '1' => '8',
            '2' => '12',
            '3' => '16',
            '4' => '20',
            '5' => '24',
            '6' => '28',
            '7' => '30',
        );
        $arpuConf = array(
            '0' => '1',
            '50' => '3',
            '100' => '4',
            '150' => '7',
            '200' => '10',
            '250' => '13',
            '300' => '16',
            '350' => '19',
            '400' => '22',
            '9999' => '25',
        );
        $yearConf = array(
            '0' => '2',
            '1' => '4',
            '2' => '6',
            '3' => '8',
            '4' => '10',
            '5' => '12',
            '6' => '14',
            '7' => '16',
            '8' => '18',
            '9' => '20',
            '10' => '23',
            '11' => '24',
            '15' => '25',
        );
        $mouConf = array(
            '100' => '2',
            '200' => '3',
            '300' => '4',
            '400' => '5',
            '800' => '7',
            '1200' => '9',
            '9999' => '10',
        );
        $douConf = array(
            '100' => '2',
            '500' => '3',
            '1024' => '4',
            '5120' => '5',
            '10240' => '7',
            '20480' => '9',
            '9999' => '10',
        );

        $Score = 0;
        $Score += $vipConf[$list['up_vip']];
        if(isset($arpuConf[$list['up_ARPU']]))
            $arpuScore = $arpuConf[$list['up_ARPU']];
        else {
            if($list['up_ARPU'] >= 400){
                $arpuScore = 25;
            }else{
                foreach ($arpuConf as $key => $value) {
                    if($key > $list['up_ARPU']){
                        $arpuScore = $value;
                        break;
                    }

                }
            }

        }

        $Score += $arpuScore;
        $currYear = '2018-04-01';
        $sDate = date_create($currYear);
        $eDate = date_create($list['up_open_date']);
        $yearDiff = date_diff($eDate, $sDate);
        $years = abs($yearDiff->format("%R%y"));
        if(isset($yearConf[$years]))
            $yearScore = $yearConf[$years];
        else {
            $yearScore = 25;
        }

        $Score += $yearScore;

        if(isset($douConf[$list['up_DOU']]))
            $douScore = $douConf[$list['up_DOU']];
        else {
            if($list['up_DOU']>=20480){
                $douScore = 10;
            }else{
                foreach ($douConf as $key => $value) {
                    if($key > $list['up_DOU']){
                        $douScore = $value;
                        break;
                    }

                }
            }
        }

        $Score += $douScore;
        if(isset($mouConf[$list['up_MOU']]))
            $mouScore = $mouConf[$list['up_MOU']];
        else {
            if($list['up_MOU']>=1200){
                $mouScore = 10; 
            }else{
                foreach ($mouConf as $key => $value) {
                    if($key > $list['up_MOU']){
                        $mouScore = $value;
                        break;
                    }

                }
            }
        }

        $Score += $mouScore;
        return $Score;
    }

}
