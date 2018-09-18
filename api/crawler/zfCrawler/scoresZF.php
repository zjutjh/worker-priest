<?php
namespace api\crawler\zfCrawler;
use api\crawler\BaseCrawler;

class scoresZF extends BaseCrawler{
    /**
     * curl的cookie临时文件
     * ctr_cookie为0表示未设置cookie
     * 
     * @var string,int
     */
    protected $cookie_file,$ctr_cookie;
    /**
     * 构造函数
     * 
     * @param void
     * @return void
     */
    function __construct(){
        $this->cookie_file=tempnam('../storage/','cookie');
        $this->ctr_cookie=0;
        //echo $this->cookie_file;
    }
    /**
     * 判断是不是json数据，用于在报错时及时返回json报错信息
     * 并结束程序
     * @param string
     */
    private function is_not_json($str){
        return is_null(@json_decode($str));
    }
    /**
     * 数据爬取函数
     *
     * @param string,string,string
     * @return string 
     */
    private function data($fun='get', $data,$url) {
        //定义请求头
    
        $browser = array(
            "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
            "Upgrade-Insecure-Requests" => "1",
            "language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3",
            "Accept"=>"text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "Connection"=> "keep-alive",
            "Accept-Language" => "zh-CN,zh;q=0.8,en;q=0.6",
            "Cache-Control" => "no-cache",
        );
    
        $ch =curl_init();
        if(isset($_REQUEST['timeout']) && is_numeric($_REQUEST['timeout']))
        {
            $timeout = intval($_REQUEST['timeout']);
        }
        else
        {
            $timeout = 15;
        }
        //赋值请求头
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        // curl_setopt($ch, CURLOPT_REFERER, "http://www.ycjw.zjut.edu.cn//logon.aspx");
        $http_header = array();
        foreach ($browser as $key => $value) {
            $http_header[] = "{$key}: {$value}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
        if($fun=="get"){
            if($data!=null)
                $data = http_build_query($data, '', '&');
    
            curl_setopt($ch, CURLOPT_URL, $url);
            if($this->ctr_cookie==0)
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
            else
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        }
        else {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data, '', '&'));
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        }
        //设置post请求
        // curl_setopt($ch, CURLOPT_HEADER, 0);
    
