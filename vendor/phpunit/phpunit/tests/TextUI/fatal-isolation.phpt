--TEST--
phpunit FatalTest ../_files/FatalTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = 'FatalTest';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/FatalTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

E

Time: %s, Memory: %sMb

There was 1 error:

1) FatalTest::testFatalError
%s

FAILURES!
Tests: 1, Assertions: 0, Errors: 1.
