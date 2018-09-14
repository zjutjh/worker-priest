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
     * 注入函数
     * 
     * @param array
     * @return void
     */
    public function setDi(Array $array){
        foreach($array as $key=>$definition){
            $this->__di->set($key,$definition);
        }
    }
    public function grab(){
        header("Access-Control-Allow-Origin: *");//使用通配符*，允许所有跨域访问，所以跨域访问成功。
        //饭卡当日记录查询接口
        include_once('./simple_html_dom.php');
            
        $username=$_REQUEST['username'];
        $password=$_REQUEST['password'];
            
        $website_url = "https://172.16.10.102/";
            
        //$username=$argv[1];
            
        //下面模拟首页 获取相关信息
        $header[] = "Expect:";//请求头
        $ch = curl_init();//创建一个新cURL资源 
        //设置URL和相应的选项
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_URL, $website_url."default.aspx");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $html=curl_exec($ch);//抓取URL并把它传递给浏览器
        //正则cookie
        preg_match('/Set-Cookie:(.*);/iU',$html,$str); //正则匹配
        $cookie=$str[1];
        //正则验证码
            
        $pattern = '/src="images\/(\d).gif"/is';
        preg_match_all($pattern, $html, $matches);
            
        curl_close($ch);//关闭cURL资源，并释放系统资源
            
        $code=$matches[1][0].$matches[1][1].$matches[1][2].$matches[1][3];
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
        curl_setopt($ch, CURLOPT_URL, $website_url."default.aspx");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);//使用cookie
        $html=curl_exec($ch);
            
        //Balance Start
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $website_url."Cardholder/AccBalance.aspx");
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
        $balance = null;
        if(preg_match_all($pattern,$html,$matches)!=0)
        {
            preg_match_all($pattern,$html,$matches);
            $class['姓名'] = $matches[1][0];
            //余额
            $pattern='/id="lblOne0" class="item1">(.*?)<\/span>/is';
            preg_match_all($pattern,$html,$matches);
            $class['卡余额'] = $matches[1][0];
            //var_dump($class);
            $balance = $class;
            unset($class);
        }
        else {
            echo json_encode( array('status'=>'error','msg'=>'用户名或密码错误'));
            return;
        }
        //Balance End
        
        //模拟查询
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $website_url."Cardholder/QueryCurrDetailFrame.aspx");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);//使用cookie
        $html=curl_exec($ch);
        $html=iconv("GBK","UTF-8",$html);
        //echo $html;
        $pattern='/<td[^>]*>(.*?)(?=<\/td>)/i';
        if(preg_match_all($pattern,$html,$matches)!=0)
        {
            //echo $matches[1][0];
            //var_dump($matches[1]);
            //echo ((sizeof($matches[1])-7)/11-1);
    
            $tdname=array(
                    '流水号',
                    '账号',
                    '卡片类型',
                    '交易类型',
                    '商户',
                    '站点',
                    '终端号',
                    '交易额',
                    '到账时间',
                    '钱包名称',
                    '卡余额'
            );
            $k = 0;
            $t = 0;
            for($i=15;$i<sizeof($matches[1])-3;$i++) {
                $contentName=$tdname[$t];
                $class[$k][$contentName] =$matches[1][$i];
                if($t<10)
                    $t++;
                else
                {
                    $t=0;
                    $k++;
                }
            }
            //var_dump($class);
        
            if(empty($class)==TRUE)
                echo json_encode( array('status'=>'success','msg'=>array('余额' => $balance, '今日账单' => array('num'=>0,'msg' => '没有相关信息'))));
            else
                echo json_encode( array('status'=>'success','msg'=>array('余额' => $balance, '今日账单' => array('num'=>((sizeof($matches[1])-7)/11-1),'msg' => $class))));
        }
    else
        echo json_encode( array('status'=>'error','msg'=>'用户名或密码错误'));
    }    
}
?>
