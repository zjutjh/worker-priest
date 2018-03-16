<?php
namespace api\crawler;
use api\crawler\registerCrawler;

class BaseCrawler{
    /**
     * curl变量
     * 
     * @var instance
     */
    protected $ch;
    /**
     * ioc容器
     * 
     * @var instance
     */
    protected $__di;
    /**
     * 构造函数
     * 
     * @param void
     * @return void
     */
    function __construct(){
        $this->__di=new registerCrawler;
    }
    /**
     * 注入函数
     * 
     * @param array
     * @return void
     */
    public function setDi(Array $array){
        foreach($array as $key=>$definition){
            $this->__di->set($key,$definition);
        }
    }
    /**
     * 模拟get请求，请求数据
     * 
     * @param void
     * @return void
     */
    public function get(){
        header('Access-Control-Allow-Origin:*',
        'Content-Type:application/json;charset=UTF-8');//api json文件
        $header[] = "Expect:";
        $this->ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_URL, "https://172.16.7.100/default.aspx");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    }
    /**
     * 模拟post请求，请求数据
     * 
     * @param string
     * @return void
     */
    public function post($url){
        header('Access-Control-Allow-Origin:*',
        'Content-Type:application/json;charset=UTF-8');//api json文件
        $this->ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST,1);
    }
    /**
     * 基础的登录函数
     * 传入的
     * 
     * @param string
     * @return array
     */
    public function baseLogin($string,$array){
        $di=new registerCrawler;
        $instance=$di->get($string);
        $instance->login($array);
    }
    /**
     * 数据抓取函数
     * 传入的闭包返回array，第一个值放的是注册的闭包（抓取逻辑）名字
     * 剩下的依次是爬取逻辑需要的变量，注意其顺序要和注册的闭包的参数顺序一致
     * 
     * @param closure
     * @return array
     */
    //public function baseGrab($param){
    //    $array=call_user_func($param);//功能函数返回的数组
    //    $di=new registerCrawler;
    //    $definition=$di->get($array[0]);//已经注册的爬取逻辑
    //    array_shift($array);//这个函数删去传入数组的首值，并返回被删掉的那个函数
    //    return call_user_func_array($definition,$array);
    //}
    public function baseGrab($string,$array){
        $di=new registerCrawler;
        $instance=$di->get($string);
        $instance->grab($array);
    }
}
?>