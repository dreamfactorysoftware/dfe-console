<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;
use DreamFactory\Enterprise\Database\Exceptions\DatabaseException;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Library\Utility\Enums\DateTimeIntervals;
use DreamFactory\Library\Utility\Enums\Limits;
use DreamFactory\Library\Utility\IfSet;
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
                'type_select'  => 'cluster',
                'cluster_id'   => null,
                'instance_id'  => null,
                'service_name' => null,
                'user_id'      => 0,
                'period_name'  => "Minute",
                'limit_nbr'    => 0,
                'active_ind'   => 0,
                'label_text'   => null,
            ] as $_input_key => $_input_default) {
                $_input[$_input_key] = \Input::get($_input_key, $_input_default);
            }

            $_time_period = str_replace(' ', '-', strtolower($_input['period_name']));

            switch ($_input['type_select']) {
                case Limits::CLUSTER:
                    $_limit_key_text = $this->_findCluster($_input['cluster_id'])->cluster_id_text . '.' . $_time_period;
                    break;
                case Limits::INSTANCE:
                    $_limit_key_text =
                        $this->_findCluster($_input['cluster_id'])->cluster_id_text .
                        '.' .
                        (!empty($_input['instance_id']) ? $this->_findInstance($_input['instance_id'])->instance_id_text : 'each_instance') .
                        '.' .
                        $_time_period;
                    break;
                case Limits::USER:
                    $_limit_key_text =
                        $this->_findCluster($_input['cluster_id'])->cluster_id_text .
                        '.' .
                        (!empty($_input['instance_id']) ? $this->_findInstance($_input['instance_id'])->instance_id_text : 'each_instance') .
                        '.' .
                        (!empty($_input['user_id']) ? 'user:' . $_input['user_id'] : 'each_user') .
                        '.' .
                        $_time_period;
                    break;
            }

            $limit = [
                'limit_type_nbr' => $_input['type_select'],
                'cluster_id'     => $_input['cluster_id'],
                'instance_id'    => empty($_input['instance_id']) ? null : $_input['instance_id'],
                'limit_key_text' => $_limit_key_text,
                'period_nbr'     => $this->periods[$_input['period_name']],
                'limit_nbr'      => $_input['limit_nbr'],
                'active_ind'     => $_input['active_ind'] ? 1 : 0,
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

            $this->refreshInstanceConfig($limit['cluster_id'], $limit['instance_id']);

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
            'user_name'        => ' ',
            'instance_id_text' => ' ',
            'limit_type_text'  => null,
        ];

        $_limits = [];

        $_limit_types = [
            Limits::CLUSTER  => 'Cluster',
            Limits::INSTANCE => 'Instance',
            Limits::USER     => 'User',
        ];

        /** @type Limit $_limit */
        foreach (Limit::all() as $_limit) {
            $_cluster = null;

            $_values = array_merge($_valueTemplate,
                ['limit_nbr' => $_limit->id, 'label_text' => $_limit->label_text, 'limit_type_text' => array_get($_limit_types, $_limit->limit_type_nbr)]);

            $_limit_key_array = explode('.', $_limit['limit_key_text']);

            if (!empty($_limit->cluster_id)) {
                //The cluster name is always the first element
                $_values['cluster_id_text'] = array_shift($_limit_key_array);
            } else {
                // Invalid cluster id, skip
                $this->error('Invalid cluster_id in limit id#' . $_limit->id);
                continue;
            }

            // The period is always the last element
            $_values['period_name'] = ucwords(str_replace('-', ' ', array_pop($_limit_key_array)));

            // Can this limit be cleared?
            $enableClear = true;

            // If there are any elements left in the array, it's the instance name/each_instance or user id/each_user

            if (!empty($_limit_key_array)) {
                // Do we have a specific instance?
                if (!empty($_limit['instance_id'])) {
                    $_values['instance_id_text'] = array_shift($_limit_key_array);
                } else {
                    $_values['instance_id_text'] = ucwords(str_replace('_', ' ', array_shift($_limit_key_array)));

                    // This is an each_instance rule, can not be cleared individually
                    $enableClear = false;
                }
            }

            // If there are any elements left in the array at this point, it's the user id or each_user

            if (!empty($_limit_key_array)) {
                $_values['user_name'] = 'Each User';

                // Do we have a specific user?
                $_user = array_shift($_limit_key_array);
                if (strpos($_user, 'user:') !== false) {

                    $_user_array = explode(':', $_user);
                    $_userId = array_pop($_user_array);

                    try {
                        $_instance = $this->_findInstance($_limit['instance_id']);

                        if (false !== ($_rows = $this->getInstanceUsers($_instance))) {
                            foreach ($_rows as $_user) {
                                if ($_user['id'] != $_userId) {
                                    continue;
                                }

                                $_values['user_name'] = $_user['name'];
                                break;
                            }
                        }
                    } catch (ModelNotFoundException $e) {
                        // Invalid instance, skip it
                        continue;
                    }
                } else {
                    // This is an each_user instance, can not be cleared individually
                    $enableClear = false;
                }
            }

            $_limits[] = [
                'id'               => $_limit['id'],
                'cluster_id_text'  => $_values['cluster_id_text'],
                'instance_id_text' => $_values['instance_id_text'],
                //'service_desc' => empty($_values['service_name']) === true ?'':$_services[$_values['service_name']],
                'user_name'        => $_values['user_name'],
                'period_name'      => $_values['period_name'],
                'limit_nbr'        => $_limit->limit_nbr,
                'label_text'       => $_limit->label_text,
                'active_ind'       => $_limit->active_ind,
                'limit_type_text'  => $_values['limit_type_text'],
                'enable_clear'     => $enableClear,
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
            'service_name'     => null,
            'role_id'          => 0,
            'api_key'          => null,
            'period_name'      => null,
            'label_text'       => $_limit->label_text,
            'cluster_id_text'  => null,
            'instance_id_text' => null,
            'user_name'        => ' ',
        ];

        $_limit_key_array = explode('.', $_limit['limit_key_text']);

        if (!empty($_limit->cluster_id)) {
            //The cluster name is always the first element
            $_values['cluster_id_text'] = array_shift($_limit_key_array);
        }

        // The period is always the last element
        $_values['period_name'] = ucwords(str_replace('-', ' ', array_pop($_limit_key_array)));

        // If there are any elements left in the array, it's the instance name/each_instance or user id/each_user

        if (!empty($_limit_key_array)) {
            // Do we have a specific instance?
            if (!empty($_limit['instance_id'])) {
                $_values['instance_id_text'] = array_shift($_limit_key_array);
            } else {
                $_values['instance_id_text'] = ucwords(str_replace('_', ' ', array_shift($_limit_key_array)));
            }
        }

        // If there are any elements left in the array at this point, it's the user id or each_user

        if (!empty($_limit_key_array)) {
            $_values['user_name'] = 'Each User';

            // Do we have a specific user?
            $_user = array_shift($_limit_key_array);
            if (strpos($_user, 'user:') !== false) {

                $_user_array = explode(':', $_user);
                $_userId = array_pop($_user_array);

                try {
                    $_instance = $this->_findInstance($_limit['instance_id']);

                    if (false !== ($_rows = $this->getInstanceUsers($_instance))) {
                        foreach ($_rows as $_user) {
                            if ($_user['id'] != $_userId) {
                                continue;
                            }

                            $_values['user_name'] = $_user['name'];
                            $_values['user_id'] = $_userId;
                            break;
                        }
                    }
                } catch (ModelNotFoundException $e) {
                    // Invalid instance, skip it
                }
            }
        }

        $_limits = [
            'id'               => $_limit['id'],
            'type'             => $_limit->limit_type_nbr,
            'cluster_id'       => $_limit['cluster_id'],
            'cluster_id_text'  => $_values['cluster_id_text'],
            'instance_id'      => empty($_limit['instance_id']) ? 0 : $_limit['instance_id'],
            'instance_id_text' => $_values['instance_id_text'],
            'user_id'          => empty($_values['user_id']) ? 0 : $_values['user_id'],
            'user_name'        => $_values['user_name'],
            'period_name'      => $_values['period_name'],
            'limit_nbr'        => $_limit->limit_nbr,
            'label_text'       => $_limit->label_text,
            'active_ind'       => $_limit->active_ind,
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
                'type_select'  => 'cluster',
                'cluster_id'   => null,
                'instance_id'  => null,
                'service_name' => null,
                'user_id'      => 0,
                'period_name'  => "Minute",
                'limit_nbr'    => 0,
                'active_ind'   => 0,
                'label_text'   => null,
            ] as $_input_key => $_input_default) {
                $_input[$_input_key] = \Input::get($_input_key, $_input_default);
            }

            $_time_period = str_replace(' ', '-', strtolower($_input['period_name']));

            switch ($_input['type_select']) {
                case Limits::CLUSTER:
                    $_limit_key_text = $this->_findCluster($_input['cluster_id'])->cluster_id_text . '.' . $_time_period;
                    break;
                case Limits::INSTANCE:
                    $_limit_key_text =
                        $this->_findCluster($_input['cluster_id'])->cluster_id_text .
                        '.' .
                        (!empty($_input['instance_id']) ? $this->_findInstance($_input['instance_id'])->instance_id_text : 'each_instance') .
                        '.' .
                        $_time_period;
                    break;
                case Limits::USER:
                    $_limit_key_text =
                        $this->_findCluster($_input['cluster_id'])->cluster_id_text .
                        '.' .
                        (!empty($_input['instance_id']) ? $this->_findInstance($_input['instance_id'])->instance_id_text : 'each_instance') .
                        '.' .
                        (!empty($_input['user_id']) ? 'user:' . $_input['user_id'] : 'each_user') .
                        '.' .
                        $_time_period;
                    break;
            }

            $limit = [
                'limit_type_nbr' => $_input['type_select'],
                'cluster_id'     => $_input['cluster_id'],
                'instance_id'    => empty($_input['instance_id']) ? null : $_input['instance_id'],
                'limit_key_text' => $_limit_key_text,
                'period_nbr'     => $this->periods[$_input['period_name']],
                'limit_nbr'      => $_input['limit_nbr'],
                'active_ind'     => $_input['active_ind'] ? 1 : 0,
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
            $this->refreshInstanceConfig($limit['cluster_id'], $limit['instance_id']);

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
                $selected = \Input::get('_selected');
                $id_array = explode(',', $selected);
            } elseif ($ids == 'resetcounter') {
                $limit = Limit::where('id', '=', \Input::get('limit_id'))->first();

                if ($limit->limit_type_nbr == Limits::CLUSTER) {
                    $instances = $this->getInstancesForCluster($limit->cluster_id);
                } else {
                    $instances = ['id' => $limit->instance_id];
                }

                foreach ($instances as $instanceId) {
                    $this->resetLimitCounter($instanceId, $limit->limit_key_text);
                }

                Session::flash('flash_message', 'The counter for the limit ' . $limit->limit_name . ' has been reset');
                Session::flash('flash_type', 'alert-success');

                return \Redirect::to('/' . $this->getUiPrefix() . '/limits');
            } elseif ($ids == 'resetallcounters') {
                $instance_id = \Input::get('instance_id');

                $this->resetAllLimitCounters($instance_id);

                Session::flash('flash_message', 'All limit counters for the instance has been reset');
                Session::flash('flash_type', 'alert-success');

                return \Redirect::to('/' . $this->getUiPrefix() . '/instances');
            } else {
                $id_array = explode(',', $ids);
            }

            foreach ($id_array as $id) {
                $limit = Limit::where('id', '=', $id)->first();
                array_push($limit_names, '"' . $limit->label_text . '"');
                $limit->delete();
                $this->refreshInstanceConfig($limit->cluster_id, $limit->instance_id);
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

            return \Redirect::to('/' . $this->getUiPrefix() . '/limits');
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

            return $this->formatResponse($_instance->call('/api/v2/system/user', [], [], Request::METHOD_GET, false));
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

    protected function refreshInstanceConfig($clusterId, $instanceId)
    {
        $instances = $this->getInstancesForCluster($clusterId);

        if (!is_null($instanceId)) {
            $instances = ['id' => $instanceId];
        }

        foreach ($instances as $instanceId) {
            $this->_refreshInstanceConfig($instanceId);
        }

        return true;
    }

    private function _refreshInstanceConfig($instanceId)
    {
        if (!empty($instanceId)) {
            $_instance = ($instanceId instanceof Instance) ? $instanceId : $this->_findInstance($instanceId);

            return $this->formatResponse($_instance->call('/instance/refresh', [], [], Request::METHOD_PUT, false));
        }

        return false;
    }

    /**
     * @param string $instanceId
     * @param string $limitKey
     *
     * @return array|bool
     */
    protected function resetLimitCounter($instanceId, $limitKey)
    {
        if (!empty($limitKey) && !empty($instanceId)) {
            $_instance = ($instanceId instanceof Instance) ? $instanceId : $this->findInstance($instanceId);

            return $this->formatResponse($_instance->call('/instance/clear-limits-counter/' . $limitKey, [], [], Request::METHOD_DELETE, false));
        }

        return false;
    }

    protected function resetAllLimitCounters($instanceId)
    {
        if (!empty($instanceId)) {
            $_instance = ($instanceId instanceof Instance) ? $instanceId : $this->_findInstance($instanceId);

            return $this->formatResponse($_instance->call('/instance/clear-limits-cache', [], [], Request::METHOD_DELETE, false));
        }

        return false;
    }

    public function getInstancesForCluster($clusterId)
    {
        $_cluster = $this->_findCluster($clusterId);
        $_rows = Instance::byClusterId($_cluster->id)->get(['id']);

        $_response = [];

        /** @type Instance $_instance */
        foreach ($_rows as $_instance) {
            $_response[] = ['id' => $_instance->id];
        }

        return $_response;
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
        if (null === ($_rows = (array)data_get($response, 'resource'))) {
            logger('invalid response format: ' . print_r($response, true));
            throw new \RuntimeException('Invalid instance response.');
        }

        $_results = [];

        foreach ($_rows as $_index => $_row) {
            if (IfSet::getBool($_row, 'is_active') && !empty(trim(array_get($_row, 'first_name') . ' ' . array_get($_row, 'last_name')))) {
                $_results[] = [
                    'id'   => $_row['id'],
                    'name' => $_row['first_name'] . ' ' . $_row['last_name'],
                ];
            }
        }

        !empty($_results) && usort($_results,
            function($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });

        return $_results;
    }

    protected function getInstance($instanceId) {
        return ($instanceId instanceOf Instance) ? $instanceId : $this->findInstance($instanceId);        
    }
}
