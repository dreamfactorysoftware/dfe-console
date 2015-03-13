<?php
use DreamFactory\Enterprise\Services\Facades\Snapshot;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class SnapshotServiceTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testCreate()
    {
        $_source = new Filesystem( new Local( dirname( dirname( __DIR__ ) ) . '/app' ) );
        $_destination = new Filesystem( new Local( __DIR__ . '/sst-destination' ) );

        Snapshot::create( 'sandman', $_source, $_destination );
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if ( is_dir( __DIR__ . '/sst-destination' ) )
        {
            static::_rmdir( __DIR__ . '/sst-destination' );
        }

        if ( !is_dir( __DIR__ . '/sst-destination' ) )
        {
            mkdir( __DIR__ . '/sst-destination', 0777, true );
        }
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

//        if ( is_dir( __DIR__ . '/sst-destination' ) )
//        {
//            static::_rmdir( __DIR__ . '/sst-destination' );
//        }

        if ( is_dir( __DIR__ . '/sst-source' ) )
        {
            rmdir( __DIR__ . '/sst-source' );
        }
    }

    /**
     * Removes a directory tree
     *
     * @param string $dir
     *
     * @return bool
     */
    protected static function _rmdir( $dir )
    {
        if ( empty( $dir ) || DIRECTORY_SEPARATOR == trim( $dir ) )
        {
            return false;
        }

        $_files = glob( $dir . DIRECTORY_SEPARATOR . '*' );

        if ( !empty( $_files ) )
        {
            foreach ( $_files as $_file )
            {
                is_dir( $_file ) && @static::_rmdir( $_file ) || @unlink( $_file );
            }

        }
        else
        {
            @rmdir( $dir );
        }

        return true;
    }

}
