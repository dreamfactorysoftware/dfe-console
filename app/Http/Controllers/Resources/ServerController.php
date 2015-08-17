<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\ClusterServer;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Database\Models\ServerType;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Session;
use Validator;

class ServerController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_tableName = 'server_t';
    /**
     * @type string
     */
    protected $_model = 'DreamFactory\Enterprise\Database\Models\Server';
    /** @type string */
    protected $_resource = 'server';
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

    /** @inheritdoc */
    public function index()
    {
        $asgn_servers = Server::join('cluster_server_asgn_t', 'server_id', '=', 'id')->get();

        $excludes = [];

        foreach ($asgn_servers as $obj) {
            array_push($excludes, $obj->id);
        }

        $not_asgn_servers = Server::whereNotIn('id', $excludes)->get();

        $result = array_merge(json_decode($asgn_servers), json_decode($not_asgn_servers));

        return View::make('app.servers')->with('prefix', $this->_prefix)->with('servers', $result);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    protected function _loadData()
    {
        $_columns = [
            'server_t.id',
            'server_t.server_id_text',
            'server_type_t.type_name_text',
            'server_t.host_text',
            'server_t.lmod_date',
        ];

        /** @type Builder $_query */
        $_query = Server::join('server_type_t', 'server_t.server_type_id', '=', 'server_type_t.id')->select($_columns);

        return $this->_processDataRequest('instance_t.instance_id_text', Server::count(), $_columns, $_query);
    }

    public function create(array $viewData = [])
    {
        return \View::make(
            'app.servers.create',
            [
                'prefix'       => $this->_prefix,
                'server_types' => ServerType::all(),
            ]
        );
    }

    public function edit($id)
    {
        $cluster_names = 'The server is not assigned to a cluster';
        $cluster_servers = $this->_serverClusters($id);

        if (count($cluster_servers) > 0)
        {
            $cluster = $this->_findCluster($cluster_servers[0]->cluster_id);
            $cluster_names = $cluster->cluster_id_text;
        }

        $server_types = ServerType::all();
        $server_data = $this->_findServer($id);

        if (is_array($server_data->config_text)) {
            $config = $server_data->config_text;
        } else {
            $config = json_decode($server_data->config_text, true);
        }

        return View::make('app.servers.edit')->with('server_id', $id)->with('prefix', $this->_prefix)->with('server',
            $server_data)->with('server_types', $server_types)->with('clusters', $cluster_names)->with('config',
            $config);
    }

    public function update($id)
    {
        $input = Input::all();



        $validator = Validator::make($input,
            [
                'server_id_text'                                                    => 'required|string|min:1',
                'server_type_select'                                                => 'required|string|min:1',
                'host_text'                                                         => [
                    "required",
                    "Regex:/((https?|ftp)\:\/\/)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?(([a-z0-9-.]*)\.([a-z]{2,6}))|(([0-9]{1,3}\.){3}[0-9]{1,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?/i",
                ],
                'config.' . $input['server_type_select'] . '.port'                  => 'sometimes|required|numeric|min:1',
                'config.' . $input['server_type_select'] . '.scheme'                => 'sometimes|required|string|min:1',
                'config.' . $input['server_type_select'] . '.username'              => 'sometimes|required|string',
                'config.' . $input['server_type_select'] . '.driver'                => 'sometimes|required|string',
                'config.' . $input['server_type_select'] . '.default-database-name' => 'sometimes|required|string'
            ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach ($messages as $key => $value) {
                switch ($key) {

                    case 'server_id_text':
                        $flash_message = 'Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'server_type_select':
                        $flash_message = 'Type is not selected';
                        break;
                    case 'host_text':
                        $flash_message = 'Host format is invalid (use subdomain.domain.tld)';
                        break;
                    case 'config.' . $input['server_type_select'] . '.port':
                        $flash_message = 'Port must be an integer and larger than 0';
                        break;
                    case 'config.' . $input['server_type_select'] . '.scheme':
                        $flash_message = 'Protocol is not selected';
                        break;
                    case 'config.' . $input['server_type_select'] . '.username':
                        $flash_message =
                            'User Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'config.' . $input['server_type_select'] . '.driver':
                        $flash_message = 'Driver is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'config.' . $input['server_type_select'] . '.default-database-name':
                        $flash_message =
                            'Default is blank or Database Name contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/servers/' . $id . '/edit')->withInput();
        }

        try {
            $input = Input::all();

            $type = $input['server_type_select'];
            $input_config = $input['config'][$type];
            $input['config_text'] = $input_config;

            if ($type == 'db') {
                $input['server_type_id'] = 1;
            }
            if ($type == 'web') {
                $input['server_type_id'] = 2;
            }
            if ($type == 'app') {
                $input['server_type_id'] = 3;
            }

            unset($input['_method']);
            unset($input['_token']);
            unset($input['config']);
            unset($input['server_type_select']);

            $server = Server::find($id);
            $server->update($input);

            $result_text = 'The server "' . $input['server_id_text'] . '" was updated successfully!';
            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->_prefix;
            $_redirect .= '/servers';

            return Redirect::to($_redirect)
                ->with('flash_message', $result_text)
                ->with('flash_type', $result_status);
        } catch (QueryException $e) {
            Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/servers/' . $id . '/edit')->withInput();
        }
    }

    public function store()
    {
        $input = Input::all();
        $type = 0;

        $validator = Validator::make($input,
            [
                'server_id_text'                                                    => 'required|string|min:1',
                'server_type_select'                                                => 'required|string|min:1',
                'host_text'                                                         => [
                    "required",
                    "Regex:/((https?|ftp)\:\/\/)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?(([a-z0-9-.]*)\.([a-z]{2,6}))|(([0-9]{1,3}\.){3}[0-9]{1,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?/i",
                ],
                'config.' . $input['server_type_select'] . '.port'                  => 'sometimes|required|numeric|min:1',
                'config.' . $input['server_type_select'] . '.scheme'                => 'sometimes|required|string|min:1',
                'config.' . $input['server_type_select'] . '.username'              => 'sometimes|required|string',
                'config.' . $input['server_type_select'] . '.driver'                => 'sometimes|required|string',
                'config.' . $input['server_type_select'] . '.default-database-name' => 'sometimes|required|string'
            ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach ($messages as $key => $value) {
                switch ($key) {

                    case 'server_id_text':
                        $flash_message = 'Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'server_type_select':
                        $flash_message = 'Type is not selected';
                        break;
                    case 'host_text':
                        $flash_message = 'Host format is invalid (use subdomain.domain.tld)';
                        break;
                    case 'config.' . $input['server_type_select'] . '.port':
                        $flash_message = 'Port must be an integer and larger than 0';
                        break;
                    case 'config.' . $input['server_type_select'] . '.scheme':
                        $flash_message = 'Scheme is not selected';
                        break;
                    case 'config.' . $input['server_type_select'] . '.username':
                        $flash_message =
                            'User Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'config.' . $input['server_type_select'] . '.driver':
                        $flash_message = 'Driver is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'config.' . $input['server_type_select'] . '.default-database-name':
                        $flash_message =
                            'Default is blank or Database Name contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/servers/create')->withInput();
        }

        try {
            $input = Input::all();

            $type = $input['server_type_select'];
            $input_config = $input['config'][$type];
            $input['config_text'] = $input_config;

            if ($type == 'db') {
                $input['server_type_id'] = 1;
            }
            if ($type == 'web') {
                $input['server_type_id'] = 2;
            }
            if ($type == 'app') {
                $input['server_type_id'] = 3;
            }

            unset($input['_method']);
            unset($input['_token']);
            unset($input['config']);
            unset($input['server_type_select']);

            $create_server = new Server();
            $create_server->create($input);

            $result_text = 'The server "' . $input['server_id_text'] . '" was created successfully!';
            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->_prefix;
            $_redirect .= '/servers';

            return Redirect::to($_redirect)
                ->with('flash_message', $result_text)
                ->with('flash_type', $result_status);
        } catch (QueryException $e) {
            Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/servers/create')->withInput();
        }
    }

    public function destroy($ids)
    {
        try {
            $id_array = [];
            $server_names = [];

            if ($ids == 'multi') {
                $params = Input::all();
                $selected = $params['_selected'];
                $id_array = explode(',', $selected);
            } else {
                $id_array = explode(',', $ids);
            }

            foreach ($id_array as $id) {
                $server = Server::where('id', '=', $id);
                $server_name = $server->get(['server_id_text']);
                array_push($server_names, '"' . $server_name[0]->server_id_text . '"');
                $server->delete();
                ClusterServer::where('server_id', '=', intval($id))->delete();
            }

            if (count($id_array) > 1) {
                $servers = '';
                foreach ($server_names as $i => $name) {
                    $servers .= $name;

                    if (count($server_names) > $i + 1) {
                        $servers .= ', ';
                    }
                }

                $result_text = 'The servers ' . $servers . ' were deleted successfully!';
            } else {
                $result_text = 'The server ' . $server_names[0] . ' was deleted successfully!';
            }

            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->_prefix;
            $_redirect .= '/servers';

            return Redirect::to($_redirect)
                ->with('flash_message', $result_text)
                ->with('flash_type', $result_status);
        } catch (QueryException $e) {
            Session::flash('flash_message', 'An error occurred! Please try again.');
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/servers')->withInput();
        }
    }
}
