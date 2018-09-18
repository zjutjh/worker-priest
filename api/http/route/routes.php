<?php

use http\controller\BaseController;
//use NoahBuscher\Macaw\Macaw;

/*Macaw::get('/index.php/libraryTEST/data/(:any)',function($Str){
    var_dump($Str);
    $arr1 = explode('&',$Str);
    foreach($arr1 as $arr){
	$a = explode('=',$arr);
	$arr2[$a[0]] = $a[1];
    }
    var_dump($arr2);
    $data=null;
    var_dump(registerCrawler::$register['libraryCrawler']->data($data,'http://210.32.205.60/login.aspx'));
   // var_dump(registerCrawler::$register['libraryCrawler']->data($data,'http://www.zjut.edu.cn'));
});*/
$http->on('request',function($request,$response){
    //var_dump($request->get,$request->post);
    if($request->get!=null){
        $_REQUEST=$request->get;
    }
    else if($request->post!=null){
        $_REQUEST=$request->post;
    }
    $_SERVER=$request->server;
    $response->header("Content-Type","application/json;charset=utf-8");
    $httpCode=json_encode(array('code'=>1,'error'=>'404'));
    if(BaseController::run()==$httpCode){
	    $response->status("404");
	    $response->end($httpCode);
    }
    $response->end(BaseController::run());
});
//Macaw::dispatch();
?>
