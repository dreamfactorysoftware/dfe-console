<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

class UserController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'user_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Auth\\User';
    /** @type string */
    protected $_resource = 'user';
}
