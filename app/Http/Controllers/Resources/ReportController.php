<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;

class ReportController extends ViewController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'report_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Report';
    /** @type string */
    protected $_resource = 'report';
    /**
     * @type string
     */
    protected $_prefix = 'v1';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function show($id)
    {
        return $this->index();
    }

    /** @inheritdoc */
    public function index()
    {
        //  Dynamically generate the proper report query, parameters, and host/port
        $_key = 'reports.connections.' . config('reports.default');
        $_title = config($_key . '.reports.api-usage.title');
        $_indexType = config($_key . '.reports.api-usage.index-type', config('dfe.cluster-id'));

        $_clientHost = '//' . trim(str_replace(['https:', 'http:', ':', '///', '//', '/',],
                null,
                config($_key . '.client-host')),
                ' .'/** space and dot trimmed **/);

        if (!empty($_clientPort = config($_key . '.client-port'))) {
            $_clientHost .= ':' . $_clientPort;
        }

        $_query = config($_key . '.reports.api-usage.query-uri');

        if (!empty($_params = config($_key . '.reports.api-usage.query-params', []))) {
            foreach ($_params as $_key => $_value) {
                if (null !== $_value) {
                    //  Tighten up!
                    $_value = trim(str_replace([PHP_EOL, "\t", "\r", "\n", ' '],
                        null,
                        str_replace(['{index_type}', '(', ')', '"', ' ',],
                            [$_indexType, '%28', '%29', '\'', null],
                            $_value)));
                }

                $_query .= '&' . $_key . '=' . $_value;
            }
        }

        return \View::make('app.reports',
            [
                'prefix'            => $this->_prefix,
                'clusters'          => Cluster::orderBy('cluster_id_text')->get(['cluster_id_text']),
                'users'             => User::orderBy('first_name_text')->orderBy('last_name_text')->get([
                    'email_addr_text',
                    'first_name_text',
                    'last_name_text',
                ]),
                'instances'         => Instance::orderBy('instance_id_text')->get(['instance_id_text']),
                'report_index_type' => $_indexType,
                'report_title'      => $_title,
                'report_query'      => $_query,
                'report_host'       => $_clientHost,
            ]);
    }
}
