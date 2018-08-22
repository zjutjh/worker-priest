<?php
namespace api\crawler;
use api\crawler\BaseCrawler;
use api\crawler\registerCrawler;
use api\crawler\CrawlerInterface;

class libraryCrawler extends BaseCrawler{ //implements CrawlerInterface{
    ///**
    // * 临时容器
    // * 
    // */
    //protected $__array=array();
    
    /**
     * ioc容器
     * 
     * @var instance
     */
    protected $__di;
    /**
     * curl的cookie临时文件
     * cookie的路由是否正确？
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
        $this->__di=new registerCrawler;
        $this->cookie_file=tempnam('./storage/','cookie');
        $this->ctr_cookie=0;
    }
    
    
    ///**
    // * 注入函数
    // * 
    // * @param array
    // * @return void
    // */
    //public function setDi(){
    //    $this->__array=[
    //            "book_grab"=>function(Array $array){
    //                return $this->book_grab($array);
    //            }
    //        ];
    //    foreach($this->__array as $key=>$definition){
    //        $this->__di->set($key,$definition);
    //    }
    //}
    

    /**
     * 判断是不是json数据，用于在报错时及时返回json报错信息
     * 并结束程序
     */
    public function is_not_json($str){
        return is_null(json_decode($str));
    }
    /**
     * 共用的数据获取函数
     * 
     * 
     */
    public function data( $data,$url,$fun="get",$referer="http://210.32.205.60"){//data是否应该传入引用
        global $cookie_file,$ctr_cookie;
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
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        $http_header = array();
        foreach ($browser as $key => $value) {
            $http_header[] = "{$key}: {$value}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);//传入一个数组
        if($fun=="get"){
            if($data!=null)//既然传入了data,那为啥还要get
                $data = http_build_query($data, '', '&');//对data进行操作

            curl_setopt($ch, CURLOPT_URL, $url);
            if($ctr_cookie==0) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
            }
            else{
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
            }
        }
        else{
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data, '', '&'));//传入键值对字符串
            
