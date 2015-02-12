--TEST--
GH-581: PHPUnit_Util_Type::export adds extra newlines in Windows
--FILE--
<?php

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue581Test';
$_SERVER['argv'][3] = dirname(__FILE__).'/581/Issue581Test.php';

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

1) Issue581Test::testExportingObjectsDoesNotBreakWindowsLineFeeds
Failed asserting that two objects are equal.
--- Expected
+++ Actual
@@ @@
 stdClass Object (
     0 => 1
     1 => 2
     2 => 'Test\n'
     3 => 4
-    4 => 5
+    4 => 1
     5 => 6
     6 => 7
     7 => 8
 )

%s:%i

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
