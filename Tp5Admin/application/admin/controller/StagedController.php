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
            if($data['phone'])
                $where['up_phone'] = $data['phone'];

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

            if($data['abnormal'] != '-1')
                $where['up_abnormal'] = $data['abnormal'];

            // 分页传参数， ['query' => request()->param()] 或者 $parmas = request()->param(); $obj->appends($parmas)->render();
            $lists = Db::name("user_phone")->where($where)->order('up_id asc')->paginate(15, false, ['query' => request()->param()]);

            $this->assign('lists', $lists);
            $this->assign('reqData', $data);
        }
        return $this->fetch();
    }

}
