<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\AppKey;

class AppKeyController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $tableName = 'app_key_t';
    /** @type string */
    protected $model = AppKey::class;
    /** @type string */
    protected $resource = 'app-key';
}
