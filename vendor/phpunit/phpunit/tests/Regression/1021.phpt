--TEST--
#1021: Depending on a test that uses a data provider does not work
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue1021Test';
$_SERVER['argv'][3] = dirname(__FILE__).'/1021/Issue1021Test.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

..

Time: %s, Memory: %sMb

OK (2 tests, 1 assertion)
