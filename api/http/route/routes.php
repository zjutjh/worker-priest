<?php

use NoahBuscher\Macaw\Macaw;

Macaw::get('/',function(){
    echo "成功！";
});

Macaw::get('/test',function(){
    echo "成功！";
});

Macaw::get('/funk',function(){
    echo "yooooo";
});

Macaw::dispatch();
?>