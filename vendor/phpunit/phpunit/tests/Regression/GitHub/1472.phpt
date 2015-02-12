--TEST--
GH-1472: assertEqualXMLStructure modifies the tested elements
--SKIPIF--
<?php
// See: https://github.com/facebook/hhvm/issues/4669
if (defined('HHVM_VERSION')) {
    print 'skip: HHVM does not support cloning DOM nodes';
}
?>
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = 'Issue1472Test';
$_SERVER['argv'][3] = dirname(__FILE__) . '/1472/Issue1472Test.php';

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

Time: %s, Memory: %sMb

OK (1 test, 4 assertions)
