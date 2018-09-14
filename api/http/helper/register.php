<?php
namespace api\http\helper;
use api\crawler\BaseCrawler;
use api\crawler\cardCrawler;
use api\crawler\libraryCrawler\library_book;
use api\crawler\libraryCrawler\library_borrow;
use api\crawler\libraryCrawler\library_search;
use api\crawler\ycCrawler;
use api\crawler\zfCrawler;
use api\crawler\cardCrawler\campus-card;
use api\crawler\cardCrawler\cardBalance;
use api\crawler\cardCrawler\cardRecords;

//echo "register";
//$c=new cardCrawler;
//$l=new libraryCrawler;
//$y=new ycCrawler;
//$z=new zfCrawler;

$array=[
    "/cardCrawler"=>new cardCrawler(),
    //"libraryCrawler"=>new libraryCrawler(),
    "/library_book"=>new library_book(),
    "/library_borrow"=>new library_borrow(),
    "/library_search"=>new library_search(),
    "/ycCrawler"=>new ycCrawler(),
    "/zfCrawler"=>new zfCrawler(),
    "/cardBalance"=>new cardBalance(),
    "/cardRecords"=>new cardRecords(),
    "/campus-card"=>new campus-card()
];
//基类
$base=new BaseCrawler;
//注入
$base->setDi($array);

?>
