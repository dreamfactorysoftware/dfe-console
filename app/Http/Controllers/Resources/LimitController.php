<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Library\Utility\Enums\DateTimeIntervals;
use DreamFactory\Library\Utility\Curl;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Session;

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
            'Minute' => DateTimeIntervals::SECONDS_PER_MINUTE,
            'Hour'   => DateTimeIntervals::SECONDS_PER_HOUR,
            'Day'    => DateTimeIntervals::SECONDS_PER_DAY,
            '7 Days'   => DateTimeIntervals::SECONDS_PER_DAY * 7,
            '30 Days'  => DateTimeIntervals::SECONDS_PER_DAY * 30,
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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return \View::make('app.limits',
            [
                'prefix' => $this->_prefix,
                'limits' => [],
            ]);
    }

    public function edit($limit_id)
    {
        $limit = Limit::find($limit_id);

        $services = $users = $_response = [];

        $values = [
            'id' => $limit_id,
            'cluster_id' => $limit->cluster_id,
            'instance_id' => $limit->instance_id,
            'limit_nbr' => $limit->limit_nbr,
            'user_id' => 0,
            'service_name' => '',
            'role_id' => 0,
            'api_key' => '',
            'period_name' => ''
        ];

        if ($limit->instance_id != 0) {
            $services = $this->getInstanceServices($limit->instance_id);
            $users = $this->getInstanceUsers($limit->instance_id);

            // @todo Refactor this so it's not in two places!

            $_cluster = $this->_findCluster($values['cluster_id']);
            $_rows = Instance::byClusterId($_cluster->id)->get(['id', 'instance_name_text']);

            /** @type Instance $_instance */
            foreach ($_rows as $_instance) {
                $_response[] = ['id' => $_instance->id, 'name' => $_instance->instance_name_text];
            }
        }

        foreach (explode('.', $limit->limit_key_text) as $_value) {
            $_limit_key = explode(':', $_value);

            switch($_limit_key[0]) {
                case 'default':
                case 'cluster':
                case 'instance':
                    break;
                case 'user':
                    $values['user_id'] = $_limit_key[1];
                    break;
                case 'service':
                    $values['service_name'] = $_limit_key[1];
                    break;
                case 'role':
                    $values['role_id'] = $_limit_key[1];
                    break;
                case 'api_key':
                    $values['api_key'] = $_limit_key[1];
                    break;
                default:
                    // It's time period
                    $values['period_name'] = ucwords(str_replace('-', ' ', $_limit_key[0]));
            }
        }

        return \View::make('app.limits.edit',
            [
                'limitPeriods' => $this->periods,
                'prefix' => $this->_prefix,
                'clusters' => Cluster::all(),
                'instances' => $_response,
                'services' => $services,
                'users' => $users,
                'limit' => $values
            ]);
    }

    public function update($limit_id)
    {
        try {
            // Build the limit record

            $formLimit = $this->_buildLimitFromInput();

            $limit = Limit::find($limit_id);

            foreach($formLimit as $_key => $_value) {
                $limit->$_key = $_value;
            }

            $limit->save();

            return \Redirect::to('/' . $this->getUiPrefix() . '/limits')->with('flash_message', 'Limit updated')->with('flash_type', 'alert-success');

        } catch (QueryException $e) {

            Session::flash('flash_message', 'Unable to edit limit!');
            Session::flash('flash_type', 'alert-danger');

            return redirect('/' . $this->getUiPrefix() . '/limits/' . $limit['id'] . '/edit')->withInput();
        }
    }

    /**
     * @todo add manual constraint checks, as 0 is a valid option for cluster_id and instance_id in this use
     *
     * @return $this
     */
    public function store()
    {
        try {
            // Build the limit record

            $limit = $this->_buildLimitFromInput();

            Limit::create($limit);

            return \Redirect::to('/' . $this->getUiPrefix() . '/limits')->with('flash_message', 'Limit added')->with('flash_type', 'alert-success');

        } catch (QueryException $e) {

            Session::flash('flash_message', 'Unable to add limit!');
            Session::flash('flash_type', 'alert-danger');

            return redirect('/' . $this->getUiPrefix() . '/limits/create')->withInput();
        }
    }

    private function _buildLimitFromInput()
    {
        try {
            $_input = [];

            foreach ([
                         'cluster_id' => 0,
                         'instance_id' => 0,
                         'service_name' => 0,
                         'user_id' => 0,
                         'period_name' => "Minute",
                         'limit_nbr' => 0
                     ] as $_input_key => $_input_default) {
                $_input[$_input_key] = \Input::get($_input_key, $_input_default);
            }

            $_time_period = str_replace(' ', '-', strtolower($_input['period_name']));

            if ($_input['cluster_id'] == 0 && $_input['instance_id'] == 0) {
                $_limit_key_text = 'default.' . $_time_period;
            } elseif ($_input['cluster_id'] != 0 && $_input['instance_id'] == 0) {
                $_limit_key_text = 'cluster.default.' . $_time_period;
            } else {
                if ($_input['service_name'] == 'all' && $_input['user_id'] == 0) {
                    $_limit_key_text = 'instance.default.' . $_time_period;
                } elseif ($_input['service_name'] != 'all') {
                    $_limit_key_text = 'service:' . $_input['service_name'] . '.' . $_time_period;
                } elseif ($_input['user_id'] != 0) {
                    $_limit_key_text = 'user:' . $_input['user_id'] . '.' . $_time_period;
                }
            }

            $limit = [
                'cluster_id' => $_input['cluster_id'],
                'instance_id' => $_input['instance_id'],
                'limit_key_text' => $_limit_key_text,
                'period_nbr' => $this->periods[$_input['period_name']],
                'limit_nbr' => $_input['limit_nbr'],
                'is_active' => true
            ];

            return $limit;
        } catch (\Exception $e) {
            throw new \Exception('Unable to build limit record');
        }

    }

    /**
     * @param string|int $instanceId
     *
     * @return array|bool|\stdClass
     */
    public function getInstanceServices($instanceId)
    {
        return $this->callInstanceApi($this->_findInstance($instanceId), '/api/v2/system/service');
    }

    /**
     * @param string|int $instanceId
     *
     * @return array|bool|\stdClass
     */
    public function getInstanceUsers($instanceId)
    {
        return $this->callInstanceApi($this->_findInstance($instanceId),'/api/v2/system/user');
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