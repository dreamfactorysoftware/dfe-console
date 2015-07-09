<?php namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Contracts\PortableData;
use DreamFactory\Enterprise\Common\Exceptions\DiskException;
use DreamFactory\Enterprise\Services\Provisioners\BaseStorageProvisioner;
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
class StorageProvisioner extends BaseStorageProvisioner implements PortableData
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string My ID!
     */
    const PROVISIONER_ID = 'rave';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**@inheritdoc */
    protected function doProvision($request)
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
            foreach (config('provisioning.public-paths', []) as $_path) {
                !$_filesystem->has($_check = $_instanceRootPath . DIRECTORY_SEPARATOR . $_path) &&
                $_filesystem->createDir($_check);
            }

            foreach (config('provisioning.private-paths', []) as $_path) {
                !$_filesystem->has($_check = $_privatePath . DIRECTORY_SEPARATOR . $_path) &&
                $_filesystem->createDir($_check);
            }

            foreach (config('provisioning.owner-private-paths', []) as $_path) {
                !$_filesystem->has($_check = $_ownerPrivatePath . DIRECTORY_SEPARATOR . $_path) &&
                $_filesystem->createDir($_check);
            }
        } catch (\Exception $_ex) {
            $this->error('! error creating directory structure: ' . $_ex->getMessage());
            throw $_ex;
        }

        $this->debug('instance "' . $_instance->instance_id_text . '"storage created');
        $this->debug('        private: ' . $_privatePath);
        $this->debug('  owner private: ' . $_ownerPrivatePath);

        $this->privatePath = $_privatePath;
        $this->ownerPrivatePath = $_ownerPrivatePath;
    }

    /** @inheritdoc */
    protected function doDeprovision($request)
    {
        $_instance = $request->getInstance();
        $_filesystem = $request->getStorage();
        $_storagePath = $_instance->instance_id_text;

        //  I'm not sure how hard this tries to delete the directory
        if (!$_filesystem->has($_storagePath)) {
            $this->notice('! unable to stat storage path "' . $_storagePath . '". not deleting!');

            return false;
        }

        if (!$_filesystem->deleteDir($_storagePath)) {
            $this->error('! error deleting storage area "' . $_storagePath . '"');

            return false;
        }

        $this->debug('instance "' . $_instance->instance_id_text . '"storage removed');

        return true;
    }

    /** @inheritdoc */
    public function import($request)
    {
        $_instance = $request->getInstance();
        $_mount = $_instance->getStorageMount();
        $_target = $request->getTarget();

        if (!($_mount instanceof Filesystem)) {
            $_mount = new Filesystem(new ZipArchiveAdapter($_mount));
        }

        //  If "clean" == true, storage is wiped clean before restore
        if (true === $request->get('clean', false)) {
            $_target->deleteDir('./');
        }

        //  Extract the files
        $_restored = [];

        foreach ($_mount->listContents() as $_file) {
            $_restored[$_file['file']] = $_target->put($_file['file'], $_mount->read($_file['file']));
        }

        return $_restored;
    }

    /** @inheritdoc */
    public function export($request)
    {
        $_instance = $request->getInstance();
        $_mount = $_instance->getStorageMount();
        $_target = $request->getTarget();
        $_tag = date('YmdHis') . '.' . $_instance->instance_id_text;

        if (empty($_target)) {
            $_path = $this->getWorkPath($_tag, true);
            $_file = $_tag;
        } else {
            //  Make sure the output file is copacetic
            $_path = dirname($_target);
            $_file = basename($_target);

            if (!\DreamFactory\Library\Utility\FileSystem::ensurePath($_path)) {
                throw new DiskException('Unable to write to export file "' . $_target . '".');
            }
        }

        //  Create our zip container
        $_file = static::archiveTree($_mount, $_path . DIRECTORY_SEPARATOR . $_file);

        //  Copy it over to the snapshot area
        $this->writeStream($_instance->getSnapshotMount(), $_path . DIRECTORY_SEPARATOR . $_file, $_file);
        $this->deleteWorkPath($_tag);

        //  The name of the file in the snapshot mount
        return $_file;
    }

}