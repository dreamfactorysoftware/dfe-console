<?php
namespace DreamFactory\Enterprise\Services\Tests;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Services\Facades\InstanceStorage;
use Illuminate\Filesystem\FilesystemAdapter;

class InstanceStorageServiceTest extends \TestCase
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string
     */
    const STORAGE_ROOT = 'ec2.us-east-1a/be/be730efabf167e80cb995826ce4ba2764af99e88bc84dac5d7a78c7350dd76d2';
    /**
     * @type string
     */
    const STORAGE_ROOT_PATH = '/opt/dreamfactory/dfe/dfe-console/tests/storage/ec2.us-east-1a/be/be730efabf167e80cb995826ce4ba2764af99e88bc84dac5d7a78c7350dd76d2';
    /**
     * @type string
     */
    const INSTANCE_STORAGE_PATH = '/opt/dreamfactory/dfe/dfe-console/tests/storage/ec2.us-east-1a/be/be730efabf167e80cb995826ce4ba2764af99e88bc84dac5d7a78c7350dd76d2/dfe-test-case';
    /**
     * @type string
     */
    const INSTANCE_PRIVATE_PATH = '/opt/dreamfactory/dfe/dfe-console/tests/storage/ec2.us-east-1a/be/be730efabf167e80cb995826ce4ba2764af99e88bc84dac5d7a78c7350dd76d2/dfe-test-case/.private';
    /**
     * @type string
     */
    const OWNER_PRIVATE_PATH = '/opt/dreamfactory/dfe/dfe-console/tests/storage/ec2.us-east-1a/be/be730efabf167e80cb995826ce4ba2764af99e88bc84dac5d7a78c7350dd76d2/.private';
    /**
     * @type string
     */
    const SNAPSHOT_PATH = '/opt/dreamfactory/dfe/dfe-console/tests/storage/ec2.us-east-1a/be/be730efabf167e80cb995826ce4ba2764af99e88bc84dac5d7a78c7350dd76d2/.private/snapshots';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests provision request
     */
    public function testStoragePaths()
    {
        $_instance = $this->_findInstance( 'dfe-test-case' );

        $_instanceId = $_instance->instance_id_text;
        $_private = config( 'dfe.provisioning.private-path-name', ConsoleDefaults::PRIVATE_PATH_NAME );
        $_snapshot = config( 'dfe.provisioning.snapshot-path-name', ConsoleDefaults::SNAPSHOT_PATH_NAME );

        $this->assertEquals(
            static::STORAGE_ROOT . DIRECTORY_SEPARATOR . $_instanceId,
            InstanceStorage::getStoragePath( $_instance )
        );

        $this->assertEquals(
            static::STORAGE_ROOT . DIRECTORY_SEPARATOR . $_instanceId . DIRECTORY_SEPARATOR . $_private,
            InstanceStorage::getPrivatePath( $_instance )
        );

        $this->assertEquals(
            static::STORAGE_ROOT . DIRECTORY_SEPARATOR . $_private,
            InstanceStorage::getOwnerPrivatePath( $_instance )
        );

        $this->assertEquals(
            static::STORAGE_ROOT . DIRECTORY_SEPARATOR . $_private . DIRECTORY_SEPARATOR . $_snapshot,
            InstanceStorage::getSnapshotPath( $_instance )
        );
    }

    /**
     * @covers InstanceStorage::getStorageMount
     * @covers InstanceStorage::getPrivateStorageMount
     * @covers InstanceStorage::getOwnerPrivateStorageMount
     * @covers InstanceStorage::getSnapshotMount
     */
    public function testStorageMounts()
    {
        $_instance = $this->_findInstance( 'dfe-test-case' );

        $_testFile = '_test.file_';
        $_contents = 'test';

        $this->_doFileTest( InstanceStorage::getStorageMount( $_instance ), $_testFile, $_contents, static::INSTANCE_STORAGE_PATH );
        $this->_doFileTest( InstanceStorage::getPrivateStorageMount( $_instance ), $_testFile, $_contents, static::INSTANCE_PRIVATE_PATH );
        $this->_doFileTest( InstanceStorage::getOwnerPrivateStorageMount( $_instance ), $_testFile, $_contents, static::OWNER_PRIVATE_PATH );
        $this->_doFileTest( InstanceStorage::getSnapshotMount( $_instance ), $_testFile, $_contents, static::SNAPSHOT_PATH );
    }

    /**
     * @param FilesystemAdapter $mount
     * @param string            $file
     * @param string            $contents
     * @param string            $check
     *
     * @throws \Exception
     */
    protected function _doFileTest( $mount, $file, $contents, $check )
    {
        $mount->put( $file, $contents );

        try
        {
            $this->assertTrue(
                $contents == file_get_contents( $check . DIRECTORY_SEPARATOR . $file )
            );

            @$mount->delete( $file );
        }
        catch ( \Exception $_ex )
        {
            @$mount->delete( $file );
            throw $_ex;
        }
    }

}