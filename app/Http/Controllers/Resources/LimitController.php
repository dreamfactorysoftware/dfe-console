<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;
use DreamFactory\Enterprise\Database\Exceptions\DatabaseException;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Library\Utility\Enums\DateTimeIntervals;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Session;
use Validator;

class LimitController extends ViewController
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
            'Minute'  => DateTimeIntervals::MINUTES_PER_MINUTE,
            'Hour'    => DateTimeIntervals::MINUTES_PER_HOUR,
            'Day'     => DateTimeIntervals::MINUTES_PER_DAY,
            '7 Days'  => DateTimeIntervals::MINUTES_PER_WEEK,
            '30 Days' => DateTimeIntervals::MINUTES_PER_MONTH,
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
                'clusters'     => Cluster::all(),
            ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param integer                  $id
     *
     * @return \Illuminate\View\View
     * @throws \DreamFactory\Enterprise\Database\Exceptions\DatabaseException
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make(\Input::all(),
            [
                'type_select' => 'required|string',
                'label_text'  => 'required|string',
                'cluster_id'  => 'required|string',
                'instance_id' => 'sometimes|min:1',
                'user_id'     => 'sometimes|min:1',
                'period_name' => 'required|string|min:1',
                'limit_nbr'   => 'required|numeric|min:1',

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
                        $flash_message = 'Select Period';
                        break;
                    case 'limit_nbr':
                        $flash_message = 'Limit must be a number and larger than 0';
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

            foreach ([
                'cluster_id'   => null,
                'instance_id'  => null,
                'service_name' => 0,
                'user_id'      => 0,
                'period_name'  => "Minute",
                'limit_nbr'    => 0,
                'active_ind'   => 0,
                'label_text'   => 0,
                'type_select'  => 0,
            ] as $_input_key => $_input_default) {
                $_input[$_input_key] = \Input::get($_input_key, $_input_default);
            }

            $_time_period = str_replace(' ', '-', strtolower($_input['period_name']));

            $_limit_key_text = 'default.' . $_time_period;

            if ($_input['type_select'] == 'cluster') {
                $_input['instance_id'] = '';
                $_input['user_id'] = '';
            }

            if ($_input['type_select'] == 'instance') {
                $_input['user_id'] = '';
            }

            if ($_input['cluster_id'] === '' && $_input['instance_id'] === '') {
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
                'cluster_id'     => $_input['cluster_id'] == 0 ? null : $_input['cluster_id'],
                'instance_id'    => $_input['instance_id'] == 0 ? null : $_input['instance_id'],
                'limit_key_text' => $_limit_key_text,
                'period_nbr'     => $this->periods[$_input['period_name']],
                'limit_nbr'      => $_input['limit_nbr'],
                'active_ind'     => ($_input['active_ind']) ? 1 : 0,
                'label_text'     => $_input['label_text'],
            ];

            $res =
                Limit::where('cluster_id', $limit['cluster_id'])
                    ->where('instance_id', $limit['instance_id'])
                    ->where('limit_key_text', $limit['limit_key_text'])
                    ->where('period_nbr', $limit['period_nbr'])
                    ->where('id', '!=', $id)
                    ->first();

            if (is_object($res)) {
                \Session::flash('flash_message',
                    'Unable to update limit! A limit with the selected combination of Cluster/Instance/User and Period already exists.');
                Session::flash('flash_type', 'alert-danger');

                return redirect('/' . $this->getUiPrefix() . '/limits/' . $id . '/edit')->withInput();
            }

            if (!Limit::find($id)->update($limit)) {
                throw new DatabaseException('Unable to update limit "' . $id . '"');
            }

            \Session::flash('flash_message', 'The limit "' . $_input['label_text'] . '" was updated successfully!');
            \Session::flash('flash_type', 'alert-success');

            return \Redirect::to($this->makeRedirectUrl('limits'));
        } catch (QueryException $e) {
            $err_msg = $e->getMessage();

            if (strpos($err_msg, 'SQLSTATE') !== false) {
                Session::flash('flash_message',
                    'Unable to update limit! A limit with the selected combination of Cluster/Instance/User and Period already exists.');
            } else {
                Session::flash('flash_message', 'Unable to update limit!');
            }

            Session::flash('flash_type', 'alert-danger');
            logger('Error editing limit: ' . $err_msg);

            return redirect('/' . $this->getUiPrefix() . '/limits/' . $id . '/edit')->withInput();
        }
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $_valueTemplate = [
            'limit_nbr'        => null,
            'user_id'          => null,
            'service_name'     => null,
            'role_id'          => null,
            'api_key'          => null,
            'period_name'      => null,
            'label_text'       => null,
            'cluster_id_text'  => null,
            'instance_id_text' => null,
        ];

        $_limits = [];

        /** @type Limit $_limit */
        foreach (Limit::all() as $_limit) {
            $_cluster = null;

            $_values = array_merge($_valueTemplate, ['limit_nbr' => $_limit->id, 'label_text' => $_limit->label_text]);

            if (!empty($_limit->cluster_id)) {
                try {
                    $_cluster = $this->_findCluster($_limit['cluster_id']);
                    $_values['cluster_id_text'] = $_cluster->cluster_id_text;
                } catch (ModelNotFoundException $e) {
                    // Invalid cluster id, skip
                    $this->error('Invalid cluster_id in limit id#' . $_limit->id);
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

            $_userName = null;

            if (!empty($_limit['instance_id'])) {

                try {
                    $_instance = $this->_findInstance($_limit['instance_id']);
                    $_values['instance_id_text'] = $_instance->instance_id_text;

                    if ('user' == $_this_limit_type) {
                        if (false !== ($_rows = $this->getInstanceUsers($_instance))) {
                            foreach ($_rows as $_user) {
                                if ($_user['id'] != $_values['user_id']) {
                                    continue;
                                }

                                $_userName = $_user['name'];
                                break;
                            }
                        }
                    }
                } catch (ModelNotFoundException $e) {
                    // Invalid instance, skip it
                    continue;
                }
            }

            $_limits[] = [
                'id'               => $_limit['id'],
                'cluster_id_text'  => $_values['cluster_id_text'],
                'instance_id_text' => $_values['instance_id_text'],
                //'service_desc' => empty($_values['service_name']) === true ?'':$_services[$_values['service_name']],
                'user_name'        => $_userName,
                'period_name'      => $_values['period_name'],
                'limit_nbr'        => $_limit->limit_nbr,
                'label_text'       => $_limit->label_text,
                'active_ind'       => $_limit->active_ind,
                'notes'            => $_values['notes'],
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
        /** @type Limit $_limit */
        $_limit = Limit::find($id);

        $_values = [
            'limit_nbr'        => $_limit->limit_nbr,
            'user_id'          => null,
            'service_name'     => '',
            'role_id'          => 0,
            'api_key'          => '',
            'period_name'      => '',
            'label_text'       => $_limit->label_text,
            'cluster_id_text'  => '',
            'instance_id_text' => '',
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

        $_userName = null;
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

        if (!empty($_limit->cluster_id)) {
            $_cluster = $this->_findCluster($_limit['cluster_id']);
            $_values['cluster_id_text'] = $_cluster->cluster_id_text;
        }

        if (!empty($_limit->instance_id)) {
            $_instance = $this->_findInstance($_limit->instance_id);
            $_values['instance_id_text'] = $_instance->instance_id_text;

            if (!empty($_values['user_id'])) {
                if (false !== ($_rows = $this->getInstanceUsers($_instance))) {
                    foreach ($_rows as $_user) {
                        if ($_user['id'] != $_values['user_id']) {
                            continue;
                        }

                        $_userName = $_user['name'];
                        break;
                    }
                }
            }
        }

        $_limits = [
            'id'               => $_limit['id'],
            'type'             => $_this_limit_type,
            'cluster_id'       => $_limit['cluster_id'],
            'cluster_id_text'  => $_values['cluster_id_text'],
            'instance_id'      => $_limit['instance_id'],
            'instance_id_text' => $_values['instance_id_text'],
            'user_id'          => $_values['user_id'],
            'user_name'        => $_userName,
            'period_name'      => $_values['period_name'],
            'limit_nbr'        => $_limit->limit_nbr,
            'label_text'       => $_limit->label_text,
            'active_ind'       => $_limit->active_ind,
            'notes'            => $_values['notes'],
        ];

        logger('limit: ' . print_r($_limits, true));

        return \View::make('app.limits.edit',
            [
                'limitPeriods' => $this->periods,
                'prefix'       => $this->_prefix,
                'clusters'     => Cluster::all(),
                'limit'        => $_limits,
            ]);
    }

    /**
     * @todo add manual constraint checks, as 0 is a valid option for cluster_id and instance_id in this use
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return $this
     */
    public function store(Request $request)
    {

        $validator = Validator::make(\Input::all(),
            [
                'label_text'  => 'required|string',
                'type_select' => 'required|string',
                'cluster_id'  => 'required|string',
                'instance_id' => 'sometimes|string',
                'user_id'     => 'sometimes|string|min:1',
                'period_name' => 'required|string|min:1',
                'limit_nbr'   => 'required|numeric|min:1',

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
                        $flash_message = 'Select Period';
                        break;
                    case 'limit_nbr':
                        $flash_message = 'Limit must be a number and larger than 0';
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

            foreach ([
                'cluster_id'   => null,
                'instance_id'  => null,
                'service_name' => 0,
                'user_id'      => 0,
                'period_name'  => "Minute",
                'limit_nbr'    => 0,
                'active_ind'   => 0,
                'label_text'   => 0,
            ] as $_input_key => $_input_default) {
                $_input[$_input_key] = \Input::get($_input_key, $_input_default);
            }

            $_time_period = str_replace(' ', '-', strtolower($_input['period_name']));

            //  Set the default key
            $_limit_key_text = 'default.' . $_time_period;

            if ($_input['cluster_id'] !== '' && $_input['instance_id'] === '') {
                $_limit_key_text = 'cluster.default.' . $_time_period;
            } elseif ($_input['service_name'] == 'all' && $_input['user_id'] == '') {
                $_limit_key_text = 'instance.default.' . $_time_period;
            } elseif ($_input['service_name'] != 'all') {
                $_limit_key_text = 'service:' . $_input['service_name'] . '.' . $_time_period;
            } elseif ($_input['user_id'] != '') {
                $_limit_key_text = 'user:' . $_input['user_id'] . '.' . $_time_period;
            }

            $limit = [
                'cluster_id'     => $_input['cluster_id'] == 0 ? null : $_input['cluster_id'],
                'instance_id'    => $_input['instance_id'] == 0 ? null : $_input['instance_id'],
                'limit_key_text' => $_limit_key_text,
                'period_nbr'     => $this->periods[$_input['period_name']],
                'limit_nbr'      => $_input['limit_nbr'],
                'active_ind'     => ($_input['active_ind']) ? 1 : 0,
                'label_text'     => $_input['label_text'],
            ];

            $res =
                Limit::where('cluster_id', $limit['cluster_id'])
                    ->where('instance_id', $limit['instance_id'])
                    ->where('limit_key_text', $limit['limit_key_text'])
                    ->where('period_nbr', $limit['period_nbr'])
                    ->first();

            if (is_object($res)) {
                \Session::flash('flash_message',
                    'Unable to update limit! A limit with the selected combination of Cluster/Instance/User and Period already exists.');
                Session::flash('flash_type', 'alert-danger');

                return redirect('/' . $this->getUiPrefix() . '/limits/create')->withInput();
            }

            Limit::create($limit);

            return \Redirect::to('/' . $this->getUiPrefix() . '/limits')
                ->with('flash_message', 'The limit "' . $_input['label_text'] . '" was created successfully!')
                ->with('flash_type', 'alert-success');
        } catch (QueryException $e) {
            $err_msg = $e->getMessage();

            if (strpos($err_msg, 'SQLSTATE') !== false) {
                Session::flash('flash_message',
                    'Unable to update limit! A limit with the selected combination of Cluster/Instance/User and Period already exists.');
            } else {
                Session::flash('flash_message', 'Unable to update limit!');
            }

            Session::flash('flash_type', 'alert-danger');
            logger('Error adding limit: ' . $e->getMessage());

            return redirect('/' . $this->getUiPrefix() . '/limits/create')->withInput();
        }
    }

    /**
     * @param $ids
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
            $_instance = ($instanceId instanceof Instance) ? $instanceId : $this->_findInstance($instanceId);

            return $this->formatResponse($_instance->call('/api/v2/system/service',
                [],
                [],
                Request::METHOD_GET,
                false));
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
            $_instance = ($instanceId instanceof Instance) ? $instanceId : $this->_findInstance($instanceId);

            //echo json_encode($_instance->call('/api/v2/system/user', [], [], Request::METHOD_GET, false));
            return $this->formatResponse($_instance->call('/api/v2/system/user', [], [], Request::METHOD_GET, false));
            //return $_instance->call(Request::METHOD_GET, '/api/v2/system/user');
            //return $this->formatResponse($_instance->call(Request::METHOD_GET, '/api/v2/system/user'));
            //return $this->formatResponse($_instance->call('/api/v2/system/user', [], [], Request::METHOD_GET, false));
        }

        return false;
    }

    /**
     * @param string|int $instanceId
     *
     * @return array|bool|\stdClass
     */
    public function getInstanceAdmins($instanceId)
    {
        if (!empty($instanceId)) {
            $_instance = ($instanceId instanceof Instance) ? $instanceId : $this->_findInstance($instanceId);

            return $this->formatResponse($_instance->call('/api/v2/system/admin', [], [], Request::METHOD_GET, false));
        }

        return false;
    }

    /**
     * Formats the instance api response
     *
     * @param array $response
     *
     * @return array
     */
    protected function formatResponse($response)
    {
        echo json_encode($response);
        echo '<br><br>';
        echo json_encode($response['resource']);

        if (null === ($_rows = (array)data_get($response, 'resource'))) {
            logger('invalid response format: ' . print_r($response, true));
            throw new \RuntimeException('Invalid console response.');
        }

        $_results = [];

        foreach ($_rows as $_index => $_row) {
            if (array_key_exists('is_active', $_row) && 1 != $_row['is_active']) {
                continue;
            }

            $_results[] = ['id' => $_row['id'], 'name' => $_row['first_name'] . ' ' . $_row['last_name']];
        }

        usort($_results,
            function ($a, $b){
                return strcasecmp($a['name'], $b['name']);
            });

        return $_results;
    }
}
