<?php
namespace api\crawler;
use api\crawler\DiAwareInterface;

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
    public static function register($name,Closure $definition){
    static::$register[$name]=$definition;
    }
    /**
     * 获取实例&自动注入
     * 
     * @param string
     * @return instance
     */
    public function get($name){
        if (isset($this->_service[$name])){ 
            $definition = $this->service[$name]; 
        }
        else{
            throw new Exception("Service '" . name . "' wasn't found in the dependency injection container'");
        }
        if(is_object($definition)){
            $instance=call_user_func($definition);
        }

        // 如果实现了DiAwareInterface这个接口，自动注入
        if (is_object($instance)) {
            if ($instance instanceof DiAwareInterface) {
                $instance->setDI($this);
            }
        }
        return $instance;
    }
}
?>