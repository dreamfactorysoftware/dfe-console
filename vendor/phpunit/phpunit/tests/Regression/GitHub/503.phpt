--TEST--
GH-503: assertEquals() Line Ending Differences Are Obscure
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue503Test';
$_SERVER['argv'][3] = dirname(__FILE__).'/503/Issue503Test.php';

require __DIR__ . '/../../bootstrap.php';
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

1) Issue503Test::testCompareDifferentLineEndings
Failed asserting that two strings are identical.
--- Expected
+++ Actual
@@ @@
 #Warning: Strings contain different line endings!
 foo

%s:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
