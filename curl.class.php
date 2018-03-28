<?php
/**
 * @author sudyna
 * @since 2018-03-03
 *
 */
class CurlExchange {    
    private static  $url = ''; // 访问的url
    private static $refUrl = ''; // referer url
    private static $data = array(); // 可能发出的数据 post,put
    private static $method; // 访问方式，默认是GET请求
    
    public static function send($url, $data = array(), $method = 'get') {
        if (!$url) 
            exit('url can not be null');
        
        self::$url = $url;
        self::$method = $method;
        $urlArr = parse_url($url);
        self::$refUrl = $urlArr['scheme'] .'://'. $urlArr['host'];
        self::$data = $data;
        if ( !in_array(self::$method, array('get', 'post', 'put', 'delete')))
            exit('error request method type!');

        $funName = $method . 'Request';
        $func = self::$funName(); // $func = call_user_func(array(self, $funName));
        return $func;
    }

    /**
     * 基础发起curl请求函数
     * @param int $is_post 是否是post请求
     */
    private static function doRequest($is_post = 0, $json = 0) {
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL, self::$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 设置超时
        // curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        // curl_setopt($ch, CURLOPT_REFERER, self::$refUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        if($is_post == 1) 
            curl_setopt($ch, CURLOPT_POST, $is_post);//post提交方式

        if (!empty(self::$data)) {
            self::$data = self::dealPostData(self::$data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, self::$data);
        }

        if ($json) {
            $headers = array(
                "Content-type: application/json;charset='utf-8'",
                "Accept: application/json",
            );

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
        }
        
        // $info = curl_getinfo($ch); print_r($info); // 显示请求的相关信息

        $data = curl_exec($ch);//运行curl    
        curl_close($ch);
        return $data;

    }
    /**
     * 发起get请求
     */
    public static function getRequest() {
        return self::doRequest(0);
    }

    /**
     * 发起post请求
     */
    public static function postRequest() {
        return self::doRequest(1,1);
    }

    /**
     * 处理发起非get请求的传输数据
     * 
     * @param array $postData
     */
    public static function dealPostData($postData) {
        return $postData;
        if (!is_array($postData)) 
            exit('post data should be array');

        foreach ($postData as $k => $v) {
            $o .= "$k=" . urlencode($v) . "&";
        }

        $postData = substr($o, 0, -1);
        return $postData;
    }
    
}

// $res = CurlExchange::send('http://www.baidu.com', array('ip' => '61.142.206.145'), 'post');
// var_dump($res);die();