        //param为请求的参数
        $file_contents = curl_exec($ch);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpCode == 0)
        {
            return json_encode( array('code'=>1,'error'=>'服务器错误'));
        }
        // var_dump($cookie_file);
        return $file_contents;
    }
    /*function getCurrentTerm()
     *功能：一定算法识别出当前学期
     *返回当前学期的字符串 
     */
    private function getCurrentTerm() {
        $year = $showtime=date("Y");
        $lastYear = $year-1;
        $nextYear = $year+1;
        $month = $showtime=date("m");
        $month = $month+0;
        if($month>4&&$month<11){
            return $lastYear."/".$year."(2)";
        }
        else if($month>10&&$month<13){
            return $year."/".$nextYear."(1)";
        }
        else{
            return $lastYear."/".$year."(1)";
        }
    }
    /**
     * grab接口
     */
    public function grab(){
         $root = "http://www.gdjw.zjut.edu.cn/";
        // $root = 'http://172.16.19.170/';
        //$root = 'http://172.16.19.168/';

        if(isset($_REQUEST['ip'])) {
            $root = 'http://172.16.19.' . $_REQUEST['ip'] . '/';
        }
        if(!isset($_REQUEST['username']) || !$_REQUEST['password'])
        {
            @unlink ($cookie_file);
            $this->ctr_cookie=0;
            return  json_encode( array('code'=>1,'error'=>'参数错误'));
            //exit;
        }
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];
        $url = $root . 'xtgl/login_getPublicKey.html?time=' . (time() * 1000);//公钥
        $publicKeyData = json_decode(data('get',null, $url), true);//要数据类型的数据，加参数true
        $this->ctr_cookie=1;//请求公钥时会返回cookie
        $exponent = $publicKeyData['exponent'];
        $modules = $publicKeyData['modulus'];
        $output = data('get', null, 'http://weixin.zjut.imcr.me:3000?' . http_build_query(array(
            'password' => $password,
            'exponent' => $exponent,
            'modules' => $modules
        )));
        $enPassword = json_decode($output, true);
        $enPassword = $enPassword['enPassword'];
        $post_field["yhm"] = $username;//用户名
        $post_field["mm"] = $enPassword;//密码
        
        $isRightCapcha = false;

        while(!$isRightCapcha) {
            // 开始验证码识别
            $url = $root . '/kaptcha';
            $captcha = data('get', null, $url);
            $image_base64 = 'data:image/jpeg;base64,' . base64_encode($captcha);
            $tensorUrl = 'http://172.16.32.50/yzm';
            $tensorResult = json_decode(data('post', array(
                'img_base64' => $image_base64
            ), $tensorUrl), true);
            $yzm = $tensorResult['data'];
            $post_field["yzm"] = $yzm;//验证码
        
            $url = $root."xtgl/login_slogin.html";
            //$url="http://172.16.7.86///logon.aspx";
            $result = data('post',$post_field, $url);
            if(preg_match_all('/验证码输入错误/', $result, $arr)!=0){
                function create_uuid($prefix = ""){    //可以指定前缀
                    $str = md5(uniqid(mt_rand(), true));   
                    $uuid  = substr($str,0,8) . '-';   
                    $uuid .= substr($str,8,4) . '-';   
                    $uuid .= substr($str,12,4) . '-';   
                    $uuid .= substr($str,16,4) . '-';   
                    $uuid .= substr($str,20,12);   
                    return $prefix . $uuid;
                }
                // $uuid = create_uuid();
                // $fp = fopen('/opt/www/student/student/failImages/' . $uuid . '.jpg', 'w');
                // @fwrite($fp, $captcha);
                // fclose($fp);
            } else {
                $isRightCapcha = true;
            }
        }

        if(preg_match_all('/用户名或密码不正确，请重新输入/', $result, $arr)!=0){
            echo json_encode( array('status'=>'error','msg'=> '用户名或密码错误'));
            @unlink ($cookie_file);
            exit;
        }
        if ($result !== "") {
            echo json_encode( array('status'=>'error','msg'=> '服务器错误'));
            @unlink ($cookie_file);
            exit;
        }

        // 登陆成功，接下来获取成绩
        $year = isset($_REQUEST['year']) ? $_REQUEST['year'] : '';
        $term = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';

        //xqm 学期名
        // 3 第一学期
        // 12 第二学期
        // 16 短学期
        $termNum = array(
        	'3' => '1',
        	'12' => '2',
        	'16' => '短'
        );
        $res = data('post', array(
            "xnm"=>$year,
            "xqm"=>$term,
            "queryModel.showCount"=>"150"
        ), $root. 'cjcx/cjcx_cxDgXscj.html?doType=query');
        $result = json_decode($res, true);
        foreach ($result['items'] as $key => $value) {
            $result['items'][$key]['term'] = $value['xnm'] . '/' . ($value['xnm'] + 1) . '(' . $termNum[$value['xqm']] . ')';
            $result['items'][$key]['name'] = $value['kcmc'];
            $result['items'][$key]['classprop'] = $value['kcbj'];
            $result['items'][$key]['classscore'] = $value['cj'];
            $result['items'][$key]['classhuor'] = $value['cj'];
            $result['items'][$key]['classcredit'] = isset($value['xf']) ? $value['xf'] : '';
            $result['items'][$key]['classcredit'] = isset($value['xf']) ? $value['xf'] * 16 : '';
            unset($result['items'][$key]['queryModel']);
            unset($result['items'][$key]['userModel']);
        }
        echo json_encode(array(
            'status' => 'success',
            'msg' => $result['items']
        ));
        @unlink ($cookie_file);
    }
}
?>