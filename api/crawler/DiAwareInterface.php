<?php
namespace api\crawler;

/**
 * 用以实现自动注入的接口
 * 
 */
interface DiAwareInterface{
    
    public function set();
    public function get();
}
?>