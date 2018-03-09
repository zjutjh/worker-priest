<?php
namespace api\crawler;

class registerCrawler{
    /**
     * IOC容器
     * 
     * @var array
     */
    protected static $register=array();
    /**
     * 注册函数
     * 
     * @param string,closure
     * @return void
     */
    public static function set($name,Closure $definition){
    static::$register[$name]=$definition;
    }
    /**
     * 获取实例&自动注入
     * 
     * @param string
     * @return instance
     */
    public function get($name){
        if (isset(static::$register[$name])){ 
            $definition = static::$register[$name]; 
        }
        else{
            throw new Exception("Service '" . name . "' wasn't found in the dependency injection container'");
        }
        if(is_object($definition)){
            $instance=call_user_func($definition);//通过这个函数使用闭包函数
        }
        return $instance;
    }
}
?>