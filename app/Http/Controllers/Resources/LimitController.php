<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
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

        return Curl::get($_url,[], [CURLOPT_HEADER => EnterpriseDefaults::CONSOLE_X_HEADER . ': ' . $this->generateConsoleApiKey($instance->instance_data_text['env'])]);
    }
}