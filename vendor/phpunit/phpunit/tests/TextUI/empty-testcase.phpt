--TEST--
phpunit EmptyTestCaseTest ../_files/EmptyTestCaseTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'EmptyTestCaseTest';
$_SERVER['argv'][3] = dirname(dirname(__FILE__)) . '/_files/EmptyTestCaseTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

F

Time: %s, Memory: %sMb

There was 1 failure:

1) Warning
No tests found in class "EmptyTestCaseTest".

FAILURES!
Tests: 1, Assertions: 0, Failures: 1.
