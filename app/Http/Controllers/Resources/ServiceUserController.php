<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\ServiceUser;

class ServiceUserController extends ViewController
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
