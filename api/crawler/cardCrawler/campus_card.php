<?php

namespace api\crawler\cardCrawler;
use api\crawler\BaseCrawler;
use api\crawler\registerCrawler;

//include_once('./simple_html_dom.php');
class campus_card extends BaseCrawler{
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
        //饭卡余额查询接口
        header('Access-Control-Allow-Origin:*',
        'Content-Type:application/json;charset=UTF-8');
        $cmd = false;
        $username = '';
        if($_GET['id']!=null){
        $username=$_GET['id'];
        $nameConfirm = $_GET['name'];
        if (!$_GET['password']) {
        $password = substr($username,6,6);
        }
        else {
        $password = $_GET['password'];
        }
        }
        else {
        // 命令行方式，这里是判断密码是否正确吗？
        $cmd = true;
        $username=$argv[1];//指向在DOS命令行中执行程序名后的第一个字符串。
        if (!$argv[2]) {
        $password = substr($username,6,6);
        }
        else {
        $password = $argv[2];//指向执行程序名后的第二个字符串。
        }
        }

        //下面模拟首页 获取相关信息
        $header[] = "Expect:";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点  FALSE 禁止 cURL 验证对等证书（peer's certificate）。要验证的交换证书可以在 CURLOPT_CAINFO 选项中设置，或在 CURLOPT_CAPATH中设置证书目录。
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上设置为 1    CURLOPT_SSL_VERIFYHOST是检查服务器SSL证书中是否存在一个公用名(common name)。译者注：公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)。 设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配。 0 为不检查名称。 在生产环境中，这个值应该是 2（默认值）。
        curl_setopt($ch, CURLOPT_URL, "https://172.16.7.100/default.aspx");  // 需要获取的 URL 地址，也可以在curl_init() 初始化会话的时候获取。
        curl_setopt($ch, CURLOPT_HEADER, 1); //将标题传递给数据流
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);//设置 HTTP 头字段的数组。格式： array('Content-type: text/plain', 'Content-length: 100')
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//????   TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出。
        $html=curl_exec($ch);
        //正则cookie
        preg_match('/Set-Cookie:(.*);/iU',$html,$str); //正则匹配
        $cookie=$str[1];
        //正则验证码
        $pattern = '/src="images\/(.*?).gif"/is';
        preg_match_all($pattern, $html, $matches);//preg_match_all 用于执行一个全局正则表达式匹配
        curl_close($ch);
        $code=$matches[1][5].$matches[1][6].$matches[1][7].$matches[1][8];//matches()方法用于检测字符串是否匹配给定的正则表达式
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
        $html=curl_exec($ch);// 抓取URL并把它传递给浏览器   

        //模拟查询
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://172.16.7.100/Cardholder/AccBalance.aspx");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//用于访问https站点
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//同上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_COOKIE,$cookie);//使用cookie
        $html=curl_exec($ch);
        //姓名
        $pattern='/id="lblName0">(.*?)<\/span>/is';
        preg_match_all($pattern,$html,$matches);
        if ($cmd) {
        echo $matches[1][0];
        }
        else {
        $name = iconv('gbk', 'utf-8', $matches[1][0]);
        }
        //余额
        $pattern='/id="lblOne0" class="item1">(.*?)<\/span>/is';
        preg_match_all($pattern,$html,$matches);
        if ($cmd) {
        echo $matches[1][0];
        }
        else {
        $balance = $matches[1][0];
        }

        $data =  array('balance' => $balance);
        if (!$cmd) {
        if ($nameConfirm == $name) {
        echo json_encode($data);
        }
        else {
        echo json_encode($name);
        }
        exit();
        }
    }
}
?>
