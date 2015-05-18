<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

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
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Instance';
    /** @type string */
    protected $_resource = 'instance';

    protected $_prefix = 'v1';

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
        //echo 'here';
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

        $test = $this->_processDataRequest( 'instance_t', Instance::count(), $_columns, $_query );

        echo $test->count();

        return $this->_processDataRequest( 'instance_t', Instance::count(), $_columns, $_query );
    }

    public function create( array $viewData = [] )
    {
        $clusters = new Cluster();
        $clusters_list = $clusters->all();

        return View::make( 'app.instances.create' )->with( 'prefix', $this->_prefix )->with( 'clusters', $clusters_list );
    }

    public function edit( $id )
    {
        $clusters = new Cluster();
        $clusters_list = $clusters->all();

        $_columns = [
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
            ->select( $_columns )->where( 'instance_t.id', '=', $id );

        $test = $this->_processDataRequest( 'instance_t', Instance::count(), $_columns, $_query );

        return View::make( 'app.instances.edit' )->with( 'instance_id', $id )->with( 'prefix', $this->_prefix )->with(
            'instance',
            $test['response'][0]
        )->with( 'clusters', $clusters_list );
    }

    public function store()
    {

        $instance_name_text = Input::get( 'instance_name_text' );
        $instance_cluster_select = Input::get( 'instance_cluster_select' );
        $instance_policy_select = Input::get( 'instance_policy_select' );
        $instance_ownername_text = Input::get( 'instance_ownername_text' );

        $user = ServiceUser::where( 'email_addr_text', '=', $instance_ownername_text )->first();

        if ( Instance::where( 'instance_id_text', '=', Input::get( 'instance_name_text' ) )->exists() )
        {
            return 'EXISTS';
        }

        //return 'OK';

        /* */
        $create_instance = new Instance;

        $create_instance->user_id = $user->id;
        //$create_instance->vendor_id = 2;
        //$create_instance->vendor_image_id = 34;
        $create_instance->instance_id_text = $instance_name_text;
        $create_instance->instance_name_text = $instance_name_text;
        $create_instance->cluster_id = $instance_cluster_select;
        $create_instance->storage_id_text = '0';
        //$create_instance->cluster_id = $instance_cluster_select;

        if ( $create_instance->save() )
        {
            return 'OK';
        }
        else
        {
            return 'FAIL';
        }

    }

    public function index()
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

        $test = $this->_processDataRequest( 'instance_t', Instance::count(), $_columns, $_query );

        return View::make( 'app.instances' )->with( 'prefix', $this->_prefix )->with( 'instances', $test['response'] );

    }
}