            if($ctr_cookie==0) {//判断是否有cookie
                curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);//连接时把获得的cookie存为文件
            }
            else{
                curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);//在访问其他页面时拿着这个cookie文件去访问
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
        //var_dump($cookie_file);
        return $file_contents;
    }
    /**
     * 获取用以post的data
     * 
     * 
     * @param string,string,mixed,array
     * 
     */
    public function post_data($post_field,$url,$data = null, $post_field_name=array("__VIEWSTATE","__EVENTTARGET","__EVENTARGUMENT","__VIEWSTATEGENERATOR","__VIEWSTATEENCRYPTED","__EVENTVALIDATION"))
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
        var_dump($post_field);
        return $post_field;
    }
    /**
     * book查询
     * 
     */
    public function library_book(){
        if(!isset($_REQUEST['id']) || !$_REQUEST['id'])
    {
        return json_encode( array('status'=>'error','msg'=>'请输入书本id'));
        exit;
    }
    $id = $_REQUEST['id'];
    $result=$this->data(null,'http://210.32.205.60/Book.aspx?id='.$id);
    if(!$this->is_not_json($result)){//检查是否报错
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
    @unlink ($cookie_file);
    if($class) {//若抓到数据
        return json_encode( array('status'=>'success','msg'=>$class));
    }
    else {
        return json_encode( array('status'=>'error','msg'=>'没有相关信息'));
    }

    //@unlink ($cookie_file);
    }
    /**
     * 图书馆登录函数
     * 
     * 传入用户名和密码
     * 
     * @param array
     * @return void
     */
    public function login($username,$passward){
        global $ctr_cookie;
        $url = "http://210.32.205.60/login.aspx";
        $post_field['TextBox1']=$username;
        $post_field['TextBox2']=$passward;
        $post_field['DropDownList1']='0';
        $post_field['ImageButton1.x']='35';
        $post_field['ImageButton1.y']='15';
        $post_data=$this->post_data($post_field,$url,null,array("__VIEWSTATE","__VIEWSTATEGENERATOR","__EVENTVALIDATION"));
        if(!$this->is_not_json($post_data)){//检查是否报错
            return $post_data;
        }
        $result = $this->data($post_data,"http://210.32.205.60/default.aspx",'post',$url);
        $ctr_cookie = 1;
        echo $result;
        return $result;
    }
    /**
     *借书逻辑
     */
    public function borrow_action($post_field, $action)
    {
        $url = "http://210.32.205.60/Borrowing.aspx";
        $post_field['ctl00$ScriptManager1']='ctl00$ContentPlaceHolder1$UpdatePanel1|ctl00$ContentPlaceHolder1$GridView1';
        $post_field['ctl00_TreeView1_ExpandState']='eennnnnnennnnnnnennnnnennnenn';
        $post_field['ctl00_TreeView1_SelectedNode']='';
        $post_field['ctl00_TreeView1_PopulateLog']='';
        $post_field['__ASYNCPOST']='true';
        if($action == 'next')//翻页
        {
            $post_field['__EVENTARGUMENT']='Page$Next';
        }
        else if($action == 'pre')
        {
            $post_field['__EVENTARGUMENT']='Page$Prev';
        }
        $post_field['__EVENTTARGET']='ctl00$ContentPlaceHolder1$GridView1';

        $result = $this->data($post_field,$url,'post');
        return $result;
    }
    /**
     * 除借书信息外的一些基础信息
     * info_action函数
     */
    public function info_action($class)
    {
        $url = "http://210.32.205.60/Default.aspx";
        $result = $this->data(null,$url);
        if(!$this->is_not_json($result)){//检查是否报错
            return $result;
        }
        if(preg_match_all('/<span id="ctl00_ContentPlaceHolder1_LBnowborrow[\w\W]*?>([\w\W]*?)<\/span>/', $result, $arr)!=0){
            $class['borrow_num'] = trim($arr[1][0]);
        }

        if(preg_match_all('/<span id="ctl00_ContentPlaceHolder1_LBcq[\w\W]*?>([\w\W]*?)<\/span>/', $result, $arr)!=0){
            $class['overdue'] = trim($arr[1][0]);
        }

        if(preg_match_all('/<span id="ctl00_ContentPlaceHolder1_LBqk[\w\W]*?>([\w\W]*?)<\/span>/', $result, $arr)!=0){
            $class['debet'] = trim($arr[1][0]);
        }

        return $class;
    }
    /**
     * 将粗糙的前端页面匹配为可用的数据
     * fix_result函数
     */
    function fix_result($result, $fix_ajax = false)
    {
        if(preg_match_all("/Object moved to/", $result, $temp)!=0) {
            @unlink ($cookie_file);
	        return json_encode( array('status'=>'error','msg'=>'用户名或密码错误'));
            //@unlink ($cookie_file);
            exit;
        }

        if(preg_match_all('/pic\/NextPage\.png/', $result, $arr)!=0){
            $class['has_next'] = true;
        }

        if(preg_match_all('/pic\/PrePage\.png/', $result, $arr)!=0){
            $class['has_pre'] = true;
        }
        
        $class['borrow_list'] = array();

        if(preg_match_all('/<table\s*style="border-style: none;[\w\W]*?>([\w\W]*?)\s*<\/table>/', $result, $arr)!=0){//若抓到数据
            //var_dump($arr);
            foreach ($arr[1] as $key => $value) {
                if(preg_match_all('/<a id="ctl00_ContentPlaceHolder1_GridView1[\w\W]*?href="Book.aspx\?id=([\d]*?)"[\w\W]*?>([\w\W]*?)<\/a>/', $value, $temp)!=0) {
                    $borrow['bookid'] = $temp[1][0];
                    $borrow['title'] = $temp[2][0];
                }
                if(preg_match_all('/<span id="ctl00_ContentPlaceHolder1_GridView1[\w\W]*?>([\w\W]*?)<\/span>/', $value, $temp)!=0) {
                    // var_dump($temp);
                    $borrow['collection_code'] = $temp[1][0];
                    $borrow['collection_address'] = $temp[1][1];
                    $borrow['borrow_date'] = $temp[1][2];
                    $borrow['return_date'] = $temp[1][3];
                    $borrow['renew'] = $temp[1][4];
                    $borrow['status'] = $temp[1][5];
                    $class['borrow_list'][] = $borrow;
                }
            }
        }
        if($fix_ajax)
        {
            $post_field = array();
            if(preg_match_all('/\d+\|hiddenField\|([\w\W]*?)\|([\w\W]*?)\|/', $result, $t))
            {
                foreach ($t[1] as $key => $value) {
                    $post_field[$value] = $t[2][$key];
                }
            }
            $class['session'] = $post_field;
        }
        else
        {   $post_data = $this->post_data(array(),null,$result,array("__VIEWSTATE","__LASTFOCUS","__VIEWSTATEGENERATOR","__EVENTVALIDATION","__VIEWSTATEENCRYPTED"));
            if(!$this->is_not_json($post_data)){//检查是否报错
                return $post_data;
            }
            $class['session'] =$post_data;
        }

        return $class;
    }
    /**
     * book_borrow函数
     */
    public function book_borrow(){
        if(!isset($_REQUEST['username']) || !$_REQUEST['password'])
        {
            @unlink ($cookie_file);
            return json_encode( array('status'=>'error','msg'=>'参数错误'));
            exit;
        }
        //查询成功，下面开始正则抓取
        //开始正则抓取表格数据
        $class = array();
        if($this->login($_REQUEST['username'], $_REQUEST['password'])){//登陆成功
            //如果有翻页操作
            if(isset($_REQUEST['action']) && $_REQUEST['action'] && isset($_REQUEST['session']) && $_REQUEST['session'])
            {
                $class = $this->fix_result($this->borrow_action($_REQUEST['session'], $_REQUEST['action']), true);
                if(!$this->is_not_json($class)){//检查是否报错
                    return $class;
                }
            }
            else
            {
                $result = $this->data(null,'http://210.32.205.60/Borrowing.aspx');
                if(!$this->is_not_json($result)){//检查是否报错
                    return $result;
                }
                $class = $this->fix_result($result);
                if(!$this->is_not_json($class)){//检查是否报错
                    return $class;
                }
            }
            $class = $this->info_action($class);
            if(!$this->is_not_json($class)){//检查是否报错
                return $class;
            }
            //echo ($result);
        }
        if(isset($class['borrow_num'])){//若抓到数据
            return json_encode( array('status'=>'success','msg'=>$class));
        }
        else {//若没有抓到数据
            return json_encode( array('status'=>'success','msg'=>'没有相关信息'));
        }
        @unlink ($cookie_file);
    }
    /**
     * 爬取逻辑
     * 传入的数组的首值存放的是闭包名
     * 其余各值分别为召唤的函数的所需的参数
     * 
     * 出来吧，switch！！！
     * 颤抖吧~~哈哈哈哈~~~（原谅我突然的中二）
     * 
     * @param array
     */
    public function grab(Array $array){
        //$name=$array[0];
        //if(isset(registerCrawler::$register[$name])){
        //    $definition=$__di->get($name);
        //}
        //else 
        switch($array[0]){
            case "book_grab":return book_grab();break;
            case "book_borrow":return book_borrow();break;
            default:throw new Exception("the function '".$array[0]."' hasn't been defined in this scope");
        }
    }
}
?>
