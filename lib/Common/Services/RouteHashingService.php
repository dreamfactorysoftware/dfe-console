<?php
namespace DreamFactory\Enterprise\Common\Services;

use DreamFactory\Library\Fabric\Database\Models\Deploy\RouteHash;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Filesystem;

/**
 * Provides route hashing services
 */
class RouteHashingService extends BaseService implements Hasher
{
    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @param string $pathToHash The path to hash
     * @param int    $keepDays   The number of days to keep the link active
     *
     * @return string The hash/token representing the unique owner-path pair.
     */
    public function create( $pathToHash, $keepDays = 30 )
    {
        $_hash = sha1( md5( $pathToHash ) . microtime( true ) . getmypid() );

        RouteHash::insert(
            [
                'hash_text'        => $_hash,
                'actual_path_text' => $pathToHash,
                'expire_date'      => $keepDays ? date( 'c', time() + ( $keepDays * 86400 ) ) : null,
            ]
        );

        return $_hash;
    }

    /**
     * @param string $hashToResolve A hash generated by this object
     *
     * @return string Returns the path that belongs to the given hash
     * @throws \InvalidArgumentException when the owner-hash pair is invalid
     */
    public function resolve( $hashToResolve )
    {
        return RouteHash::where( 'hash_text', '=', $hashToResolve )->pluck( 'actual_path_text' ) ?: false;
    }

    /**
     * @param Filesystem $fsToCheck The file system to check
     *
     * @return int Returns the number of files that were spoiled.
     */
    public static function expireFiles( $fsToCheck )
    {
        /** @type Collection $_hashes */
        $_hashes = RouteHash::where( 'expire_date', '<', DB::raw( 'CURRENT_DATE' ) )->get();
        $_count = 0;

        if ( !empty( $_hashes ) )
        {
            foreach ( $_hashes as $_hash )
            {
                if ( $fsToCheck->has( $_hash->actual_path_text ) )
                {
                    $fsToCheck->delete( $_hash->actual_path_text );
                }

                $_hash->delete();
                unset( $_hash );
            }

            unset( $_hashes );
        }

        return $_count;
    }

}