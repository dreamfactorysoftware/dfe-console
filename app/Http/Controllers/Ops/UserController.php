<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

class UserController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'user_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\User';
    /** @type string */
    protected $_resource = 'user';
}
