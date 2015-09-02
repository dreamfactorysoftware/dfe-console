<?php namespace DreamFactory\Enterprise\Services\Provisioners\Rave;

use DreamFactory\Enterprise\Common\Contracts\PortableData;
use DreamFactory\Enterprise\Common\Facades\InstanceStorage;
use DreamFactory\Enterprise\Common\Traits\Guzzler;
use DreamFactory\Enterprise\Services\Provisioners\BaseStorageProvisioner;
use DreamFactory\Library\Utility\Disk;
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
    //* Traits
    //******************************************************************************

    use Guzzler;

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
        $_instanceRootPath = trim($_instance->instance_id_text);

        //  The user's and instance's private path
        $_privateName = InstanceStorage::getPrivatePathName();
        $_ownerPrivatePath = $_privateName;
        $_privatePath = Disk::segment([$_instanceRootPath, $_privateName]);

        //  Make sure everything exists
        try {
            logger('* private path: ' . $_privatePath);
            !$_filesystem->has($_privatePath) && $_filesystem->createDir($_privatePath);

            logger('* owner private path: ' . $_ownerPrivatePath);
            !$_filesystem->has($_ownerPrivatePath) && $_filesystem->createDir($_ownerPrivatePath);

            //  Now ancillary sub-directories
            foreach (config('provisioning.public-paths', []) as $_path) {
                $_path = Disk::segment([$_instanceRootPath, $_path]);
                logger('* public path: ' . $_path);
                !$_filesystem->has($_path) && $_filesystem->createDir($_path);
            }

            foreach (config('provisioning.private-paths', []) as $_path) {
                $_path = Disk::segment([$_privatePath, $_path]);
                logger('* private path: ' . $_path);
                !$_filesystem->has($_path) && $_filesystem->createDir($_path);
            }

            foreach (config('provisioning.owner-private-paths', []) as $_path) {
                $_path = Disk::segment([$_ownerPrivatePath, $_path]);
                logger('* owner private path: ' . $_path);
                !$_filesystem->has($_path) && $_filesystem->createDir($_path);
            }
        } catch (\Exception $_ex) {
            $this->error('! error creating directory structure: ' . $_ex->getMessage());
            logger('File system: ' . print_r($_filesystem, true));
            throw $_ex;
        }

        $this->debug('Storage provisioned for instance "' . $_instance->instance_id_text . '"');

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
        $_from = null;
        $_instance = $request->getInstance();
        $_mount = $_instance->getStorageMount();

        //  Grab the target (zip archive) and pull out the target of the import
        $_zip = $request->getTarget();
        /** @var \ZipArchive $_archive */
        /** @noinspection PhpUndefinedMethodInspection */
        $_archive = $_zip->getAdapter()->getArchive();

        $_path = null;

        foreach ($_zip->listContents() as $_file) {
            if ('dir' != $_file['type'] && false !== strpos($_file['path'], '.storage.zip')) {
                $_from = Disk::segment([sys_get_temp_dir(), 'dfe', 'import', sha1($_file['path'])], true);

                if (!$_archive->extractTo($_from, $_file['path'])) {
                    throw new \RuntimeException('Unable to unzip archive file "' . $_file['path'] . '" from snapshot.');
                }

                $_path = Disk::path([$_from, $_file['path']], false);

                if (!$_path || !file_exists($_path)) {
                    throw new \InvalidArgumentException('$from file "' . $_file['path'] . '" missing or unreadable.');
                }

                $_from = new Filesystem(new ZipArchiveAdapter($_path));

                break;
            }
        }

        if (!($_mount instanceof Filesystem)) {
            $_mount = new Filesystem(new ZipArchiveAdapter($_mount));
        }

        //  If "clean" == true, storage is wiped clean before restore
        if (true === $request->get('clean', false)) {
            $_mount->deleteDir('./');
        }

        //  Extract the files
        $_restored = [];

        /** @type Filesystem $_archive */
        foreach ($_from->listContents() as $_file) {
            $_filename = $_file['path'];

            if ('dir' == array_get($_file, 'type')) {
                $_mount->createDir($_filename);
            } else {
                $_mount->writeStream($_filename, $_archive->readStream($_filename));
            }

            $_restored[] = $_file;
        }

        unset($_from);
        $_path && is_dir(dirname($_path)) && Disk::deleteTree(dirname($_path));

        return $_restored;
    }

    /** @inheritdoc */
    public function export($request)
    {
        $_instance = $request->getInstance();
        $_mount = $_instance->getStorageMount();
        $_tag = date('YmdHis') . '.' . $_instance->instance_id_text;
        $_workPath = $this->getWorkPath($_tag, true);
        $_target = $_tag . '.storage.zip';

        //  Create our zip container
        if (false !== ($_file = static::archiveTree($_mount, $_workPath . DIRECTORY_SEPARATOR . $_target))) {
            //  Copy it over to the snapshot area
            $this->writeStream($_instance->getSnapshotMount(), $_workPath . DIRECTORY_SEPARATOR . $_target, $_target);
        }

        !$request->get('keep-work', false) && $this->deleteWorkPath($_tag);

        //  The name of the file in the snapshot mount
        return $_file;
    }
}