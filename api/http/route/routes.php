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
    $pool->on("WorkerStart", function ($pool, $workerId) {
        echo "Worker#{$workerId} is started\n";
        //var_dump($request->get,$request->post);
        if($request->get!=null){
            $_REQUEST=$request->get;
        }
        else if($request->post!=null){
            $_REQUEST=$request->post;
        }
        $_SERVER=$request->server;
        $response->header("Content-Type","application/json;charset=utf-8");
        $response->end(BaseController::run());
    });
    $pool->on("WorkerStop", function ($pool, $workerId) {
        echo "Worker#{$workerId} is stopped\n";
    });
    $pool->start();
});
//Macaw::dispatch();
?>
