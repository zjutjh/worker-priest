<?php
$http = new swoole_http_server("0.0.0.0",9501);

$http->set(array(
	'worker_num' => 4,
	'daemonize' => 1,
	'log_file' => '../storage/http_server.log',
));
require '../public/index.php';
/*$http->on('request',function($request,$response){
	var_dump($request->get,$request->post);
	require '../public/index.php';
	$response->header("Content-Type","text/html;charset=utf-8");
	$response->end("<h1>Hello Swoole . #".rand(1000,9999)."</h1>");
});*/

$http->start();

echo "Swoole started";
?>
