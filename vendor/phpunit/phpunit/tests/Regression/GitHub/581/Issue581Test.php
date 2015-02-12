<?php
class Issue581Test extends PHPUnit_Framework_TestCase
{
    public function testExportingObjectsDoesNotBreakWindowsLineFeeds()
    {
        $this->assertEquals(
<<<<<<< HEAD
            (object)array(1, 2, "Test\r\n", 4, 5, 6, 7, 8),
            (object)array(1, 2, "Test\r\n", 4, 1, 6, 7, 8)
=======
            (object)array(1,2,"Test\r\n",4,5,6,7,8),
            (object)array(1,2,"Test\r\n",4,1,6,7,8)
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
        );
    }
}
