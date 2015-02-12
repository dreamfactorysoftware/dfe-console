--TEST--
phpunit --repeat 3 BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--repeat';
$_SERVER['argv'][3] = '3';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = dirname(__FILE__).'/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

.........

Time: %s, Memory: %sMb

OK (9 tests, 9 assertions)
