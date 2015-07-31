<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

class LimitController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'limit_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Limit';
    /** @type string */
    protected $_resource = 'limit';
}
