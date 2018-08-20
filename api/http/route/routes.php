<?php

use api\crawler\registerCrawler;
use NoahBuscher\Macaw\Macaw;

/*Macaw::get('/index.php/',function(){
    echo "成功！";
});

Macaw::get('/index.php/test',function(){
    echo "成功！";
});

Macaw::get('/index.php/hello',function(){
    echo "yooooo";
});
Macaw::get('/index.php/libraryTEST/data/(:any)',function($Str){
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
});

Macaw::get('/swoole',function(){
    echo "swoole Macaw\n";
});*/
$http->on('request',function($request,$response){
    var_dump($request->get,$request->post);
    if($request->get!=null){
        $_REQUEST=$request->get;
    }
    else if($request->post!=null){
        $_REQUEST=$request->post;
    }
    $response->header("Content-Type","application/json;charset=utf-8");
    //$response->end(registerCrawler::$register['libraryCrawler']->data($data,'http://210.32.205.60/login.aspx'));
    //$response->end(registerCrawler::$register['libraryCrawler']->login(201706060615,201706060615));
     //$response->end("swooleTEST");
    $response->end(registerCrawler::$register['libraryCrawler']->book_borrow());

});
//Macaw::dispatch();
?>
