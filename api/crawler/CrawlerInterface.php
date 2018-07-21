<?php
namespace api\crawler;

interface CrawlerInterFace{
    public  function login($username,$passward);
    public  function grab(Array $array);
}
?>
