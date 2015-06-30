<?php namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Contracts\Portability;
use DreamFactory\Enterprise\Common\Contracts\PrivatePathAware;
use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\Archivist;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Providers\ProvisioningServiceProvider;
use DreamFactory\Enterprise\Services\Provisioners\ProvisioningRequest;
use DreamFactory\Library\Utility\Exceptions\FileSystemException;
use League\Flysystem\Filesystem;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

/**
 * DreamFactory Enterprise(tm) and Services Platform File System
 *
 * The default functionality (static::$partitioned is set to TRUE) of this resolver is to provide partitioned
 * layout paths for the hosted storage area. The structure generated is as follows:
 *
 * /mount_point                             <----- Mount point/absolute path of storage area (i.e. "/")
 *      /storage                            <----- Root directory of hosted storage (i.e. "/data/storage")
 *          /zone                           <----- The storage zones (ec2.us-east-1a, ec2.us-west-1b, local, etc.)
 *              /[00-ff]                    <----- The first two bytes of hashes within (the partition)
 *                  /owner-hash
 *                      /.private           <----- owner private storage root
 *                      /instance-hash      <----- Instance storage root
 *                          /.private       <----- Instance private storage root
 *
 * Example paths:
 *
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/.private
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/applications
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/plugins
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private/.cache
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private/config
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private/scripts
 * /data/storage/ec2.us-east-1a/33/33f58e59068f021c975a1cac49c7b6818de9df5831d89677201b9c3bd98ee1ed/bender/.private/scripts.user
 */
