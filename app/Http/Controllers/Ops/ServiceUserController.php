<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\ServiceUser;

class ServiceUserController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $tableName = 'service_user_t';
    /**
     * @type string
     */
    protected $model = ServiceUser::class;
    /** @type string */
    protected $resource = 'service-user';
}
