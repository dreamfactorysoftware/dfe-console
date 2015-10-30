<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\Mount;

class MountController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $tableName = 'mount_t';
    /** @type string */
    protected $model = Mount::class;
    /** @type string */
    protected $resource = 'mount';
}
