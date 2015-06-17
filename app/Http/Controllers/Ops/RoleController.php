<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

class RoleController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'role_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Role';
    /** @type string */
    protected $_resource = 'role';
}
