<?php
namespace api\crawler;
use api\crawler\DiAwareInterface;

class BaseCrawler{
    /**
     * 页面HTML
     * 
     * @var string
     */
    protected $html;
    function __construct(){

    }
    /**
     * 模拟get请求，请求数据
     * 
     * @param void
     * @return void
     */
    function get(){
        header('Access-Control-Allow-Origin:*',
        'Content-Type:application/json;charset=UTF-8');//api json文件
        $header[] = "Expect:";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_URL, "https://172.16.7.100/default.aspx");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $html=curl_exec($ch);
        	//正则cookie
        preg_match('/Set-Cookie:(.*);/iU',$html,$str); //正则匹配
        $cookie=$str[1];
        	//正则验证码
        $pattern = '/src="images\/(.*?).gif"/is';
        preg_match_all($pattern, $html, $matches);
        curl_close($ch);
        $code=$matches[1][5].$matches[1][6].$matches[1][7].$matches[1][8];
        	//正则viewstate
        $pattern = '/<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="(.*?)" \/>/is';
        preg_match_all($pattern, $html, $matches);
        $res= $matches[1][0];
        $viewstate=urlencode($res);
        	//正则eventvalidation
        $pattern = '/<input type="hidden" name="__EVENTVALIDATION" id="__EVENTVALIDATION" value="(.*?)" \/>/is';
        preg_match_all($pattern, $html, $matches);
        $res= $matches[1][0];
        $event=urlencode($res);

    }
    /**
     * 模拟post请求，请求数据
     * 
     * @param void
     * @return void
     */
    function post(){
        header('Access-Control-Allow-Origin:*',
        'Content-Type:application/json;charset=UTF-8');//api json文件
        $body="__LASTFOCUS="."&__VIEWSTATE=$viewstate"."&__EVENTTARGET="."&__EVENTARGUMENT="."&__EVENTVALIDATION=$event"."&UserLogin%3AtxtUser=$username"."&UserLogin%3AtxtPwd=$password"."&UserLogin%3AddlPerson=%BF%A8%BB%A7"."&UserLogin%3AtxtSure=$code"."&UserLogin%3AImageButton1.x=0"."&UserLogin%3AImageButton1.y=0";
        $post=$body;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://172.16.7.100/default.aspx");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);//使用cookie
        $html=curl_exec($ch);
        curl_close($ch);
    }
}
?>