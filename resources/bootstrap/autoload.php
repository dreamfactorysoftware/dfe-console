<?php
/**
 * DreamFactory encapsulated application autoloader
 */
define('LARAVEL_START', microtime(true));

require ($_dir = getcwd()) . '/../vendor/autoload.php';

if (file_exists($_dir . '/cache/compiled.php')) {
    require $_dir . '/cache/compiled.php';
}
