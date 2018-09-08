<?php
namespace api\crawler\libraryCrawler;
use api\crawler\BaseCrawler;
use api\crawler\registerCrawler;

class library_book extends BaseCrawler{
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
     * 共用的数据获取函数
     * 
     * @param array,string,string
     * @return mixed
     */
    private function data( $data,$url,$fun="get"){//data是否应该传入引用
        //global $cookie_file,$ctr_cookie;
        //定义请求头
        $browser = array(
        "User-Agent" => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36",
        "Upgrade-Insecure-Requests" => "1",
        "Content-Type" => "application/x-www-form-urlencoded",
        "language" => "zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3",
        "Accept"=>"text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
        "Accept-Encoding"=>"gzip, deflate",
        "Connection"=> "keep-alive",
        "Accept-Language" => "zh-CN,zh;q=0.8",
        "Cache-Control" => "max-age=0"
        );//最后一个键值对后面加逗号是否可行

        $ch =curl_init();
        if(isset($_REQUEST['timeout']) && is_numeric($_REQUEST['timeout']))//timeout变量是否有数值
        {
            $timeout = intval($_REQUEST['timeout']);//转化为int型
        }
        else
        {
            $timeout = 15;
        }
        //赋值请求头
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
        curl_setopt($ch, CURLOPT_REFERER, "http://210.32.205.60");
        $http_header = array();
        foreach ($browser as $key => $value) {
            $http_header[] = "{$key}: {$value}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);//传入一个数组
        if($fun=="get"){
            if($data!=null)//既然传入了data,那为啥还要get
                $data = http_build_query($data, '', '&');//对data进行操作

            curl_setopt($ch, CURLOPT_URL, $url);
            if($this->ctr_cookie==0) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
                //$this->ctr_cookie=1;
                //echo "makecookie\n";
            }
            else{
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
                //echo "sendcookie\n";
            }
        }
        else{
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data, '', '&'));//传入键值对字符串
            
            if($this->ctr_cookie==0) {//判断是否有cookie
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);//连接时把获得的cookie存为文件
                //$this->ctr_cookie=1;
                //echo "post makecookie\n";
            }
            else{
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);//在访问其他页面时拿着这个cookie文件去访问
                //echo "post sendcookie\n";
            }
        }
        //设置post请求
       // curl_setopt($ch, CURLOPT_HEADER, 0);
