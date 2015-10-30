<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\Limit;

class LimitController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $tableName = 'limit_t';
    /** @type string */
    protected $model = Limit::class;
    /** @type string */
    protected $resource = 'limit';
}
