<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Library\Utility\Enums\DateTimeIntervals;
use DreamFactory\Library\Utility\Curl;

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

        print "<pre>" . print_r($limit, true);
    }

    /**
     * @todo add manual constraint checks, as 0 is a valid option for cluster_id and instance_id in this use
     *
     * @return $this
     */
    public function store()
    {
        $_input = [];

        try {
            // Build the limit record

            foreach([
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

            if ( $_input['cluster_id'] == 0 && $_input['instance_id'] == 0) {
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

            Limit::create($limit);

            return \Redirect::to('/' . $this->getUiPrefix() . '/limits')->with('flash_message', 'Limit added')->with('flash_type', 'alert-success');

        } catch (QueryException $e) {

            Session::flash('flash_message', 'Unable to add limit!');
            Session::flash('flash_type', 'alert-danger');

            return redirect('/' . $this->getUiPrefix() . '/limits/create')->withInput();
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