<?php
namespace api\crawler\cardCrawler;
use api\crawler\BaseCrawler;
use api\crawler\registerCrawler;

include_once('./simple_html_dom.php');
class cardBalance extends BaseCrawler{
    /**
     * curl变量
     * 
     * @var string,int 
     */
    protected $cookie_file,$ctr_cookie;
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
		$this->cookie_file=tempnam('../storage/','cookie');
        $this->ctr_cookie=0;
    }
    /**
     * 数据爬取函数
     * 
     */
    public function grab(){
        //饭卡当日记录查询接口
        $username=$_REQUEST['username'];
        $password=$_REQUEST['password'];
        //$username=$argv[1];
        
        //下面模拟首页 获取相关信息
        $header[] = "Expect:";
        $ch = curl_init();
        curl_setopt ( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_URL, "https://172.16.7.100/default.aspx");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
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
        
        //下面模拟登录
        //$password=substr($username,6,6);
        $body="__LASTFOCUS="."&__VIEWSTATE=$viewstate"."&__EVENTTARGET="."&__EVENTARGUMENT="."&__EVENTVALIDATION=$event"."&UserLogin%3AtxtUser=$username"."&UserLogin%3AtxtPwd=$password"."&UserLogin%3AddlPerson=%BF%A8%BB%A7"."&UserLogin%3AtxtSure=$code"."&UserLogin%3AImageButton1.x=0"."&UserLogin%3AImageButton1.y=0";
        $post=$body;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://172.16.7.100/default.aspx");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);//使用cookie
        $html=curl_exec($ch);
        
        //模拟查询
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://172.16.7.100/Cardholder/AccBalance.aspx");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);//使用cookie
        $html=curl_exec($ch);
        $html=iconv("GBK","UTF-8",$html);
        //echo $html;
        
        //姓名
        $pattern='/id="lblName0">(.*?)<\/span>/is';
        
        if(preg_match_all($pattern,$html,$matches)!=0)
        {
	       preg_match_all($pattern,$html,$matches);
	       $class[0]['姓名'] = $matches[1][0];
	       //余额
	       $pattern='/id="lblOne0" class="item1">(.*?)<\/span>/is';
	       preg_match_all($pattern,$html,$matches);
	       $class[0]['卡余额'] = $matches[1][0];
	       //var_dump($class);
	       echo json_encode( array('status'=>'success','msg'=>$class));
        }
        else
            echo json_encode( array('status'=>'error','msg'=>'用户名或密码错误'));

    }
}
?>
