<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Enums\ServerTypes;
use DreamFactory\Library\Fabric\Database\Models\Deploy;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

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
    /** @type string */
    protected $_prefix = 'v1';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return mixed
     */
    public function create()
    {
        return \View::make( 'app.clusters.create' )->with( 'prefix', $this->_prefix );
    }

    public function edit( $id )
    {
        $_contexts = [ServerTypes::DB => 'primary', ServerTypes::WEB => 'success', ServerTypes::APP => 'warning'];
        $_cluster = $this->_findCluster( $id );
        $_clusterServers = $this->_clusterServers( $_cluster->id );
        $_data = $_dropdown = $_ids = [];

        foreach ( $_clusterServers as $_type => $_servers )
        {
            $_serverType = ServerTypes::nameOf( $_type );

            foreach ( $_servers as $_server )
            {
                $_label = <<<HTML
<div><span class="label label-{$_contexts[$_type]}">{$_serverType}</span></div>
HTML;

                $_button = <<<HTML
<button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" id="cluster_button_" onclick="removeServer({$_server->id});" value="delete" style="width: 25px"></button>
HTML;

                $_data[] = [
                    $_server->id,
                    $_button,
                    $_server->server_id_text,
                    $_label,
                ];
            }
        }

        $_unassigned = Deploy\ClusterServer::where( 'cluster_id', '!=', $_cluster->id )->with( 'server' )->get();

        if ( !empty( $_unassigned ) )
        {
            $_index = 0;

            foreach ( $_unassigned as $_server )
            {
                $_type = $_server->server->server_type_id;
                $_serverType = ServerTypes::nameOf( $_type );

                $_label = <<<HTML
<div><span class="label label-{$_contexts[$_type]}">{$_serverType}</span></div>
HTML;

                $_button = <<<HTML
<button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" id="cluster_button_" onclick="removeServer({$_server->id});" value="delete" style="width: 25px"></button>
HTML;

                $_dropdown[] = [
                    $_index++,
                    intval( $_server->id ),
                    $_server->server_id_text,
                    $_serverType,
                    $_label,
                    $_button
                ];
            }
        }

        return \View::make(
            'app.clusters.edit',
            [
                'cluster_id'          => $id,
                'prefix'              => $this->_prefix,
                'cluster'             => $_cluster,
                'servers'             => json_encode( $_data ),
                'server_dropdown_str' => json_encode( $_dropdown ),
                'server_dropdown'     => $_dropdown
            ]
        );
    }

    public function update( $id )
    {
        $cluster_name_text = Input::get( 'cluster_name_text' );
        $cluster_subdomain_text = Input::get( 'cluster_subdomain_text' );
        $cluster_instancecount_text = Input::get( 'cluster_instancecount_text' );
        $cluster_assigned_servers = Input::get( 'cluster_assigned_servers' );

        $cluster_assigned_servers_array = array_map( 'intval', explode( ',', $cluster_assigned_servers ) );

        $cluster_servers = new Deploy\ClusterServer;
        $cluster_server_list = $cluster_servers->where( 'cluster_id', '=', $id )->select( ['server_id'] )->get();

        $server_ids = [];

        foreach ( $cluster_server_list as $value )
        {
            array_push( $server_ids, intval( $value->server_id ) );
        }

        $servers_remove = array_diff( $server_ids, $cluster_assigned_servers_array );
        $servers_remove = array_values( $servers_remove );

        foreach ( $servers_remove as $value )
        {
            $cs = new Deploy\ClusterServer;
            $cs->where( 'server_id', '=', intval( $value ) )->where( 'cluster_id', '=', intval( $id ) )->delete();
        }

        $servers_add = array_diff( $cluster_assigned_servers_array, $server_ids );
        $servers_add = array_values( $servers_add );

        foreach ( $servers_add as $value )
        {
            $cs = new Deploy\ClusterServer;
            $cs->server_id = intval( $value );
            $cs->cluster_id = intval( $id );
            $cs->save();
        }

        $clusters = new Deploy\Cluster;
        $cluster = $clusters->find( $id );

        $cluster->cluster_id_text = $cluster_name_text;
        $cluster->subdomain_text = $cluster_subdomain_text;

        $cluster->save();

        return 'OK';
    }

    public function store()
    {

        $cluster_name_text = Input::get( 'cluster_name_text' );
        $cluster_subdomain_text = Input::get( 'cluster_subdomain_text' );
        $cluster_instancecount_text = Input::get( 'cluster_instancecount_text' );

        if ( Deploy\Cluster::where( 'cluster_id_text', '=', Input::get( 'cluster_name_text' ) )->exists() )
        {
            return 'EXISTS';
        }

        $create_cluster = new Deploy\Cluster;

        //$create_cluster->user_id = null;
        $create_cluster->cluster_id_text = $cluster_name_text;
        $create_cluster->subdomain_text = $cluster_subdomain_text;

        if ( $create_cluster->save() )
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

        $clusters = new Deploy\Cluster;

        //echo $clusters->all();

        return View::make( 'app.clusters' )->with( 'prefix', $this->_prefix )->with( 'clusters', $clusters->all() );//take(10)->get());
    }

}
