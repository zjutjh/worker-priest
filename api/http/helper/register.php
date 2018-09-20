<?php
namespace api\http\helper;
use api\crawler\BaseCrawler;
use api\crawler\libraryCrawler\library_book;
use api\crawler\libraryCrawler\library_borrow;
use api\crawler\libraryCrawler\library_search;
use api\crawler\cardCrawler\campus_card;
use api\crawler\cardCrawler\cardBalance;
use api\crawler\cardCrawler\cardRecords;
use api\crawler\zfCrawler\scoresZF;

//echo "register";
//$c=new cardCrawler;
//$l=new libraryCrawler;
//$y=new ycCrawler;
//$z=new zfCrawler;

$array=[
    //"cardCrawler"=>new cardCrawler(),
    //"libraryCrawler"=>new libraryCrawler(),
    "/library/book"=>new library_book(),
    "/library/borrow"=>new library_borrow(),
    "/library/search"=>new library_search(),
    //"/ycCrawler"=>new ycCrawler(),
    //"/zfCrawler"=>new zfCrawler(),
    "/cardBalance"=>new cardBalance(),
    "/cardRecords"=>new cardRecords(),
    "/campus-card"=>new campus_card(),
    "/zf/scores"=>new scoresZF()
];
//基类
$base=new BaseCrawler;
//注入
$base->setDi($array);

?>
