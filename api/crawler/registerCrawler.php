<?php
namespace api\crawler;

class registerCrawler{
    /**
     * IOC容器
     * 
     * @var array
     */
    static public $register=array();
    /**
     * 注册函数
     * 
     * 注册的抓取逻辑闭包要返回一个数组
     * 
     * @param string,closure
     * @return void
     */
    public function set($name,$definition){
        self::$register[$name]=$definition;
    }
    /**
     * 获取实例/闭包
     * 是否有获取实例的必要?
     * 
     * @param string
     * @return instance
     */
    public function get($name){
        if (isset(self::$register[$name])){ 
            $definition = self::$register[$name];
        }
        else{
            throw new Exception("Service '" . $name . "' wasn't found in the dependency injection container'");
        }
        if(is_object($instance=call_user_func($definition))){
            return $instance;//通过这个函数使用闭包函数
        }
        else {
            return $instance=$definition;//返回实例或闭包
        }
    }
}
?>
