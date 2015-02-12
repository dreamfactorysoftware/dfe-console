--TEST--
GH-455: expectOutputString not working in strict mode
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
<<<<<<< HEAD
$_SERVER['argv'][2] = '--disallow-test-output';
=======
$_SERVER['argv'][2] = '--strict';
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
$_SERVER['argv'][3] = 'Issue445Test';
$_SERVER['argv'][4] = dirname(__FILE__).'/445/Issue445Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
<<<<<<< HEAD
PHPUnit %s by Sebastian Bergmann and contributors.
=======
PHPUnit %s by Sebastian Bergmann.
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4

..F

Time: %s, Memory: %sMb

There was 1 failure:

1) Issue445Test::testNotMatchingOutput
Failed asserting that two strings are equal.
--- Expected
+++ Actual
@@ @@
-'foo'
+'bar'

FAILURES!
Tests: 3, Assertions: 3, Failures: 1.
