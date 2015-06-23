<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use Session;
use Validator;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\ClusterServer;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Database\Models\ServerType;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;


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
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Server';
    /** @type string */
    protected $_resource = 'server';

    protected $_prefix = 'v1';

    //******************************************************************************
    //* Traits
    //******************************************************************************
    use EntityLookup;

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
        $serv_t = new ServerType();
        $server_types = $serv_t->all();

        return View::make('app.servers.create')->with('prefix', $this->_prefix)->with('server_types', $server_types);
    }

    public function edit($id)
    {
        $cluster_servers = $this->_serverClusters($id);

        $cluster_names = '';

        foreach ($cluster_servers as $value) {
            $cluster = $this->_findCluster($value->cluster_id);
            $cluster_names .= ' ' . $cluster->cluster_id_text . ',';
        }

        $cluster_names = rtrim($cluster_names, ',');
        $cluster_names = ($cluster_names ? $cluster_names : '(none)');

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


        $validator = Validator::make($input, [
            'server_id_text' => array('Regex:/^[a-z0-9 .\-]+$/i'),
            'host_text' => array("Regex:/((https?|ftp)\:\/\/)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?(([a-z0-9-.]*)\.([a-z]{2,6}))|(([0-9]{1,3}\.){3}[0-9]{1,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?/i"),
            'config.'.$type.'.port' => array('Regex:/^[0-9]+$/'),
            'config.'.$type.'.username' => array('Regex:/^[a-z0-9 .\-]+$/i'),
            'config.'.$type.'.driver' => array('Regex:/^[a-z0-9 .\-]+$/i'),
            'config.'.$type.'.default-database-name' => array('Regex:/^[a-z0-9 .\-]+$/i'),
            //'config.'.$type.'.access_token' => array('Regex:/^[0-9]+$/')
        ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach($messages as $key => $value){
                switch ($key) {

                    case 'server_id_text':
                        $flash_message = 'Name contain invalid characters (use a-z, A-Z, . and -)';
                        break;
                    case 'host_text':
                        $flash_message = 'Host format is invalid (use http/https://subdomain.domain.tld)';
                        break;
                    case 'config.'.$type.'.port':
                        $flash_message = 'Port must be an integer and larger than 0';
                        break;
                    case 'config.'.$type.'.username':
                        $flash_message = 'User Name contain invalid characters (use a-z, A-Z, . and -)';
                        break;
                    case 'config.'.$type.'.driver':
                        $flash_message = 'Driver contain invalid characters (use a-z, A-Z, . and -)';
                        break;
                    case 'config.'.$type.'.default-database-name':
                        $flash_message = 'Default Database Name contain invalid characters (use a-z, A-Z, . and -)';
                        break;
                    /*
                    case 'config.'.$type.'.access_token':
                        $flash_message = 'Port must be an integer and larger than 0';
                        break;
                    */
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/servers/'.$id.'/edit')->withInput();
        }

        unset($input['_method']);
        unset($input['_token']);
        unset($input['config']);
        unset($input['server_type_select']);

        try{

            $server = Server::find($id);
            $server->update($input);

            $result_text = 'The server "'.$input['server_id_text'].'" was successfully updated!';
            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->_prefix;
            $_redirect .= '/servers';

            return Redirect::to($_redirect)
                ->with('flash_message', $result_text)
                ->with('flash_type', $result_status);
        }
        catch (\Illuminate\Database\QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/servers/'.$id.'/edit')->withInput();
        }
    }

    public function store()
    {
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

        $validator = Validator::make($input, [
            'server_id_text' => array('Regex:/^[a-z0-9 .\-]+$/i'),
            'host_text' => array("Regex:/((https?|ftp)\:\/\/)?([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?(([a-z0-9-.]*)\.([a-z]{2,6}))|(([0-9]{1,3}\.){3}[0-9]{1,3})(\:[0-9]{2,5})?(\/([a-z0-9+\$_-]\.?)+)*\/?(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?(#[a-z_.-][a-z0-9+\$_.-]*)?/i"),
            'config.'.$type.'.port' => array('Regex:/^[0-9]+$/'),
            'config.'.$type.'.username' => array('Regex:/^[a-z0-9 .\-]+$/i'),
            'config.'.$type.'.driver' => array('Regex:/^[a-z0-9 .\-]+$/i'),
            'config.'.$type.'.default-database-name' => array('Regex:/^[a-z0-9 .\-]+$/i'),
            //'config.'.$type.'.access_token' => array('Regex:/^[0-9]+$/')
        ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach($messages as $key => $value){
                switch ($key) {

                    case 'server_id_text':
                        $flash_message = 'Name contain invalid characters (use a-z, A-Z, . and -)';
                        break;
                    case 'host_text':
                        $flash_message = 'Host format is invalid (use subdomain.domain.tld)';
                        break;
                    case 'config.'.$type.'.port':
                        $flash_message = 'Port must be an integer and larger than 0';
                        break;
                    case 'config.'.$type.'.username':
                        $flash_message = 'User Name contain invalid characters (use a-z, A-Z, . and -)';
                        break;
                    case 'config.'.$type.'.driver':
                        $flash_message = 'Driver contain invalid characters (use a-z, A-Z, . and -)';
                        break;
                    case 'config.'.$type.'.default-database-name':
                        $flash_message = 'Default Database Name contain invalid characters (use a-z, A-Z, . and -)';
                        break;
                    /*
                    case 'config.'.$type.'.access_token':
                        $flash_message = 'Port must be an integer and larger than 0';
                        break;
                    */
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/servers/create')->withInput();
        }

        unset($input['_method']);
        unset($input['_token']);
        unset($input['config']);
        unset($input['server_type_select']);

        try{

            $create_server = new Server();
            $create_server->create($input);

            $result_text = 'The server "'.$input['server_id_text'].'" was created successfully!';
            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->_prefix;
            $_redirect .= '/servers';

            return Redirect::to($_redirect)
            ->with('flash_message', $result_text)
            ->with('flash_type', $result_status);
        }
        catch (\Illuminate\Database\QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/servers/create')->withInput();
        }
    }

    public function destroy($ids)
    {
        try {
            $id_array = [];

            if ($ids == 'multi') {
                $params = Input::all();
                $selected = $params['_selected'];
                $id_array = explode(',', $selected);
            } else {
                $id_array = explode(',', $ids);
            }

            foreach ($id_array as $id) {
                Server::find($id)->delete();
                ClusterServer::where('server_id', '=', intval($id))->delete();
            }

            if(count($id_array) > 1) {
                $result_text = 'The servers were deleted successfully!';
            }
            else
            {
                $result_text = 'The server was deleted successfully!';
            }

            $result_status = 'alert-success';

            $_redirect = '/';
            $_redirect .= $this->_prefix;
            $_redirect .= '/servers';

            return Redirect::to($_redirect)
                ->with('flash_message', $result_text)
                ->with('flash_type', $result_status);
        }
        catch (\Illuminate\Database\QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message', 'An error occurred! Please try again.');
            Session::flash('flash_type', 'alert-danger');
            return redirect('/v1/servers')->withInput();
        }
    }

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



    protected function _validate(){


    }

}
