<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

class ClusterController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'cluster_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Cluster';
    /** @type string */
    protected $_resource = 'cluster';
}
