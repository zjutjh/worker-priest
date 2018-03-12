<?php
namespace api\crawler;

class BaseCrawler{
    /**
     * curl变量
     * 
     * @var instance
     */
    protected $ch;
    /**
     * 模拟get请求，请求数据
     * 
     * @param instance
     * @return void
     */
    function get(){
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
     * @param instance,string
     * @return void
     */
    function post($url){
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
     * 
     * @param ~
     * @return void
     */
    function baselogin($viewstate,$event,$username,$password,$code){
        $body="__LASTFOCUS="."&__VIEWSTATE=$viewstate"."&__EVENTTARGET="."&__EVENTARGUMENT="."&__EVENTVALIDATION=$event"."&UserLogin%3AtxtUser=$username"."&UserLogin%3AtxtPwd=$password"."&UserLogin%3AddlPerson=%BF%A8%BB%A7"."&UserLogin%3AtxtSure=$code"."&UserLogin%3AImageButton1.x=0"."&UserLogin%3AImageButton1.y=0";
        $post=$body;
        $this->ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://172.16.7.100/default.aspx");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);//使用cookie
    }
    /**
     * 数据抓取函数
     * 
     * @param closure
     * @return mixed
     */
    function grab($param){
        
    }
}
?>