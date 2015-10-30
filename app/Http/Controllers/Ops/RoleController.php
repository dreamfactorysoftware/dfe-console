<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\Role;

class RoleController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $tableName = 'role_t';
    /** @type string */
    protected $model = Role::class;
    /** @type string */
    protected $resource = 'role';
}
