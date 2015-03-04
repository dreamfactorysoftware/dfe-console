<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Response;

class InstanceController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_tableName = 'instance_t';
    /**
     * @type string
     */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\Instance';
    /** @type string */
    protected $_resource = 'instance';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    protected function _loadData()
    {
        $_columns =
            [
                'instance_t.id',
                'instance_t.instance_id_text',
                'cluster_t.cluster_id_text',
                'instance_t.create_date',
                'user_t.email_addr_text',
                'user_t.lmod_date',
            ];

        /** @type Builder $_query */
        $_query = Instance::join( 'user_t', 'instance_t.user_id', '=', 'user_t.id' )
            ->join( 'cluster_t', 'instance_t.cluster_id', '=', 'cluster_t.id' )
            ->select( $_columns );

        return $this->_processDataRequest( 'instance_t', Instance::count(), $_columns, $_query );
    }

}
