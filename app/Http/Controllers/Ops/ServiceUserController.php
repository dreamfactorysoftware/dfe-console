<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

class ServiceUserController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_tableName = 'service_user_t';
    /**
     * @type string
     */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\ServiceUser';
    /** @type string */
    protected $_resource = 'service-user';
}
