<?php
/**
 * elk.php
 * This file contains configuration information for elasticsearch/logstash/kibana
 */
return [
    'host' => 'lps-east-1.fabric.dreamfactory.com',
    'port' => 9200,
    'timeout' => 90,
    'strategy' => '\\Elastica\\Connection\\Strategy\\Simple',
];
