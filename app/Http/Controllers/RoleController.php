<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

class RoleController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'role_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\Role';
    /** @type string */
    protected $_resource = 'role';
}
