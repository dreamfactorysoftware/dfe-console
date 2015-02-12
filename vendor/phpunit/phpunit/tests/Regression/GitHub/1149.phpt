--TEST--
GH-1149: Test swallows output buffer when run in a separate process
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue1149Test';
$_SERVER['argv'][3] = dirname(__FILE__).'/1149/Issue1149Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

.1.2

Time: %s, Memory: %sMb

OK (2 tests, 2 assertions)
