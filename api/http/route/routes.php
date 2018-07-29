<?php

use NoahBuscher\Macaw\Macaw;

Macaw::get('/index.php/',function(){
    echo "成功！";
});

Macaw::get('/index.php/test',function(){
    echo "成功！";
});

Macaw::get('/index.php/hello',function(){
    echo "yooooo";
});

Macaw::get('/swoole',function(){
    echo "swoole Macaw\n";
});
Macaw::dispatch();
?>
