<?php
/**
 * Created by PhpStorm.
 * User: debian
 * Date: 19-8-18
 * Time: 下午2:59
 */
require_once __DIR__ .'/../vendor/autoload.php';

$manager = new \Sharedsway\Event\Manager();
$manager->attach('hello', function (\Sharedsway\Event\EventInterface $event,$sender,$data) {
    if ('name' == $event->getType()) {
        echo (sprintf('hello %s',$data)) , PHP_EOL;

    }
});


$manager->fire('hello:name',new \stdClass(),'Tony');
$manager->fire('hello:name',new \stdClass(),'Jim Green');