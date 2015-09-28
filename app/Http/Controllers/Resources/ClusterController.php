<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Enums\ServerTypes;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;
use DreamFactory\Enterprise\Database\Exceptions\DatabaseException;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\ClusterServer;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Server;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Session;
use Validator;

class ClusterController extends ViewController
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $tableName = 'cluster_t';
    /** @type string */
    protected $model = 'DreamFactory\\Enterprise\\Database\\Models\\Cluster';
    /** @type string */
    protected $resource = 'cluster';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Returns an array of the instances assigned to a cluster
     *
     * @param int|string $clusterId The cluster ID
     *
     * @return array
     */
    public function getInstances($clusterId)
    {
        $_cluster = $this->_findCluster($clusterId);
        $_rows = Instance::byClusterId($_cluster->id)->get(['id', 'instance_name_text']);

        $_response = [];

        /** @type Instance $_instance */
        foreach ($_rows as $_instance) {
            $_response[] = ['id' => $_instance->id, 'name' => $_instance->instance_name_text];
        }

        $this->debug('found ' . count($_response) . ' instance(s)');

        usort($_response,
            function ($a, $b){
                return strcasecmp($a['name'], $b['name']);
            });

        return $_response;
    }

    /**
     * Returns an array of servers NOT assigned to a cluster
     *
     * @param
     *
     * @return array
     */
    public function getAvailableServers()
    {
        $servers = Server::get(['id', 'server_type_id', 'server_id_text']);
        $cs = ClusterServer::get(['server_id']);

        $servers_all = [];
        $servers_in_use = [];

        $servers_db = [];
        $servers_web = [];
        $servers_app = [];

        foreach ($cs as $server) {
            $servers_in_use[] = intval($server->server_id);
        }

        foreach ($servers as $server) {
            if (!in_array($server->id, $servers_in_use)) {
                if ($server->server_type_id == 1) {
                    $servers_db[] = ['id' => $server->id, 'name' => $server->server_id_text];
                }

                if ($server->server_type_id == 2) {
                    $servers_web[] = ['id' => $server->id, 'name' => $server->server_id_text];
                }

                if ($server->server_type_id == 3) {
                    $servers_app[] = ['id' => $server->id, 'name' => $server->server_id_text];
                }

                $servers_all[] = intval($server->id);
            }
        }

        return ['web' => $servers_web, 'db' => $servers_db, 'app' => $servers_app];
    }

    /** @inheritdoc */
    public function create(array $viewData = [])
    {
        $servers = $this->getAvailableServers();

        return $this->renderView('app.clusters.create',
            [
                'db'  => $servers['db'],
                'web' => $servers['web'],
                'app' => $servers['app'],
            ]);
    }

    /**
     * @param int|string $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $servers = $this->getAvailableServers();
        $_cluster = $this->_findCluster($id);
        $_clusterServers = $this->_clusterServers($id);

        $_datas = [
            'web' => null,
            'db'  => null,
            'app' => null,
        ];

        foreach ($_clusterServers as $_type => $_servers) {
            $_serverType = strtolower(ServerTypes::nameOf($_type, false));

            foreach ($_servers as $_server) {
                $_datas[$_serverType] = [
                    'id'   => $_server->id,
                    'name' => $_server->server_id_text,
                ];
            }
        }

        return $this->renderView('app.clusters.edit',
            [
                'cluster_id' => $id,
                'cluster'    => $_cluster,
                'db'         => $servers['db'],
                'web'        => $servers['web'],
                'app'        => $servers['app'],
                'datas'      => $_datas,
            ]);
    }

    public function update($id)
    {
        $cluster_data = \Input::all();

        $_validator = Validator::make($cluster_data,
            [
                'cluster_id_text' => 'required|string',
                'subdomain_text'  => [
                    'required',
                    "Regex:/((https?|ftp)\:\/\/)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?(([a-z0-9-.]*)\.([a-z]{2,6}))|(([0-9]{1,3}\.){3}[0-9]{1,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?/i",
                ],
                'web_server_id'   => 'required|string',
                'db_server_id'    => 'required|string',
                'app_server_id'   => 'required|string',
            ]);

        if ($_validator->fails()) {

            $messages = $_validator->messages()->getMessages();

            $flash_message = '';

            foreach ($messages as $key => $value) {
                switch ($key) {

                    case 'cluster_id_text':
                        $flash_message = 'Name is blank or contain invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'subdomain_text':
                        $flash_message = 'Fixed DNS Subdomain is blank or format is invalid (use subdomain.domain.tld)';
                        break;
                    case 'web_server_id':
                        $flash_message = 'Select Web Server';
                        break;
                    case 'db_server_id':
                        $flash_message = 'Select Database Server';
                        break;
                    case 'app_server_id':
                        $flash_message = 'Select App Server';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');

            return redirect($this->makeRedirectUrl('clusters', $id . '/edit'))->withInput();
        }

        $servers = $cluster_data['_server_list'];

        unset($cluster_data['_method']);
        unset($cluster_data['_token']);
        unset($cluster_data['_server_list']);

        try {

            ClusterServer::where('cluster_id', '=', $id)->delete();

            $add = ['server_id' => intval($cluster_data['web_server_id']), 'cluster_id' => intval($id)];
            ClusterServer::create($add);

            $add = ['server_id' => intval($cluster_data['db_server_id']), 'cluster_id' => intval($id)];
            ClusterServer::create($add);

            $add = ['server_id' => intval($cluster_data['app_server_id']), 'cluster_id' => intval($id)];
            ClusterServer::create($add);

            unset($cluster_data['web_server_id']);
            unset($cluster_data['db_server_id']);
            unset($cluster_data['app_server_id']);

            /*
            $cluster_assigned_servers_array = [];

            if (!empty($servers)) {
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
            */

            if (!Cluster::find($id)->update($cluster_data)) {
                throw new DatabaseException('Unable to update cluster "' . $id . '"');
            }

            \Session::flash('flash_message',
                'The server "' . $cluster_data['cluster_id_text'] . '" was updated successfully!');
            \Session::flash('flash_type', 'alert-success');

            return \Redirect::to($this->makeRedirectUrl('clusters'));

            //return \Redirect::to($this->makeRedirectUrl('clusters',
            //['flash_message' => $result_text, 'flash_type' => $result_status]));

        } catch (QueryException $e) {
            //$res_text = $e->getMessage();
            \Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
            \Session::flash('flash_type', 'alert-danger');

            return redirect('/' . $this->getUiPrefix() . '/clusters/' . $id . '/edit')->withInput();
        }
    }

    public function store(Request $request)
    {
        $_input = \Input::all();

        $_validator = Validator::make($_input,
            [
                'cluster_id_text' => 'required|string',
                'subdomain_text'  => [
                    "required",
                    "Regex:/((https?|ftp)\:\/\/)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?(([a-z0-9-.]*)\.([a-z]{2,6}))|(([0-9]{1,3}\.){3}[0-9]{1,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?/i",
                ],
                'web_server_id'   => 'required|string',
                'db_server_id'    => 'required|string',
                'app_server_id'   => 'required|string',
            ]);

        if ($_validator->fails()) {

            $messages = $_validator->messages()->getMessages();

            $flash_message = '';

            foreach ($messages as $key => $value) {
                switch ($key) {

                    case 'cluster_id_text':
                        $flash_message = 'Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'subdomain_text':
                        $flash_message = 'DNS Subdomain is blank or format is invalid (use subdomain.domain.tld)';
                        break;
                    case 'web_server_id':
                        $flash_message = 'Select Web Server';
                        break;
                    case 'db_server_id':
                        $flash_message = 'Select Database Server';
                        break;
                    case 'app_server_id':
                        $flash_message = 'Select App Server';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/clusters/create')->withInput();
        }

        try {
            $cluster = new Cluster;
            $cluster->cluster_id_text = $_input['cluster_id_text'];
            $cluster->subdomain_text = $_input['subdomain_text'];
            $cluster->save();

            $add = ['server_id' => intval($_input['web_server_id']), 'cluster_id' => intval($cluster->id)];
            ClusterServer::create($add);

            $add = ['server_id' => intval($_input['db_server_id']), 'cluster_id' => intval($cluster->id)];
            ClusterServer::create($add);

            $add = ['server_id' => intval($_input['app_server_id']), 'cluster_id' => intval($cluster->id)];
            ClusterServer::create($add);

            $result_text = 'The cluster "' . $_input['cluster_id_text'] . '" was created successfully!';
            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->getUiPrefix();
            $_redirect .= '/clusters';

            return \Redirect::to($_redirect)->with('flash_message', $result_text)->with('flash_type', $result_status);
        } catch (QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/clusters/create')->withInput();
        }
    }

    public function destroy($ids)
    {
        try {
            $cluster_names = [];

            if ($ids == 'multi') {
                $params = \Input::all();
                $selected = $params['_selected'];
                $id_array = explode(',', $selected);
            } else {
                $id_array = explode(',', $ids);
            }

            foreach ($id_array as $id) {
                $cluster = Cluster::where('id', '=', $id);
                $cluster_name = $cluster->get(['cluster_id_text']);
                array_push($cluster_names, '"' . $cluster_name[0]->cluster_id_text . '"');
                $cluster->delete();
                ClusterServer::where('server_id', '=', intval($id))->delete();
            }

            if (count($id_array) > 1) {
                $clusters = '';
                foreach ($cluster_names as $i => $name) {
                    $clusters .= $name;

                    if (count($cluster_names) > $i + 1) {
                        $clusters .= ', ';
                    }
                }

                $result_text = 'The servers ' . $clusters . ' were deleted successfully!';
            } else {
                $result_text = 'The server ' . $cluster_names[0] . ' was deleted successfully!';
            }

            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->getUiPrefix();
            $_redirect .= '/clusters';

            return \Redirect::to($_redirect)->with('flash_message', $result_text)->with('flash_type', $result_status);
        } catch (QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message', 'An error occurred! Please try again.');
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/clusters')->withInput();
        }
    }

    public function index()
    {
        $_clusters = Cluster::join('cluster_server_asgn_t', 'cluster_id', '=', 'id')->distinct()->get([
            'cluster_t.*',
            'cluster_id',
        ]);

        $_excluded = [];

        foreach ($_clusters as $_cluster) {
            $_excluded[] = $_cluster->id;
        }

        $_notAssigned = Cluster::whereNotIn('id', $_excluded)->get();

        $result = array_merge(json_decode($_clusters), json_decode($_notAssigned));

        return $this->renderView('app.clusters', ['clusters' => $result]);
    }

}
