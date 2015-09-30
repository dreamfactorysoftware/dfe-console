<?php namespace DreamFactory\Enterprise\Console\Tests\Console\Commands;

use DreamFactory\Library\Utility\Disk;
use Illuminate\Support\Facades\Artisan;

class MountTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function setUp()
    {
        parent::setUp();

        $this->arguments = [
            'operation' => null,
            'mount-id'  => null,
        ];

        $this->options = [
            '--mount-type' => null,
            '--owner-id'   => null,
            '--owner-type' => null,
            '--root-path'  => null,
            '--config'     => ['disk' => 'local',],
        ];
    }

    /** @inheritdoc */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        //  Clean up: remove directory created for testing
        try {
            Disk::deleteTree(Disk::path([dirname(dirname(__DIR__)), 'storage', 'mount-test']));
        } catch (\Exception $_ex) {
            //  Ignored
        }
    }

    /**
     * Tests update request
     *
     * @covers \DreamFactory\Enterprise\Console\Commands\Mount::fire()
     */
    public function testCreate()
    {
        $_path = Disk::path([dirname(dirname(__DIR__)), 'storage', 'mount-test'], true);

        $this->arguments['operation'] = 'create';
        $this->arguments['mount-id'] = 'mount-test-' . date('YmdHis');
        $this->options['--mount-type'] = 'LOCAL';
        $this->options['--root-path'] = $_path;

        /** @noinspection PhpUndefinedMethodInspection */
        $_result =
            Artisan::call('dfe:setup',
                ['--force' => true, '--admin-password' => 'password', 'admin-email' => 'jerry@dreamfactory.com',]);

        $this->assertEquals(0, $_result);
    }

    /**
     * Tests update request
     */
    public function testUpdate()
    {
    }

    /**
     * Tests update request
     */
    public function testDelete()
    {
    }
}
