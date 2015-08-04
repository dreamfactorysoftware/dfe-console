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

        $_input = [];

        try {
            // Build the limit record

            foreach([
                        'cluster_id' => 0,
                        'instance_id' => 0,
                        'service_name' => 0,
                        'user_id' => 0,
                        'period_name' => "Minute",
                        'limit_nbr' => 0,
                        'is_active' => 0,
                        'label_text' => 0,
                        'type_select' => 0
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


            if ($_input['type_select'] == 'cluster') {
                $_input['instance_id'] = 0;
                $_input['user_id'] = 0;
            }

            if ($_input['type_select'] == 'instance') {
                $_input['user_id'] = 0;
            }

            $limit = [
                'cluster_id' => $_input['cluster_id'],
                'instance_id' => $_input['instance_id'],
                'user_id' => $_input['user_id'],
                'limit_key_text' => $_limit_key_text,
                'period_nbr' => $this->periods[$_input['period_name']],
                'limit_nbr' => $_input['limit_nbr'],
                'is_active' => ($_input['is_active']) ? 1 : 0,
                'label_text' => $_input['label_text']
            ];

            if (!Limit::find($id)->update($limit)) {
                throw new EnterpriseDatabaseException('Unable to update limit "' . $id . '"');
            }

            \Session::flash('flash_message', 'The limit "' . $_input['label_text'] . '" was updated successfully!');
            \Session::flash('flash_type', 'alert-success');

            return \Redirect::to($this->makeRedirectUrl('limits'));

        } catch (QueryException $e) {

            Session::flash('flash_message', 'Unable to add limit!');
            Session::flash('flash_type', 'alert-danger');

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

            if ($_limit['cluster_id'] != 0) {
                $_cluster = $this->_findCluster($_limit['cluster_id']);
                $_values['cluster_id_text'] = $_cluster->cluster_id_text;
            }

            //temp
            $_services = [];
            $_users = [];

            if ($_limit['instance_id'] != 0) {
                $_instance = $this->_findInstance($_limit['instance_id']);
                //$_tmp = $this->getInstanceServices($_limit['instance_id']);
                $_services = [];
                /*
                                foreach ($_tmp as $_v) {
                                    $_services[$_v['id']] = $_v['name'];
                                }

                                $_tmp = $this->getInstanceUsers($_limit['instance_id']);
                                $_users = [];

                                foreach($_tmp as $_v) {
                                    $_users[$_v['id']] = $_v['name'];
                                }
                */
                $_values['instance_id_text'] = $_instance->instance_id_text;

            }

            $defaultPos = strpos($_limit['limit_key_text'], 'default.');
            $clusterDefaultPos = strpos($_limit['limit_key_text'], 'cluster.default.');
            $instanceDefaultPos = strpos($_limit['limit_key_text'], 'instnace.default.');

            if ($defaultPos !== false && $defaultPos == 0) {
                $_values['notes'] = 'Default for all clusters and instances';
            } elseif ($clusterDefaultPos !== false && $clusterDefaultPos == 0) {
                $_values['notes'] = 'Default for cluster';
            } elseif ($instanceDefaultPos !== false && $instanceDefaultPos == 0) {
                $_values['notes'] = 'Default for instance';
            } else {
                $_values['notes'] = '';
            }


            foreach (explode('.', $_limit['limit_key_text']) as $_value) {
                $_limit_key = explode(':', $_value);

                switch ($_limit_key[0]) {
                    case 'default':
                    case 'cluster':
                    case 'instance':
                        break;
                    case 'user':
                        $_values['user_id'] = $_limit_key[1];
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

            $_limits[] = [
                'id' => $_limit['id'],
                'cluster_id_text' => $_values['cluster_id_text'],
                'instance_id_text' => $_values['instance_id_text'],
                'service_desc' => empty($_values['service_name']) === true ?'':$_services[$_values['service_name']],
                'user_name' => $_values['user_id'] == 0 ?'':$_users[$_values['user_id']],
                'period_name' => $_values['period_name'],
                'limit_nbr' => $_limit->limit_nbr,
                'label_text' => $_limit->label_text,
                'is_active' => $_limit->is_active,
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

        //echo $_results;

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

        if ($_limit['cluster_id'] != 0) {
            $_cluster = $this->_findCluster($_limit['cluster_id']);
            $_values['cluster_id_text'] = $_cluster->cluster_id_text;
        }

        //temp
        $_services = [];
        $_users = [];
        /*
                    if ($_limit['instance_id'] != 0) {
                        $_instance = $this->_findInstance($_limit['instance_id']);
                        $_tmp = $this->getInstanceServices($_limit['instance_id']);
                        $_services = [];

                        foreach ($_tmp as $_v) {
                            $_services[$_v['id']] = $_v['name'];
                        }

                        $_tmp = $this->getInstanceUsers($_limit['instance_id']);
                        $_users = [];

                        foreach($_tmp as $_v) {
                            $_users[$_v['id']] = $_v['name'];
                        }

                        $_values['instance_id_text'] = $_instance->instance_id_text;

                    }
        */
        $defaultPos = strpos($_limit['limit_key_text'], 'default.');
        $clusterDefaultPos = strpos($_limit['limit_key_text'], 'cluster.default.');
        $instanceDefaultPos = strpos($_limit['limit_key_text'], 'instance.default.');

        $_type = null;

        if ($defaultPos !== false && $defaultPos == 0) {
            $_values['notes'] = 'Default for all clusters and instances';
            $_type = '';
        } elseif ($clusterDefaultPos !== false && $clusterDefaultPos == 0) {
            $_values['notes'] = 'Default for cluster';
            $_type = 'cluster';
        } elseif ($instanceDefaultPos !== false && $instanceDefaultPos == 0) {
            $_values['notes'] = 'Default for instance';
            $_type = 'instance';
        } else {
            $_values['notes'] = '';
        }


        foreach (explode('.', $_limit['limit_key_text']) as $_value) {
            $_limit_key = explode(':', $_value);

            switch ($_limit_key[0]) {
                case 'default':
                case 'cluster':
                case 'instance':
                    break;
                case 'user':
                    $_values['user_id'] = $_limit_key[1];
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

        $_limits = [
            'id' => $_limit['id'],
            'type' => $_type,
            'cluster_id' => $_limit['cluster_id'],
            'cluster_id_text' => $_values['cluster_id_text'],
            'instance_id' => $_limit['instance_id'],
            'instance_id_text' => $_values['instance_id_text'],
            'service_desc' => empty($_values['service_name']) === true ?'':$_services[$_values['service_name']],
            'user_name' => $_values['user_id'] == 0 ?'':$_users[$_values['user_id']],
            'period_name' => $_values['period_name'],
            'limit_nbr' => $_limit->limit_nbr,
            'label_text' => $_limit->label_text,
            'is_active' => $_limit->is_active,
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
        $_input = [];

        try {
            // Build the limit record

            foreach([
                        'cluster_id' => 0,
                        'instance_id' => 0,
                        'service_name' => 0,
                        'user_id' => 0,
                        'period_name' => "Minute",
                        'limit_nbr' => 0,
                        'is_active' => 0,
                        'label_text' => 0
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
                'is_active' => (isset($_input['is_active'])) ? 1 : 0,
                'label_text' => $_input['label_text']
            ];

            Limit::create($limit);

            Session::flash('flash_message', 'The limit');
            Session::flash('flash_type', 'alert-danger');

            return \Redirect::to('/' . $this->getUiPrefix() . '/limits')->with('flash_message', 'Limit added')->with('flash_type', 'alert-success');

        } catch (QueryException $e) {

            Session::flash('flash_message', 'Unable to add limit!');
            Session::flash('flash_type', 'alert-danger');

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
        echo $ids;

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