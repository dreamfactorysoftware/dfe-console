<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

class ServiceUserController extends ResourceController
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
