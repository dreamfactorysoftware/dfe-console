<?php namespace DreamFactory\Enterprise\Services;

use Carbon\Carbon;
use DreamFactory\Enterprise\Common\Contracts\RouteHasher;
use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\RouteHash;
use DreamFactory\Enterprise\Storage\Facades\InstanceStorage;
use DreamFactory\Library\Utility\Enums\DateTimeIntervals;
use Illuminate\Database\Eloquent\Collection;
use League\Flysystem\Filesystem;

/**
 * Provides route hashing services
 */
class RouteHashingService extends BaseService implements RouteHasher
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * @param string $pathToHash The path to hash
     * @param int    $keepDays   The number of days to keep the link active
     *
     * @return string The hash/token representing the unique owner-path pair.
     */
    public function create($pathToHash, $keepDays = EnterpriseDefaults::SNAPSHOT_DAYS_TO_KEEP)
    {
        $_hash = sha1(md5($pathToHash) . microtime(true) . getmypid());
        if (empty($keepDays) || $keepDays < 0 || $keepDays > 365) {
            $keepDays = EnterpriseDefaults::SNAPSHOT_DAYS_TO_KEEP;
        }

        $_model = RouteHash::create([
            'hash_text'        => $_hash,
            'actual_path_text' => $pathToHash,
            'expire_date'      => Carbon::createFromTimestamp(time() + ($keepDays * DateTimeIntervals::SECONDS_PER_DAY)),
        ]);

        return $_model->hash_text;
    }

    /**
     * @param string $hash A hash generated by this object
     *
     * @return string Returns the path that belongs to the given hash
     * @throws \InvalidArgumentException when the owner-hash pair is invalid
     */
    public function resolve($hash)
    {
        return RouteHash::where('hash_text', $hash)->pluck('actual_path_text') ?: false;
    }

    /**
     * @param Filesystem $fsToCheck The file system to check
     *
     * @return int Returns the number of files that were trashed.
     * @throws \Exception
     */
    public function expireFiles($fsToCheck)
    {
        $_count = 0;

        try {
            /** @type Collection $_hashes */
            $_hashes = RouteHash::where('expire_date',
                '<',
                Carbon::createFromTimestamp(time() - config('snapshot.days-to-keep') * DateTimeIntervals::SECONDS_PER_DAY))->get();

            if (!empty($_hashes)) {
                foreach ($_hashes as $_hash) {
                    if ($fsToCheck->has($_hash->actual_path_text)) {
                        if ($this->moveToTrash($fsToCheck, $_hash->actual_path_text)) {
                            //  ONLY delete route_hash row if file was MOVED/DELETED
                            $_hash->delete();
                        }
                    }

                    unset($_hash);
                }

                unset($_hashes);
            }
        } catch (\Exception $_ex) {
            $this->error($_ex->getMessage());

            throw $_ex;
        }

        return $_count;
    }

    /**
     * Moves a file from somewhere to the expired trash heap
     *
     * @param Filesystem $filesystem
     * @param string     $filename
     * @param array      $config An optional configuration array
     *
     * @return bool
     */
    protected function moveToTrash(Filesystem $filesystem, $filename, array $config = [])
    {
        if (config('snapshot.soft-delete', EnterpriseDefaults::SNAPSHOT_SOFT_DELETE)) {
            $_trash = InstanceStorage::getTrashMount('expired');

            if ($_trash->writeStream($filename, $filesystem->readStream($filename), $config)) {
                return $filesystem->delete($filename);
            }

            //  Try and remove any partial file created before failure
            try {
                $_trash->delete($filename);
            } catch (\Exception $_ex) {
                //  Ignored, this is a cleanup in case of failure...
            }
        } else {
            try {
                if ($filesystem->has($filename)) {
                    return $filesystem->delete($filename);
                }

                //  It's gone
                return true;
            } catch (\Exception $_ex) {
                //  Can't delete? not good
                return false;
            }
        }

        return false;
    }
}
