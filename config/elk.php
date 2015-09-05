<?php
//******************************************************************************
//* ELK configuration
//******************************************************************************
return [
    'host'     => 'lps-east-1.fabric.dreamfactory.com',
    'port'     => 9200,
    'timeout'  => 90,
    'strategy' => \Elastica\Connection\Strategy\Simple::class,
];
