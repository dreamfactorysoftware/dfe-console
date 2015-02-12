--TEST--
GH-322: group commandline option should override group/exclude setting in phpunit.xml
--FILE--
<?php

$_SERVER['argv'][1] = '--configuration';
$_SERVER['argv'][2] = dirname(__FILE__).'/322/phpunit322.xml';
$_SERVER['argv'][3] = '--debug';
$_SERVER['argv'][4] = '--group';
$_SERVER['argv'][5] = 'one';
$_SERVER['argv'][6] = 'Issue322Test';
$_SERVER['argv'][7] = dirname(__FILE__).'/322/Issue322Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

Configuration read from %s


Starting test 'Issue322Test::testOne'.
.

Time: %s, Memory: %sMb

OK (1 test, 0 assertions)
