--TEST--
GH-1335: exportVariable multiple backslash problem
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--bootstrap';
$_SERVER['argv'][3] = dirname(__FILE__).'/1335/bootstrap1335.php';
$_SERVER['argv'][4] = dirname(__FILE__).'/1335/Issue1335Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

............

Time: %s, Memory: %sMb

OK (12 tests, 12 assertions)