-
        //param为请求的参数
        $file_contents = curl_exec($ch);//文件流
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);  //判断是否成功登陆
        if($httpCode == 0)
        {
            return json_encode( array('status'=>'error','msg'=>'服务器错误'));
            exit;
        }
        curl_close($ch);
        //var_dump($this->cookie_file);
        return $file_contents;
    }
    /**
     * 获取用以post的data
     * 
     * 
     * @param string,string,mixed,array
     * 
     */
    private function post_data($post_field,$url,$data = null, $post_field_name=array("__VIEWSTATE","__EVENTTARGET","__EVENTARGUMENT","__VIEWSTATEGENERATOR","__VIEWSTATEENCRYPTED","__EVENTVALIDATION"))
    {
        if($data)
        {
            $contents = $data;
        }
        else
        {
            $contents = $this->data(null,$url,'get');
        }
        if(!$this->is_not_json($contents)){//检查是否报错
            return $contents;
        }
        //var_dump($contents);
        //$post_field_name=array("__VIEWSTATE","__EVENTTARGET","__EVENTARGUMENT","__VIEWSTATEGENERATOR","__VIEWSTATEENCRYPTED","__EVENTVALIDATION");

        for($i=0;$i<count($post_field_name);$i++){

            unset($matches);
            $name=$post_field_name[$i];
            preg_match('/<input\s*type="hidden"\s*name="'.$name.'"\s*id="'.$name.'"\s*value="(.*?)"\s*\/>/i', $contents, $matches);//微精弘的前端页面
            //var_dump($matches[1]);
            if($matches==null)//变为键值对数组
                $post_field[$name]="";
            else
                $post_field[$name]=$matches[1];//$matches下标为0位存放的是原字符串
        }
        //var_dump($post_field);
        return $post_field;
    }
    /**
     * book_grab函数
     * @return json
     */
    public function grab(){
        if(!isset($_REQUEST['id']) || !$_REQUEST['id'])
        {
            @unlink($this->cookie_file);
            $this->ctr_cookie=0;
            return json_encode( array('status'=>'error','msg'=>'请输入书本id'));
        }
        $id = $_REQUEST['id'];
        $result=$this->data(null,'http://210.32.205.60/Book.aspx?id='.$id);
        if(!$this->is_not_json($result)){//检查是否报错
            @unlink($this->cookie_file);
            $this->ctr_cookie=0;
            return $result;
        }
        $class = array();
        //<table style="border-style: none;
        $preg = '/<table cellspacing="0" cellpadding="0" border="0" [\w\W]*?>([\w\W]*?)<\/table>/';
        if(preg_match_all('/<iframe id="ctl00_ContentPlaceHolder1_DuXiuImage"[\w\W]*?src="([\w\W]*?)">/', $result, $arr)!=0){//若抓到数据
            $class['cover_iframe'] = ($arr[1][0]);
        }
        if(preg_match_all($preg, $result, $arr)!=0){//若抓到数据
            if(preg_match_all('/<span id="ctl00_ContentPlaceHolder1_DetailsView1_Label[\w\W]*?">([\w\W]*?)<\/span>/', $arr[0][0], $temp)!=0){
                $class['title'] = $temp[1][0];
                $class['series'] = $temp[1][1];
                $class['author'] = $temp[1][2];
                $class['ISBN'] = $temp[1][3];
                $class['call_number'] = $temp[1][4];
                $class['call_type'] = $temp[1][5];
                $class['price'] = $temp[1][6];
                $class['publish_location'] = $temp[1][7];
                $class['topic'] = $temp[1][8];
                $class['type'] = $temp[1][9];
                $class['publish_date'] = $temp[1][10];
                $class['publisher'] = $temp[1][11];
            }

        }
        if(preg_match_all('/<table cellspacing="0" cellpadding="0" align="Left" rules="all" border="1" [\w\W]*?>([\w\W]*?)<\/table>/', $result, $arr)!=0){//若抓到数据
            if(preg_match_all('/<tr align="left"[\w\W]*?>([\w\W]*?)<\/tr>/', $arr[0][0], $temp)!=0){
                foreach ($temp[1] as $key => $value) {
                    if(preg_match_all('/<td align="center" style="color:[\w\W]*?>([\w\W]*?)<\/td>/', $value, $temp2)!=0){
                        if(preg_match_all('/<span id="ctl00_ContentPlaceHolder1_GridView1[\w\W]*?>([\w\W]*?)<\/span>/', $value, $temp3)!=0) {
                            $book_status['collection_address'] = $temp3[1][0];
                            $book_status['collection_location'] = $temp3[1][1];
                        }
                        $book_status['barcode'] = $temp2[1][0];
                        $book_status['collection_code'] = trim(html_entity_decode($temp2[1][1]));
                        $book_status['borrow_date'] = trim(html_entity_decode($temp2[1][2]));
                        $book_status['return_date'] = trim(html_entity_decode($temp2[1][3]));
                        if(preg_match_all('/<span id="ctl00_ContentPlaceHolder1_GridView[\w\W]*?>([\w\W]*?)<\/span>/', $temp2[1][4], $temp3)!=0) {
                            $book_status['status'] = $temp3[1][0];
                        }
                        $class['book_status_list'][] = $book_status;
                    }
                }
            }
        }
        @unlink ($this->cookie_file);
        $this->ctr_cookie=0;
        if($class) {//若抓到数据
            return json_encode( array('status'=>'success','msg'=>$class));
        }
        else {
            return json_encode( array('status'=>'error','msg'=>'没有相关信息'));
        }

        //@unlink ($cookie_file);
    }
}
?>