class StorageProvisioner extends BaseService implements ResourceProvisioner, PrivatePathAware, Portability
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation, Archivist;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The instance's private path
     */
    protected $privatePath = null;
    /**
     * @type string The user's private path
     */
    protected $ownerPrivatePath = null;
    /**
     * @type bool Indicates if the storage I govern is hosted or standalone
     */
    protected $hostedStorage = true;
    /**
     * @type array The map of storage segments
     */
    protected $storageMap = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function boot()
    {
        parent::boot();

        $this->setLumberjackPrefix(ProvisioningServiceProvider::IOC_NAME . '.' . $this->getProvisionerId());
    }

    /**
     * @return array
     */
    public function getStorageMap()
    {
        return $this->storageMap;
    }

    /**
     * Returns the id, config key, or short name, of this provisioner.
     *
     * @return string The id of this provisioner
     */
    public function getProvisionerId()
    {
        return InstanceProvisioner::PROVISIONER_ID;
    }

    /** @inheritdoc */
    public function provision($request, $options = [])
    {
        //  Make structure
        $this->createInstanceStorage($request, $options);
    }

    /** @inheritdoc */
    public function deprovision($request, $options = [])
    {
        //  '86 structure
        $this->removeInstanceStorage($request, $options);
    }

    /**
     * Create storage structure in $filesystem
     *
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @throws \Exception
     */
    protected function createInstanceStorage($request, $options = [])
    {
        //  Wipe existing stuff
        $_instance = $request->getInstance();
        $_filesystem = $request->getStorage();

        //******************************************************************************
        //* Directories are all relative to the request's storage file system
        //******************************************************************************

        //  The instance's base storage path
        $_instanceRootPath = $_instance->instance_id_text;
        $_privatePathName = \InstanceStorage::getPrivatePathName();

        //  The instance's private path
        $_privatePath = $_instance->instance_id_text . DIRECTORY_SEPARATOR . $_privatePathName;

        //  The user's private path. Same as instance's when non-hosted
        $_ownerPrivatePath = $_privatePathName;

        //  Make sure everything exists
        try {
            !$_filesystem->has($_privatePath) && $_filesystem->createDir($_privatePath);
            !$_filesystem->has($_ownerPrivatePath) && $_filesystem->createDir($_ownerPrivatePath);

            //  Now ancillary sub-directories
            foreach (config('dfe.provisioning.public-paths', []) as $_path) {
                !$_filesystem->has($_check = $_instanceRootPath . DIRECTORY_SEPARATOR . $_path) &&
                $_filesystem->createDir($_check);
            }

            foreach (config('dfe.provisioning.private-paths', []) as $_path) {
                !$_filesystem->has($_check = $_privatePath . DIRECTORY_SEPARATOR . $_path) &&
                $_filesystem->createDir($_check);
            }

            foreach (config('dfe.provisioning.owner-private-paths', []) as $_path) {
                !$_filesystem->has($_check = $_ownerPrivatePath . DIRECTORY_SEPARATOR . $_path) &&
                $_filesystem->createDir($_check);
            }
        } catch (\Exception $_ex) {
            \Log::error('Error creating directory structure: ' . $_ex->getMessage());
            throw $_ex;
        }

        \Log::debug('    * provisioner: instance storage created');
        \Log::debug('      * private path:       ' . $_privatePath);
        \Log::debug('      * owner private path: ' . $_ownerPrivatePath);

        $this->privatePath = $_privatePath;
        $this->ownerPrivatePath = $_ownerPrivatePath;
    }

    /**
     * Delete storage of an instance
     *
     * @param ProvisioningRequest $request
     * @param array               $options
     *
     * @return bool
     */
    protected function removeInstanceStorage($request, $options = [])
    {
        $_instance = $request->getInstance();
        $_filesystem = $request->getStorage();
        $_storagePath = $_instance->instance_id_text;

        //  I'm not sure how hard this tries to delete the directory
        if (!$_filesystem->has($_storagePath)) {
            $this->notice('unable to stat storage path "' . $_storagePath . '". not deleting!');

            return false;
        }

        if (!$_filesystem->deleteDir($_storagePath)) {
            $this->error('error deleting storage area "' . $_storagePath . '"');

            return false;
        }

        $this->debug('storage area "' . $_storagePath . '" removed');

        return true;
    }

    /** @inheritdoc */
    public function import($request, $from, $options = [])
    {
        $_instance = $request->getInstance();
        $_target = $_instance->getStorageMount();

        if (!($from instanceof Filesystem)) {
            $from = new Filesystem(new ZipArchiveAdapter($from));
        }

        //  If "clean" == true, storage is wiped clean before restore
        if (true === array_get($options, 'clean', false)) {
            $_target->deleteDir('./');
        }

        //  Extract the files
        $_restored = [];

        foreach ($from->listContents() as $_file) {
            $_restored[$_file['file']] = $_target->put($_file['file'], $from->read($_file['file']));
        }

        return $_restored;
    }

    /** @inheritdoc */
    public function export($request, $to, $options = [])
    {
        $_instance = $request->getInstance();
        $_source = $_instance->getStorageMount();

        //  Make sure the output file is copacetic
        $_path = dirname($to);
        $_file = basename($to);

        if (!\DreamFactory\Library\Utility\FileSystem::ensurePath($_path)) {
            throw new FileSystemException('Unable to write to export file "' . $to . '".');
        }

        //  Create our zip container
        return $this->archiveTree($_source, $_path . DIRECTORY_SEPARATOR . $_file);
    }

    /** @inheritdoc */
    public function getPrivatePath($append = null)
    {
        return $this->privatePath .
        ($append ? DIRECTORY_SEPARATOR . ltrim($append, DIRECTORY_SEPARATOR . ' ') : $append);
    }

    /** @inheritdoc */
    public function getOwnerPrivatePath($append = null)
    {
        //  I hate doing this, but it will make this service more streamlined...
        return ($this->hostedStorage ? $this->ownerPrivatePath : $this->getPrivatePath()) .
        ($append ? DIRECTORY_SEPARATOR . ltrim($append, DIRECTORY_SEPARATOR . ' ') : $append);
    }

    /** @inheritdoc */
    public function getOwnerHash(Instance $instance)
    {
        return $instance->user->getHash();
    }

    /**
     * @return boolean
     */
    public function isHostedStorage()
    {
        return $this->hostedStorage;
    }

    /**
     * @param boolean $hostedStorage
     *
     * @return $this
     */
    public function setHostedStorage($hostedStorage)
    {
        $this->hostedStorage = $hostedStorage;

        return $this;
    }

}