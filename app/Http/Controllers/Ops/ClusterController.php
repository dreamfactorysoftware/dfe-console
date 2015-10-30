<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\Cluster;

class ClusterController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $tableName = 'cluster_t';
    /** @type string */
    protected $model = Cluster::class;
    /** @type string */
    protected $resource = 'cluster';
}
