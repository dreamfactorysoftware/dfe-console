--TEST--
phpunit --process-isolation -d default_mimetype=application/x-test IniTest ../_files/IniTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '-d';
$_SERVER['argv'][4] = 'default_mimetype=application/x-test';
$_SERVER['argv'][5] = 'IniTest';
$_SERVER['argv'][6] = dirname(dirname(__FILE__)) . '/_files/IniTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

.

Time: %s, Memory: %sMb

OK (1 test, 1 assertion)
