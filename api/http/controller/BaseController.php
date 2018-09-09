<?php
namespace http\controller;
use api\crawler\BaseCrawler;
class BaseController{
    /**
     * 静态接口
     */
    static public function run(){
        $base = new BaseCrawler();
        return $base->baseGrab($_SERVER["request_uri"]);
    }
}
?>