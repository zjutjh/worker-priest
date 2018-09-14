<?php
namespace api\crawler;
use api\crawler\registerCrawler;

class BaseCrawler{
    /**
     * ioc实例
     */
    protected $_di;
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
     * 基础的登录函数
     * 从容器里召唤逻辑
     * 
     * 出来吧，召唤兽！！！
     * 
     * @param string,array
     * @return void
     */
    public function baseLogin($string,$array){
        $di=new registerCrawler;
        $instance=$di->get($string);
        $instance->login($array);
    }
    /**
     * 数据抓取函数
     * 
     * 从容器里召唤逻辑
     * 
     * 出来吧，召唤兽! ! !
     * 
     * @param string,array
     * @return array
     */
    //public function baseGrab($param){
    //    $array=call_user_func($param);//功能函数返回的数组
    //    $di=new registerCrawler;
    //    $definition=$di->get($array[0]);//已经注册的爬取逻辑
    //    array_shift($array);//这个函数删去传入数组的首值，并返回被删掉的那个函数
    //    return call_user_func_array($definition,$array);
    //}
    public function baseGrab($string){
        $di=new registerCrawler;
        $instance=$di->get($string);
        if(!is_null(@json_decode($instance))){
	    return $instance;
	}
	return $instance->grab();
    }
}
?>
