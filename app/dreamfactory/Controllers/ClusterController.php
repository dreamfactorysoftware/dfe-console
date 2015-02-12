<?php
namespace DreamFactory\Enterprise\Console\Controllers;

class ClusterController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'cluster_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\Cluster';
    /** @type string */
    protected $_resource = 'cluster';

}
