<?php
namespace api\crawler\libraryCrawler;
use api\crawler\BaseCrawler;
use api\crawler\registerCrawler;

class library_search extends BaseCrawler{
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
     * @param string,array,string
     * @return string
     */
    private function data($fun='get', $data,$url) {
        //global $cookie_file,$ctr_cookie;
        //定义请求头
        $browser = array(
        "user_agent" => "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6 (.NET CLR 3.5.30729)",
        "language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3",
        "Accept"=>"text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
        "Accept-Encoding"=>"gzip, deflate",
        "Connection"=> "keep-alive"
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
        curl_setopt($ch, CURLOPT_REFERER, "http://210.32.205.60/Search.aspx");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "User-Agent: {$browser['user_agent']}",
                "Accept-Language: {$browser['language']}",
                "Accept: {$browser['Accept']}",
                "Accept-Encoding: {$browser['Accept-Encoding']}",
                "Connection:{$browser['Connection']}"
            ));
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
            return json_encode( array('code'=>'0','error'=>'服务器错误'));
        }
        //curl_close($ch);
        // var_dump($cookie_file);
        return $file_contents;
    }
    /**
     * 获取用以post的data
     * 
     * @param string,string,mixed,array
     * 
     */
    private function post_data($post_field,$url,$data = null)
    {
        if($data)
        {
            $contents = $data;
        }
        else
        {
            $contents = $this->data('get',null,$url);
            @unlink($this->cookie_file);
        }
        if(!$this->is_not_json($contents)){//检查是否报错
            return $contents;
        }
        //var_dump($contents);
        $post_field_name=array("__VIEWSTATE","__EVENTTARGET","__EVENTARGUMENT","__VIEWSTATEGENERATOR","__VIEWSTATEENCRYPTED","__EVENTVALIDATION");

        for($i=0;$i<count($post_field_name);$i++){

            unset($matches);
            $name=$post_field_name[$i];
            preg_match('/<input\s*type="hidden"\s*name="'.$name.'"\s*id="'.$name.'"\s*value="(.*?)"\s*\/>/i', $contents, $matches);
            //var_dump($matches[1]);
            if($matches==null)
                $post_field[$name]="";
            else
                $post_field[$name]=$matches[1];
        }
       // var_dump($post_field);
        return $post_field;
    }
    /**
     * 查询函数
     * @param
     */
    private function search_book($wd, $page = null, $type = 1)
    {
        if(!$wd)
        {
            return false;
        }
        $url = "http://210.32.205.60/Search.aspx";

        $post_field['ctl00$ContentPlaceHolder1$TBSerchWord']=$wd;

        if($page)
        {
            $post_field['ctl00$ScriptManager1']='ctl00$ContentPlaceHolder1$UpdatePanel1|ctl00$ContentPlaceHolder1$AspNetPager1';
            $post_field['ctl00$ContentPlaceHolder1$AspNetPager1_input'] = $page;
            if(preg_match_all('/<\/table>\|([\w\W]*?)(Quick Search)/', $this->search_book($wd, null, 2), $temp))
            {
                if(preg_match_all('/\d+\|hiddenField\|([\w\W]*?)\|([\w\W]*?)\|/', $temp[1][0], $t))
                {
                    foreach ($t[1] as $key => $value) {
                        $post_field[$value] = $t[2][$key];
                    }
                }
            }
            $post_field['__EVENTTARGET']='ctl00$ContentPlaceHolder1$AspNetPager1';
        }
        else
        {
            $post_field['ctl00$ScriptManager1']='ctl00$ContentPlaceHolder1$UpdatePanel1|ctl00$ContentPlaceHolder1$SearchButton';
            $post_field['ctl00$ContentPlaceHolder1$SearchButton.x']='23';
            $post_field['ctl00$ContentPlaceHolder1$SearchButton.y']='21';
            $post_field =$this->post_data($post_field,$url);
            if(!$this->is_not_json($post_field)){//检查是否报错
                //@unlink($this->cookie_file);
                //$this->ctr_cookie=0;
                return $post_field;
            }
        }
        $post_field["__ASYNCPOST"]='true';
        $result = $this->data('post',$post_field,$url);
        if(!$this->is_not_json($result)){//检查是否报错
            return $result;
        }
        $this->ctr_cookie=1;//获取cookie
        if($type == 2)
        {
            return $result;
        }

        $class = null; 
        unset($post_field);

        $preg = '/<table[\w\W]*? style="border-style: none; padding: 0px; margin: 0px; width: 1000px; height:88px">[\w\W]*?>([\w\W]*?)<\/table>/';
        if(preg_match_all($preg, $result, $arr)!=0){//若抓到数据
            $class['wd'] = $wd;
            if($page)
            {
                $class['page'] = $page;
            }
            else
            {
                $class['page'] = 1;
            }
            if(preg_match_all("/为您找到相关结果([\d]+)条/", $result, $temp) == 1) {
                if($temp[1][0] == "0")
                {
                    @unlink ($this->cookie_file);
                    return json_encode( array('code'=>'0','error'=>'没有你想要的图书'));
                }
                else
                {
                    $class['num'] = $temp[1][0];
                }
            }

            foreach ($arr[1] as $key => $value) {
                if(preg_match_all('/<a id="ctl00_ContentPlaceHolder1[\w\W]*?#3366CC;">([\w\W]*?)<\/a>/', $value, $title))
                {
                    $class['book_list'][$key]['title'] = strip_tags($title[1][0]);//去除html标记
                }
                if(preg_match_all('/<a id="ctl00_ContentPlaceHolder1[\w\W]*?href="[\w\W]*?([\d]*?)"[\w\W]*?<\/a>/', $value, $title))
                {
                    $class['book_list'][$key]['bookid'] = strip_tags($title[1][0]);
                }
                if(preg_match_all('/<span id="ctl00_ContentPlaceHolder1[\w\W]*?#000000">([\w\W]*?)<\/span>/', $value, $book_info))
                {
                    $class['book_list'][$key]['call_number'] = strip_tags($book_info[1][0]);
                    $class['book_list'][$key]['author'] = strip_tags($book_info[1][1]);
                    $class['book_list'][$key]['publisher'] = strip_tags($book_info[1][2]);
                    $class['book_list'][$key]['publish_date'] = strip_tags($book_info[1][3]);
                    $class['book_list'][$key]['topic'] = strip_tags($book_info[1][4]);
                    $class['book_list'][$key]['language'] = strip_tags($book_info[1][5]);
                }

            }
            
            if(empty($class)==TRUE) {
                return json_encode( array('code'=>'0','error'=>'没有相关信息'));
            }
            else {
                return $class;
            }
        }
        else {//若没有抓到数据
            return json_encode( array('code'=>'0','error'=>'没有相关信息'));
        }
    }
    /**
     * 数据抓取接口
     * 
     */
    public function grab(){
        if(!isset($_REQUEST['wd']) || !$_REQUEST['wd'])
        {
            @unlink($this->cookie_file);
            $this->ctr_cookie=0;
            return json_encode( array('code'=>'0','error'=>'请输入关键词'));
            //exit;
        }

        //查询成功，下面开始正则抓取
        //开始正则抓取表格数据
        if(isset($_REQUEST['page']))
        {
            $page = $_REQUEST['page'];
        }
        else
        {
            $page = null;
        }
        $class = $this->search_book($_REQUEST['wd'], $page); 
        if(!$this->is_not_json($class)){//检查是否报错
            @unlink($this->cookie_file);
            $this->ctr_cookie=0;
            return $class;
        }
        if($class){//若抓到数据
            @unlink($this->cookie_file);
            $this->ctr_cookie=0;
            return json_encode( array('code'=>'1','data'=>$class));
        }
        else {//若没有抓到数据
            @unlink($this->cookie_file);
            $this->ctr_cookie=0;
            return json_encode( array('code'=>'0','error'=>'没有相关信息'));
        }
        //@unlink ($cookie_file);
    }
}
?>
