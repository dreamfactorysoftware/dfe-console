<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

class PolicyController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'policy_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Limit';
    /** @type string */
    protected $_resource = 'policy';
}
