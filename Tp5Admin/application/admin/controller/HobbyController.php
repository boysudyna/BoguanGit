<?php

/**
 *  手机偏好数据查询
 * @file   ConfigController.php  
 * @date   2016-10-10 9:39:19 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace application\admin\controller;

use think\Request;
class HobbyController extends CommonController {

    public function index() {
        $where = array();
        $data = input('param.');
        if($data['phone'])
            $where['up_phone'] = $data['phone'];

        if($data['nums'])
            $where['up_nums'][] = array('egt', $data['nums']);

        if($data['enums'])
            $where['up_nums'][] = array('lt', $data['enums']);

        if($data['apple'])
            $where['up_apple'] = array('egt', $data['apple']);

        if($data['sx'])
            $where['up_sx'] = array('egt', $data['sx']);

        if($data['hw'])
            $where['up_hw'] = array('egt', $data['hw']);

        if($data['oppo'])
            $where['up_oppo'] = array('egt', $data['oppo']);

        if($data['xm'])
            $where['up_xm'] = array('egt', $data['xm']);

        // 分页传参数， ['query' => request()->param()] 或者 $parmas = request()->param(); $obj->appends($parmas)->render();
        $lists = db("user_phonelog")->where($where)->order('up_id asc')->paginate(10, false, ['query' => request()->param()]);
        $this->assign('lists', $lists);
        $this->assign('reqData', $data);
        return $this->fetch();
    }

}
