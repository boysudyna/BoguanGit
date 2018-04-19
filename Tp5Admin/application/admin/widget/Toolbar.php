<?php

/**
 * Html按钮控制
 * @file   menu.php  
 * @date   2016-9-2 16:18:45 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace application\admin\widget;

class Toolbar {
    // 下拉选项卡
    public function select($name, $arr, $select = '', $prompt = '') {
        $html = "<select name='{$name}' id='{$name}' class='form-control'>";
        if($prompt)
            $html .= '<option value="">'.$prompt.'</option>';

        if(is_array($arr)) {
            foreach($arr as $key => $val) {
                if($select == $key)
                    $html .= "<option value='{$key}' selected>{$val}</option>";
                else
                    $html .= "<option value='{$key}'>{$val}</option>";
            }
        }
        $html .= "</select>";
        return $html;
    }

    // 通jquery的prop方法可以完美的选中或者取消选中元素，使用prop("checked",true) 选中，prop("checked",false)取消选中，通prop("checked") 返回的false或者true判断是否选中。
    // 复选框
    public function checkbox($name, $arr, $select = '') {
        $html = "";
        if($arr) {
            foreach($arr as $key => $val) {
                if($select == $val)
                    $html .= "<input type='checkbox' name='{$name}' value='{$key}' checked/>{$val}";
                else        
                    $html .= "<input type='checkbox' name='{$name}' value='{$key}' />{$val}";
            }
        }
        
        return $html;
    }

    // 单选框
    public function radio($name, $arr, $select = '') {
        $html = "";
        if($arr) {
            foreach($arr as $key => $val) {
                if($select == $val)
                    $html .= "<input type='radio' name='{$name}' value='{$key}' checked/>{$val}";
                else        
                    $html .= "<input type='radio' name='{$name}' value='{$key}' />{$val}";
            }
        }
        
        return $html;
    }
}
