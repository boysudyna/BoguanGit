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
class GuardController extends CommonController {

    public function index() {
        if(request()->param()){
            $where = array();
            $data = input('param.');
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
        }
        return $this->fetch();
    }

}
