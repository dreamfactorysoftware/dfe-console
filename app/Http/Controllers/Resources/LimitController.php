<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Library\Utility\Enums\DateTimeIntervals;
use DreamFactory\Library\Utility\Curl;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Input;
use Illuminate\Routing\Route;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\HttpFoundation\Request;
use Session;
use Validator;


class LimitController extends ResourceController
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'limit_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Limit';
    /** @type string */
    protected $_resource = 'policy';
    /**
     * @type string
     */
    protected $_prefix = 'v1';
    /**
     * @type array The limit periods
     */
    protected $periods = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();

        $this->periods = [
            'Minute' => 1,
            'Hour'   => 60,
            'Day'    => 60 * 24,
            '7 Days'   => 60 * 24 * 7,
            '30 Days'  => 60 * 24 * 30,
        ];
    }

    /**
     * @param array $viewData
     *
     * @return \Illuminate\View\View
     */
    public function create(array $viewData = [])
    {
        return \View::make('app.limits.create',
            [
                'limitPeriods' => $this->periods,
                'prefix'       => $this->_prefix,
                'clusters'     => Cluster::all()
            ]);
    }

    /**
     * @param integer $id
     *
     * @return \Illuminate\View\View
     */
    public function update($id)
    {

        $validator = Validator::make(Input::all(),
            [
                'type_select'      => 'required|string',
                'label_text'       => 'required|string',
                'cluster_id'       => 'required|string',
                'instance_id'      => 'sometimes|min:1',
                'user_id'          => 'sometimes|min:1',
                'period_name'      => 'required|string|min:1',
                'limit_nbr'        => 'required|numeric|min:1'

            ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach ($messages as $key => $value) {
                switch ($key) {

                    case 'type_select':
                        $flash_message = 'Select Type';
                        break;
                    case 'label_text':
                        $flash_message = 'Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'cluster_id':
                        $flash_message = 'Select Cluster';
                        break;
                    case 'instance_id':
                        $flash_message = 'Select Instance';
                        break;
                    case 'user_id':
                        $flash_message = 'Select User';
                        break;
                    case 'period_name':
                        $flash_message =
                            'Select Period';
                        break;
                    case 'limit_nbr':
                        $flash_message =
                            'Limit must be a number and larger than 0';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/limits/' . $id . '/edit')->withInput();
        }

        $_input = [];

        try {
            // Build the limit record

            foreach([
                        'cluster_id' => '',
                        'instance_id' => '',
                        'service_name' => 0,
                        'user_id' => '',
                        'period_name' => "Minute",
                        'limit_nbr' => 0,
                        'active_ind' => 0,
                        'label_text' => 0,
                        'type_select' => 0
                    ] as $_input_key => $_input_default) {
                $_input[$_input_key] = \Input::get($_input_key, $_input_default);
            }
echo json_encode($_input);
            $_time_period = str_replace(' ', '-', strtolower($_input['period_name']));

            if ( $_input['cluster_id'] === '' && $_input['instance_id'] === '') {
                $_limit_key_text = 'default.' . $_time_period;
            } elseif ($_input['cluster_id'] !== '' && $_input['instance_id'] === '') {
                $_limit_key_text = 'cluster.default.' . $_time_period;
            } else {
                if ($_input['service_name'] == 'all' && $_input['user_id'] == '') {
                    $_limit_key_text = 'instance.default.' . $_time_period;
                } elseif ($_input['service_name'] != 'all') {
                    $_limit_key_text = 'service:' . $_input['service_name'] . '.' . $_time_period;
                } elseif ($_input['user_id'] != '') {
                    $_limit_key_text = 'user:' . $_input['user_id'] . '.' . $_time_period;
                }
            }


            if ($_input['type_select'] == 'cluster') {
                $_input['instance_id'] = null;
                $_input['user_id'] = null;
            }

            if ($_input['type_select'] == 'instance') {
                $_input['user_id'] = null;
            }

            $limit = [
                'cluster_id' => $_input['cluster_id'] == 0 ? null : $_input['cluster_id'],
                'instance_id' => $_input['instance_id'] == 0 ? null : $_input['instance_id'],
                'limit_key_text' => $_limit_key_text,
                'period_nbr' => $this->periods[$_input['period_name']],
                'limit_nbr' => $_input['limit_nbr'],
                'active_ind' => ($_input['active_ind']) ? 1 : 0,
                'label_text' => $_input['label_text']
            ];

            /*
            if (!Limit::find($id)->update($limit)) {
                throw new EnterpriseDatabaseException('Unable to update limit "' . $id . '"');
            }
*/
            \Session::flash('flash_message', 'The limit "' . $_input['label_text'] . '" was updated successfully!');
            \Session::flash('flash_type', 'alert-success');

  //          return \Redirect::to($this->makeRedirectUrl('limits'));

        } catch (QueryException $e) {

            Session::flash('flash_message', '1Unable to update limit! '.$e->getMessage());
            Session::flash('flash_type', 'alert-danger');
            logger('Error editing limit: ' . $e->getMessage());
            return redirect('/' . $this->getUiPrefix() . '/limits/' . $id . '/edit')->withInput();
        }
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {

        $_results = Limit::all();

        $_limits = [];

        foreach($_results as $_limit) {
            $_values = [
                'limit_nbr' => $_limit->limit_nbr,
                'user_id' => 0,
                'service_name' => '',
                'role_id' => 0,
                'api_key' => '',
                'period_name' => '',
                'label_text' => $_limit->label_text,
                'cluster_id_text' => '',
                'instance_id_text' => ''
            ];

            if (!empty($_limit['cluster_id'])) {
                try {
                    $_cluster = $this->_findCluster($_limit['cluster_id']);
                    $_values['cluster_id_text'] = $_cluster->cluster_id_text;
                } catch (ModelNotFoundException $e) {
                    // Invalid cluster id, skip
                    continue;
                }
            }

            $defaultPos = strpos($_limit['limit_key_text'], 'default.');
            $clusterDefaultPos = strpos($_limit['limit_key_text'], 'cluster.default.');
            $instanceDefaultPos = strpos($_limit['limit_key_text'], 'instance.default.');

            if ($defaultPos !== false && $defaultPos == 0) {
                $_values['notes'] = 'Default for all clusters and instances';
            } elseif ($clusterDefaultPos !== false && $clusterDefaultPos == 0) {
                $_values['notes'] = 'Default for cluster';
            } elseif ($instanceDefaultPos !== false && $instanceDefaultPos == 0) {
                $_values['notes'] = 'Default for instance';
            } else {
                $_values['notes'] = '';
            }

            $_this_limit_type = null;

            foreach (explode('.', $_limit['limit_key_text']) as $_value) {
                $_limit_key = explode(':', $_value);

                switch ($_limit_key[0]) {
                    case 'default':
                        break;
                    case 'cluster':
                        $_this_limit_type = 'cluster';
                        break;
                    case 'instance':
                        $_this_limit_type = 'instance';
                        break;
                    case 'user':
                        $_values['user_id'] = $_limit_key[1];
                        $_this_limit_type = 'user';
                        break;
                    case 'service':
                        $_values['service_name'] = $_limit_key[1];
                        break;
                    case 'role':
                        $_values['role_id'] = $_limit_key[1];
                        break;
                    case 'api_key':
                        $_values['api_key'] = $_limit_key[1];
                        break;
                    default:
                        // It's time period
                        $_values['period_name'] = ucwords(str_replace('-', ' ', $_limit_key[0]));
                }
            }

            //temp
            $_services = [];
            $_users = [];

            if (!empty($_limit['instance_id'])) {

                try {
                    $_instance = $this->_findInstance($_limit['instance_id']);
                    //$_tmp = $this->getInstanceServices($_limit['instance_id']);
                    $_services = [];
                    /*
                    foreach ($_tmp as $_v) {
                        $_services[$_v['id']] = $_v['name'];
                    }
                    */

                    $_users = [];

                    if ($_this_limit_type == 'user') {
                        $_tmp = $this->getInstanceUsers($_limit['instance_id']);

                        foreach($_tmp as $_v) {
                            $_users[$_v['id']] = $_v['name'];
                        }
                    }

                    $_values['instance_id_text'] = $_instance->instance_id_text;

                } catch (ModelNotFoundException $e) {
                    // Invalid instance, skip it
                    continue;
                }

            }

            $_limits[] = [
                'id' => $_limit['id'],
                'cluster_id_text' => $_values['cluster_id_text'],
                'instance_id_text' => $_values['instance_id_text'],
                //'service_desc' => empty($_values['service_name']) === true ?'':$_services[$_values['service_name']],
                'user_name' => $_values['user_id'] == 0 ?'':$_users[$_values['user_id']],
                'period_name' => $_values['period_name'],
                'limit_nbr' => $_limit->limit_nbr,
                'label_text' => $_limit->label_text,
                'active_ind' => $_limit->active_ind,
                'notes' => $_values['notes']
            ];
        }

        return \View::make('app.limits',
            [
                'prefix' => $this->_prefix,
                'limits' => $_limits,
            ]);
    }


    /**
     * @param integer $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $_limit = Limit::find($id);

        $_values = [
            'limit_nbr' => $_limit->limit_nbr,
            'user_id' => '',
            'service_name' => '',
            'role_id' => 0,
            'api_key' => '',
            'period_name' => '',
            'label_text' => $_limit->label_text,
            'cluster_id_text' => '',
            'instance_id_text' => ''
        ];

        $defaultPos = strpos($_limit['limit_key_text'], 'default.');
        $clusterDefaultPos = strpos($_limit['limit_key_text'], 'cluster.default.');
        $instanceDefaultPos = strpos($_limit['limit_key_text'], 'instance.default.');

        if ($defaultPos !== false && $defaultPos == 0) {
            $_values['notes'] = 'Default for all clusters and instances';
        } elseif ($clusterDefaultPos !== false && $clusterDefaultPos == 0) {
            $_values['notes'] = 'Default for cluster';
        } elseif ($instanceDefaultPos !== false && $instanceDefaultPos == 0) {
            $_values['notes'] = 'Default for instance';
        } else {
            $_values['notes'] = '';
        }

        $_this_limit_type = null;

        foreach (explode('.', $_limit['limit_key_text']) as $_value) {
            $_limit_key = explode(':', $_value);

            switch ($_limit_key[0]) {
                case 'default':
                    break;
                case 'cluster':
                    $_this_limit_type = 'cluster';
                    break;
                case 'instance':
                    $_this_limit_type = 'instance';
                    break;
                case 'user':
                    $_values['user_id'] = $_limit_key[1];
                    $_this_limit_type = 'user';
                    break;
                case 'service':
                    $_values['service_name'] = $_limit_key[1];
                    break;
                case 'role':
                    $_values['role_id'] = $_limit_key[1];
                    break;
                case 'api_key':
                    $_values['api_key'] = $_limit_key[1];
                    break;
                default:
                    // It's time period
                    $_values['period_name'] = ucwords(str_replace('-', ' ', $_limit_key[0]));
            }
        }



        if ($_limit['cluster_id'] !== '') {
            $_cluster = $this->_findCluster($_limit['cluster_id']);
            $_values['cluster_id_text'] = $_cluster->cluster_id_text;
        }

        $_services = [];
        $_users = [];

        if ($_limit['instance_id'] !== null) {
            $_instance = $this->_findInstance($_limit['instance_id']);
            /*
            $_tmp = $this->getInstanceServices($_limit['instance_id']);
            $_services = [];

            foreach ($_tmp as $_v) {
                $_services[$_v['id']] = $_v['name'];
            }
            */

            if ($_values['user_id'] !== '') {
                $_tmp = $this->getInstanceUsers($_limit['instance_id']);
                $_users = [];

                foreach ($_tmp as $_v) {
                    $_users[$_v['id']] = $_v['name'];
                }
            }

            $_values['instance_id_text'] = $_instance->instance_id_text;

        }

        if ($_limit['user_id'] !== null) {
            $_user = $this->_findUser($_limit['user_id']);
            $_values['user_id_text'] = $_user->user_id_text;
        }

        $_limits = [
            'id' => $_limit['id'],
            'type' => $_this_limit_type,
            'cluster_id' => $_limit['cluster_id'],
            'cluster_id_text' => $_values['cluster_id_text'],
            'instance_id' => $_limit['instance_id'],
            'instance_id_text' => $_values['instance_id_text'],
            'user_id' => $_values['user_id'],
            //'user_id_text' => $_values['user_id_text'],
            //'service_desc' => empty($_values['service_name']) === true ?'':$_services[$_values['service_name']],
            'user_name' => $_values['user_id'] == 0 ?'':$_users[$_values['user_id']],
            'period_name' => $_values['period_name'],
            'limit_nbr' => $_limit->limit_nbr,
            'label_text' => $_limit->label_text,
            'active_ind' => $_limit->active_ind,
            'notes' => $_values['notes']
        ];

        return \View::make('app.limits.edit',
            [
                'limitPeriods' => $this->periods,
                'prefix'       => $this->_prefix,
                'clusters'     => Cluster::all(),
                'limit'        => $_limits
            ]);
    }


    /**
     * @todo add manual constraint checks, as 0 is a valid option for cluster_id and instance_id in this use
     *
     * @return $this
     */
    public function store()
    {

        $validator = Validator::make(Input::all(),
            [
                'label_text'       => 'required|string',
                'type_select'      => 'required|string',
                'cluster_id'       => 'required|string',
                'instance_id'      => 'sometimes|string',
                'user_id'          => 'sometimes|string|min:1',
                'period_name'      => 'required|string|min:1',
                'limit_nbr'        => 'required|numeric|min:1'

            ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach ($messages as $key => $value) {
                switch ($key) {

                    case 'type_select':
                        $flash_message = 'Select Type';
                        break;
                    case 'label_text':
                        $flash_message = 'Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'cluster_id':
                        $flash_message = 'Select Cluster';
                        break;
                    case 'instance_id':
                        $flash_message = 'Select Instance';
                        break;
                    case 'user_id':
                        $flash_message = 'Select User';
                        break;
                    case 'period_name':
                        $flash_message =
                            'Select Period';
                        break;
                    case 'limit_nbr':
                        $flash_message =
                            'Limit must be a number and larger than 0';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');


            return redirect('/v1/limits/create')->withInput();
        }

        $_input = [];

        try {
            // Build the limit record

            foreach([
                        'cluster_id' => null,
                        'instance_id' => null,
                        'service_name' => 0,
                        'user_id' => 0,
                        'period_name' => "Minute",
                        'limit_nbr' => 0,
                        'active_ind' => 0,
                        'label_text' => 0
                    ] as $_input_key => $_input_default) {
                $_input[$_input_key] = \Input::get($_input_key, $_input_default);
            }

            $_time_period = str_replace(' ', '-', strtolower($_input['period_name']));

            if ( $_input['cluster_id'] === '' && $_input['instance_id'] === '') {
                $_limit_key_text = 'default.' . $_time_period;
            } elseif ($_input['cluster_id'] !== '' && $_input['instance_id'] === '') {
                $_limit_key_text = 'cluster.default.' . $_time_period;
            } else {
                if ($_input['service_name'] == 'all' && $_input['user_id'] == '') {
                    $_limit_key_text = 'instance.default.' . $_time_period;
                } elseif ($_input['service_name'] != 'all') {
                    $_limit_key_text = 'service:' . $_input['service_name'] . '.' . $_time_period;
                } elseif ($_input['user_id'] != '') {
                    $_limit_key_text = 'user:' . $_input['user_id'] . '.' . $_time_period;
                }
            }

            $limit = [
                'cluster_id' => $_input['cluster_id'] == 0? null : $_input['cluster_id'],
                'instance_id' => $_input['instance_id'] == 0? null : $_input['instance_id'],
                'limit_key_text' => $_limit_key_text,
                'period_nbr' => $this->periods[$_input['period_name']],
                'limit_nbr' => $_input['limit_nbr'],
                'active_ind' => ($_input['active_ind']) ? 1 : 0,
                'label_text' => $_input['label_text']
            ];

            Limit::create($limit);

            return \Redirect::to('/' . $this->getUiPrefix() . '/limits')->with('flash_message', 'The limit "'.$_input['label_text'].'" was created successfully!')->with('flash_type', 'alert-success');

        } catch (QueryException $e) {

            Session::flash('flash_message', 'Unable to add limit!');
            Session::flash('flash_type', 'alert-danger');
            logger('Error adding limit: ' . $e->getMessage());
            return redirect('/' . $this->getUiPrefix() . '/limits/create')->withInput();
        }
    }


    /**
     * @param string|int $limitId
     *
     * @return array|bool|\stdClass
     */
    public function destroy($ids)
    {
        try {
            $limit_names = [];

            if ($ids == 'multi') {
                $params = \Input::all();
                $selected = $params['_selected'];
                $id_array = explode(',', $selected);
            } else {
                $id_array = explode(',', $ids);
            }

            foreach ($id_array as $id) {
                $limit = Limit::where('id', '=', $id);
                $limit_name = $limit->get(['label_text']);
                array_push($limit_names, '"' . $limit_name[0]->label_text . '"');
                $limit->delete();
            }

            if (count($id_array) > 1) {
                $limits = '';
                foreach ($limit_names as $i => $name) {
                    $limits .= $name;

                    if (count($limit_names) > $i + 1) {
                        $limits .= ', ';
                    }
                }

                $result_text = 'The limits ' . $limits . ' were deleted successfully!';
            } else {
                $result_text = 'The limit ' . $limit_names[0] . ' was deleted successfully!';
            }

            Session::flash('flash_message', $result_text);
            Session::flash('flash_type', 'alert-success');

            $_redirect = '/';
            $_redirect .= $this->getUiPrefix();
            $_redirect .= '/limits';

            return \Redirect::to($_redirect);
        } catch (QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message', 'An error occurred! Please try again.');
            Session::flash('flash_type', 'alert-danger');
            logger('Error deleting limit: ' . $e->getMessage());
            return redirect('/v1/limits')->withInput();
        }

    }


    /**
     * @param string|int $instanceId
     *
     * @return array|bool|\stdClass
     */
    public function getInstanceServices($instanceId)
    {
        if (!empty($instanceId)) {
            return $this->callInstanceApi($this->_findInstance($instanceId), '/api/v2/system/service');
        }

        return false;
    }

    /**
     * @param string|int $instanceId
     *
     * @return array|bool|\stdClass
     */
    public function getInstanceUsers($instanceId)
    {
        if (!empty($instanceId)) {
            return $this->callInstanceApi($this->_findInstance($instanceId),'/api/v2/system/user');
        }

        return false;
    }

    public function generateConsoleApiKey($metadata)
    {
        return hash('sha256', $metadata['cluster-id'] . $metadata['instance-id']);
    }

    public function callInstanceApi(Instance $instance, $uri)
    {
        $_url = config('DFE_DEFAULT_DOMAIN_PROTOCOL','https') . '://' .
            $instance->instance_data_text['env']['instance-id'] . '.' .
            $instance->instance_data_text['env']['default-domain'] .
            $uri;

        $_rows = Curl::get($_url, [], [CURLOPT_HTTPHEADER => [EnterpriseDefaults::CONSOLE_X_HEADER . ': ' . $this->generateConsoleApiKey($instance->instance_data_text['env'])]])->resource;

        $_results=[];

        foreach ($_rows as $_row) {
            if ($_row->is_active === true) {
                if (empty($_row->label) === true) {
                    $_results[] = ['id' => $_row->id, 'name' => $_row->name];
                } else {
                    $_results[] = ['id' => $_row->name, 'name' => $_row->label];
                }

            }
        }

        return $_results;
    }
}