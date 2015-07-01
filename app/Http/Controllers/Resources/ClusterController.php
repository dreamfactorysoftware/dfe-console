<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use Session;
use Validator;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\ClusterServer;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Services\Enums\ServerTypes;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class ClusterController extends ResourceController
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
    /**
     * @type string
     */
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
    public function create(array $viewData = [])
    {
        return \View::make('app.clusters.create', ['prefix' => $this->_prefix]);
    }

    /**
     * @param int|string $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $_contexts = [ServerTypes::DB => 'primary', ServerTypes::WEB => 'success', ServerTypes::APP => 'warning'];

        $_cluster = $this->_findCluster($id);
        $_clusterServers = $this->_clusterServers($_cluster->id);

        $_ids = [];

        $_rows = ClusterServer::join('server_t', 'id', '=', 'server_id')->get([
                'server_t.id',
                'server_t.server_id_text',
                'server_t.server_type_id',
                'server_t.config_text',
                'cluster_server_asgn_t.cluster_id',
            ]);

        /** @type Server $_server */
        foreach ($_rows as $_server) {
            if (ServerTypes::DB == $_server->server_type_id) {
                if (!property_exists($_server, 'config_text')) {
                    if (!array_key_exists('multi-assign', json_decode($_server->config_text, true))) {
                        $_ids[] = intval($_server->id);
                    } else {
                        if ($id == $_server->cluster_id) {
                            $_ids[] = intval($_server->id);
                        }
                    }
                } else {
                    $_ids[] = intval($_server->id);
                }
            } else {
                $_ids[] = intval($_server->id);
            }
        }

        $_data = $_dropdown = $_dropdown_all = [];

        foreach ($_clusterServers as $_type => $_servers) {
            $_serverType = strtoupper(ServerTypes::nameOf($_type, false));

            foreach ($_servers as $_server) {
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

        $_servers_all = Server::all();

        if (!empty($_servers_all)) {
            $_index1 = 0;
            $_index2 = 0;

            foreach ($_servers_all as $_server) {
                $_serverType = strtoupper(ServerTypes::nameOf($_type = $_server->server_type_id, false));

                $_label = <<<HTML
<div><span class="label label-{$_contexts[$_type]}">{$_serverType}</span></div>
HTML;

                $_button = <<<HTML
<button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" id="cluster_button_" onclick="removeServer({$_server->id});" value="delete" style="width: 25px"></button>
HTML;

                if (!in_array(intval($_server->id), $_ids)) {
                    $_dropdown[] = [
                        $_index1++,
                        intval($_server->id),
                        $_server->server_id_text,
                        strtoupper($_serverType),
                        $_label,
                        $_button,
                    ];
                }

                $_dropdown_all[] = [
                    $_index2++,
                    intval($_server->id),
                    $_server->server_id_text,
                    strtoupper($_serverType),
                    $_label,
                    $_button,
                ];
            }
        }

        return \View::make('app.clusters.edit', [
                'cluster_id'          => $id,
                'prefix'              => $this->_prefix,
                'cluster'             => $_cluster,
                'servers'             => json_encode($_data),
                'server_dropdown_all' => json_encode($_dropdown_all),
                'server_dropdown'     => $_dropdown,
            ]);
    }

    public function update($id)
    {
        $cluster_data = Input::all();

        $validator = Validator::make($cluster_data, [
            'cluster_id_text' => 'required|string',
            'subdomain_text' => array("required", "Regex:/((https?|ftp)\:\/\/)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?(([a-z0-9-.]*)\.([a-z]{2,6}))|(([0-9]{1,3}\.){3}[0-9]{1,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?/i")
        ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach($messages as $key => $value){
                switch ($key) {

                    case 'cluster_id_text':
                        $flash_message = 'Name is blank or contain invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'subdomain_text':
                        $flash_message = 'Host is blank or format is invalid (use subdomain.domain.tld)';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/clusters/'.$id.'/edit')->withInput();
        }


        $servers = $cluster_data['_server_list'];

        unset($cluster_data['_method']);
        unset($cluster_data['_token']);
        unset($cluster_data['_server_list']);


        try {
        $cluster_assigned_servers_array = [];

        if ($servers != '') {
            $cluster_assigned_servers_array = array_map('intval', explode(',', $servers));
        }

        $cluster_server_list = ClusterServer::where('cluster_id', '=', $id)->select(['server_id'])->get();

        $server_ids = [];

        foreach ($cluster_server_list as $value) {
            array_push($server_ids, intval($value->server_id));
        }

        $servers_remove = array_diff($server_ids, $cluster_assigned_servers_array);

        foreach (array_values($servers_remove) as $value) {
            ClusterServer::where('server_id', '=', intval($value))->where('cluster_id', '=', intval($id))->delete();
        }

        $servers_add = array_diff($cluster_assigned_servers_array, $server_ids);

        foreach (array_values($servers_add) as $value) {
            $add = ['server_id' => intval($value), 'cluster_id' => intval($id)];
            ClusterServer::create($add);
        }

        $cluster = Cluster::find($id);
        $cluster->update($cluster_data);

            $result_text = 'The server "'.$cluster_data['cluster_id_text'].'" was updated successfully!';
            $result_status = 'alert-success';

        $_redirect = '/';
        $_redirect .= $this->_prefix;
        $_redirect .= '/clusters';

            return Redirect::to($_redirect)
                ->with('flash_message', $result_text)
                ->with('flash_type', $result_status);
        }
        catch (\Illuminate\Database\QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/clusters/'.$id.'/edit')->withInput();
        }
    }

    public function store()
    {
        $input = Input::all();

        $validator = Validator::make($input, [
            'cluster_id_text' => 'required|string',
            'subdomain_text' => array("required", "Regex:/((https?|ftp)\:\/\/)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?(([a-z0-9-.]*)\.([a-z]{2,6}))|(([0-9]{1,3}\.){3}[0-9]{1,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?/i")
        ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach($messages as $key => $value){
                switch ($key) {

                    case 'cluster_id_text':
                        $flash_message = 'Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'subdomain_text':
                        $flash_message = 'DNS Subdomain is blank or format is invalid (use subdomain.domain.tld)';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/clusters/create')->withInput();
        }

        try{
            $create_cluster = new Cluster;
            $create_cluster->create($input);

            $result_text = 'The cluster "'.$input['cluster_id_text'].'" was created successfully!';
            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->_prefix;
            $_redirect .= '/clusters';

            return Redirect::to($_redirect)
                ->with('flash_message', $result_text)
                ->with('flash_type', $result_status);
        }
        catch (\Illuminate\Database\QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/clusters/create')->withInput();
        }
    }

    public function destroy($ids)
    {
        try {
            $id_array = [];
            $cluster_names = [];

            if ($ids == 'multi') {
                $params = Input::all();
                $selected = $params['_selected'];
                $id_array = explode(',', $selected);
            } else {
                $id_array = explode(',', $ids);
            }

            foreach ($id_array as $id) {
                $cluster = Cluster::where('id', '=', $id);
                $cluster_name = $cluster->get(['cluster_id_text']);
                array_push($cluster_names, '"'.$cluster_name[0]->cluster_id_text.'"');
                $cluster->delete();
                ClusterServer::where('server_id', '=', intval($id))->delete();
            }

            if(count($id_array) > 1)
            {
                $clusters = '';
                foreach ($cluster_names as $i => $name)
                {
                    $clusters .= $name;

                    if (count($cluster_names) > $i + 1)
                    {
                        $clusters .= ', ';
                    }
                }

                $result_text = 'The servers '.$clusters.' were deleted successfully!';
            }
            else
            {
                $result_text = 'The server '.$cluster_names[0].' was deleted successfully!';
            }

            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->_prefix;
            $_redirect .= '/clusters';

            return Redirect::to($_redirect)
                ->with('flash_message', $result_text)
                ->with('flash_type', $result_status);
        }
        catch (\Illuminate\Database\QueryException $e) {
            //$res_text = $e->getMessage(); 
            Session::flash('flash_message', 'An error occurred! Please try again.');
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/clusters')->withInput();
        }
    }

    public function index()
    {
        $asgn_clusters = Cluster::join('cluster_server_asgn_t', 'cluster_id', '=', 'id')->distinct()->get([
            'cluster_t.*',
            'cluster_id',
        ]);

        $excludes = [];

        foreach ($asgn_clusters as $obj) {
            array_push($excludes, $obj->id);
        }

        $not_asgn_clusters = Cluster::whereNotIn('id', $excludes)->get();

        $result = array_merge(json_decode($asgn_clusters), json_decode($not_asgn_clusters));

        return View::make('app.clusters')->with('prefix', $this->_prefix)->with('clusters', $result);
    }

}
