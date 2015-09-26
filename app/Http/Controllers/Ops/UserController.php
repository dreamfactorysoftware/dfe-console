<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\User;

class UserController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $tableName = 'user_t';
    /** @type string */
    protected $model = User::class;
    /** @type string */
    protected $resource = 'user';
}
