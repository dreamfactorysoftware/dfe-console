<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\ServiceUser;

class ServiceUserController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $tableName = 'service_user_t';
    /** @inheritdoc */
    protected $model = ServiceUser::class;
    /** @inheritdoc */
    protected $resource = 'service-user';
}
