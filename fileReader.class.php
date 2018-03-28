<?php
/**
* 超大文件操作类
* 配合使用SplFileObject类
*/

class FileReader{  
    private $file;  
    private $instance;  
  
    /** 
    * 初始化BigFile类 
    */  
    public function __construct($file){  
        $this->file = $file;  
        $this->instance = new SplFileObject($file,'r');  
    }  
  
    /** 
    * 获取大文件的总行数 
    * @access public 
    * @return int 返回行数 
    */  
    public function lines(){  
        /* 
        //这种方式效率略低 
        $this->instance->seek(filesize($this->file)); 
        return $this->instance->key(); 
        */  
        $sum = 0;  
        while($this->instance->valid()){  
            $data = $this->instance->fread(1024*1024*2);   //每次读取2M  
            $num = substr_count($data,"\n");    //计算换行符出现的次数  
            $sum += $num;  
        }  
        return $sum;  
    }  
  
    /** 
    * 读取指定行区间的数据 
    * @access public 
    * @param $start 开始的行号 
    * @param $num 读取的行数 
    * @return $buf 返回读取到的行数组 
    */  
    public function slice($start,$num){  
        if($start<=0 || $num<=0 ||  !is_int($start) || !is_int($num)){  
            throw new Exception("参数不正确，请输入大于0的整数");  
        };  
        $buf = array();  
        $this->instance->seek($start-1);             //SplFileObject的seek方法索引从0开始  
        while($num>0 && $this->instance->valid()){  
            $buf[] = $this->instance->current();  
                        $this->instance->next();  
            $num--;  
        }  
        return $buf;  
    }  
  
    /** 
    * 读取头部前N条数据 
    * @access public 
    * @param $num 读取的行数 
    * @return $buf 返回读取到的行数组 
    */  
    public function head($num){  
        if($num<=0 || !is_int($num)){  
            throw new Exception("参数不正确，请输入大于0的整数");  
        };  
        $buf = array();  
        $this->instance->seek(0);             //SplFileObject文件行数索引从0开始  
        while($num>0 && $this->instance->valid()){  
            $buf[] = $this->instance->fgets();  
            $num--;  
        }  
        return $buf;  
    }  
  
    /** 
    * 读取末尾N条数据 
    * @access public 
    * @param $num 读取的行数 
    * @return $buf 返回读取到的行数组 
    */  
    function tail($num){  
        if($num<=0 || !is_int($num)){  
            throw new Exception("参数不正确，请输入大于0的整数");  
        };  
        $fp = fopen($this->file,"r");  
        $pos = -2;  
        $eof = "";  
        $head = false;   //当总行数小于Num时，判断是否到第一行了  
        $buf = array();  
        while($num>0){  
            while($eof != "\n"){  
                if(fseek($fp, $pos, SEEK_END)==0){    //fseek成功返回0，失败返回-1  
                    $eof = fgetc($fp);  
                    $pos--;  
                }else{                       //当到达第一行，行首时，设置$pos失败  
                    fseek($fp,0,SEEK_SET);  
                    $head = true;            //到达文件头部，开关打开  
                    break;  
                }  
  
            }  
            array_unshift($buf,fgets($fp));  
            if($head){ break; }               //这一句，只能放上一句后，因为到文件头后，把第一行读取出来再跳出整个循环  
            $eof = "";  
            $num--;  
        }  
        fclose($fp);  
        return $buf;  
    }  
  
    public function __destruct(){  
        unset($this->file);  
        unset($this->instance);  
    }  
}