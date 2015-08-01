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

    public function store()
    {
        $_input = \Input::all();

        try {
            // Build the limit record

            $_time_period = str_replace(' ', '-', strtolower(array_get($_input, 'period_name', 'Minute')));

            if (array_get($_input, 'cluster_id', 0) === 0 && array_get($_input, 'instance_id', 0) === 0) {
                $_limit_key_text = 'default.' . $_time_period;
            } elseif (array_get($_input, 'cluster_id', 0) !== 0 && array_get($_input, 'instance_id', 0) === 0) {
                $_limit_key_text = 'cluster.default.' . $_time_period;
            } else {
                if (array_get($_input, 'service_name', 0) === 0 && array_get($_input, 'user_id', 0) === 0) {
                    $_limit_key_text = 'instance.default.' . $_time_period;
                } elseif (array_get($_input, 'service_name', 0) !== 0) {
                    $_limit_key_text = 'service:' . $_input['service_name'] . '.' . $_time_period;
                } elseif (array_get($_input, 'user_id', 0) !== 0) {
                    $_limit_key_text = 'user:' . $_input['user_id'] . '.' . $_time_period;
                }
            }

            $limit = [
                'cluster_id' => array_get($_input, 'cluster_id', 0),
                'instance_id' => array_get($_input, 'instance_id', 0),
                'limit_key_text' => $_limit_key_text,
                'period_nbr' => $this->periods[array_get($_input, 'period_name', 'Minute')],
                'limit_nbr' => array_get($_input, 'limit_nbr', 0),
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