--TEST--
GH-1348: STDOUT/STDERR IO streams should exist in process isolation
--SKIPIF--
<?php
if (defined('HHVM_VERSION'))
    print "skip: PHP runtime required";
?>
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
<<<<<<< HEAD
$_SERVER['argv'][] = '--report-useless-tests';
=======
$_SERVER['argv'][] = '--strict';
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = 'Issue1348Test';
$_SERVER['argv'][] = __DIR__ . '/1348/Issue1348Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

.
STDOUT does not break test result
E

Time: %s, Memory: %sMb

There was 1 error:

1) Issue1348Test::testSTDERR
PHPUnit_Framework_Exception: STDERR works as usual.

FAILURES!
<<<<<<< HEAD
Tests: 2, Assertions: 1, Errors: 1.
=======
Tests: 2, Assertions: 1, Errors: 1.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
