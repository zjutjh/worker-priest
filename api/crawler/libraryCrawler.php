<?php
namespace api\crawler;
use api\crawler\BaseCrawler;
use api\crawler\registerCrawler;
use api\crawler\CrawlerInterface;

class libraryCrawler extends BaseCrawler implements CrawlerInterface{
    /**
     * 
     */
    protected $__array=[];
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
    public function setDi(){
        foreach($this->__array as $key=>$definition){
            $this->__di->set($key,$definition);
        }
    }
    /**
     * 图书馆登录函数
     * 
     * @param closure
     * @return void
     */
    public function login(){
        
    }
}
?>